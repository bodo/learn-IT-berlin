<?php

namespace App\Livewire\Admin\LearningGraphs;

use App\Enums\LearningGraphBlockType;
use App\Enums\LearningGraphEdgeDirection;
use App\Enums\LearningGraphStatus;
use App\Models\Group;
use App\Models\LearningGraph;
use App\Models\LearningGraphEdge;
use App\Models\LearningGraphNode;
use App\Models\LearningGraphNodeBlock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class GraphForm extends Component
{
    use WithFileUploads;

    public Group $group;
    public ?LearningGraph $graph = null;

    public string $title = '';
    public string $status = LearningGraphStatus::Draft->value;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $nodes = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $edges = [];

    public function mount(Group $group, $graph = null): void
    {
        abort_unless($group->canManage(Auth::user()), 403);

        $this->group = $group;

        if ($graph instanceof LearningGraph) {
            $this->graph = $graph;
        } elseif (is_numeric($graph)) {
            $this->graph = $group->learningGraphs()->findOrFail($graph);
        } else {
            $this->graph = new LearningGraph([
                'group_id' => $group->id,
                'status' => LearningGraphStatus::Draft,
            ]);
        }

        $this->title = $this->graph->title ?? '';
        $this->status = $this->graph->status?->value ?? LearningGraphStatus::Draft->value;

        $this->loadStateFromModel();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(LearningGraphStatus::class)],
            'nodes' => ['array'],
            'nodes.*.title' => ['required', 'string', 'max:255'],
            'nodes.*.level' => ['nullable', 'integer', 'min:0', 'max:50'],
            'nodes.*.order' => ['nullable', 'integer', 'min:0', 'max:100'],
            'nodes.*.blocks' => ['array'],
            'nodes.*.blocks.*.type' => ['required', Rule::enum(LearningGraphBlockType::class)],
            'nodes.*.blocks.*.content' => ['nullable', 'string'],
            'nodes.*.blocks.*.upload' => ['nullable', 'image', 'max:4096'],
            'edges' => ['array'],
            'edges.*.from' => ['nullable', 'string'],
            'edges.*.to' => ['nullable', 'string'],
            'edges.*.direction' => ['required', Rule::enum(LearningGraphEdgeDirection::class)],
            'edges.*.label' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nodes.*.blocks.*.upload.image' => __('Each upload must be a valid image file.'),
            'nodes.*.blocks.*.upload.max' => __('Images must be smaller than :max KB.', ['max' => 4096]),
        ];
    }

    public function addNode(): void
    {
        $this->nodes[] = [
            'uuid' => (string) Str::uuid(),
            'id' => null,
            'title' => '',
            'level' => count($this->nodes),
            'order' => count($this->nodes),
            'blocks' => [],
        ];
    }

    public function removeNode(string $uuid): void
    {
        $index = $this->findNodeIndex($uuid);

        if ($index === null) {
            return;
        }

        array_splice($this->nodes, $index, 1);

        $this->edges = array_values(array_filter($this->edges, function ($edge) use ($uuid) {
            return $edge['from'] !== $uuid && $edge['to'] !== $uuid;
        }));
    }

    public function addBlock(string $nodeUuid, string $type): void
    {
        $index = $this->findNodeIndex($nodeUuid);

        if ($index === null) {
            return;
        }

        $blockType = LearningGraphBlockType::from($type);

        $this->nodes[$index]['blocks'][] = [
            'uuid' => (string) Str::uuid(),
            'id' => null,
            'type' => $blockType->value,
            'content' => $blockType === LearningGraphBlockType::Text ? '' : null,
            'image_path' => null,
            'order' => count($this->nodes[$index]['blocks']),
            'upload' => null,
        ];
    }

    public function removeBlock(string $nodeUuid, string $blockUuid): void
    {
        $nodeIndex = $this->findNodeIndex($nodeUuid);

        if ($nodeIndex === null) {
            return;
        }

        $blocks = $this->nodes[$nodeIndex]['blocks'] ?? [];

        foreach ($blocks as $index => $block) {
            if (($block['uuid'] ?? null) === $blockUuid) {
                array_splice($blocks, $index, 1);
                $this->nodes[$nodeIndex]['blocks'] = array_values($blocks);

                return;
            }
        }
    }

    public function addEdge(): void
    {
        if (count($this->nodes) < 2) {
            return;
        }

        $from = $this->nodes[0]['uuid'];
        $to = $this->nodes[1]['uuid'] ?? $from;

        if ($from === $to && isset($this->nodes[1])) {
            $to = $this->nodes[1]['uuid'];
        }

        if ($from === $to) {
            return;
        }

        $this->edges[] = [
            'uuid' => (string) Str::uuid(),
            'id' => null,
            'from' => $from,
            'to' => $to,
            'direction' => LearningGraphEdgeDirection::To->value,
            'label' => null,
        ];
    }

    public function removeEdge(string $edgeUuid): void
    {
        $this->edges = array_values(array_filter($this->edges, fn ($edge) => $edge['uuid'] !== $edgeUuid));
    }

    public function save(): void
    {
        $this->resetErrorBag();

        $validated = $this->validate();

        $nodeUuidToId = [];

        DB::transaction(function () use (&$nodeUuidToId, $validated) {
            $graph = $this->graph && $this->graph->exists ? $this->graph : new LearningGraph();

            $graph->fill([
                'title' => trim($validated['title']),
                'status' => LearningGraphStatus::from($validated['status']),
            ]);

            $graph->group()->associate($this->group);
            $graph->save();

            $this->graph = $graph;

            $existingNodes = $graph->nodes()->with('blocks')->get()->keyBy('id');
            $persistedNodeIds = [];

            foreach ($this->nodes as $nodeIndex => &$nodeData) {
                $payload = [
                    'title' => trim($nodeData['title'] ?? ''),
                    'level' => (int) ($nodeData['level'] ?? $nodeIndex),
                    'order_column' => (int) ($nodeData['order'] ?? $nodeIndex),
                ];

                if (! empty($nodeData['id']) && $existingNodes->has($nodeData['id'])) {
                    $nodeModel = $existingNodes->get($nodeData['id']);
                    $nodeModel->update($payload);
                } else {
                    $nodeModel = $graph->nodes()->create($payload);
                    $nodeData['id'] = $nodeModel->id;
                }

                $persistedNodeIds[] = $nodeModel->id;
                $nodeUuidToId[$nodeData['uuid']] = $nodeModel->id;

                $existingBlocks = $nodeModel->blocks()->get()->keyBy('id');
                $blockIdsToKeep = [];

                foreach ($nodeData['blocks'] as $blockIndex => &$blockData) {
                    $blockType = LearningGraphBlockType::from($blockData['type']);

                    $payload = [
                        'type' => $blockType,
                        'order_column' => (int) ($blockData['order'] ?? $blockIndex),
                        'content' => null,
                        'image_path' => null,
                    ];

                    if ($blockType === LearningGraphBlockType::Text) {
                        $payload['content'] = trim($blockData['content'] ?? '');
                    } else {
                        if (! empty($blockData['upload'])) {
                            $path = $blockData['upload']->store('learning-graphs', 'public');

                            if (! empty($blockData['image_path']) && Storage::disk('public')->exists($blockData['image_path'])) {
                                Storage::disk('public')->delete($blockData['image_path']);
                            }

                            $blockData['image_path'] = $path;
                            $blockData['upload'] = null;
                        }

                        $payload['image_path'] = $blockData['image_path'] ?? null;
                    }

                    if (! empty($blockData['id']) && $existingBlocks->has($blockData['id'])) {
                        $blockModel = $existingBlocks->get($blockData['id']);

                        if ($blockModel->type === LearningGraphBlockType::Image
                            && $blockType === LearningGraphBlockType::Text
                            && $blockModel->image_path
                            && Storage::disk('public')->exists($blockModel->image_path)) {
                            Storage::disk('public')->delete($blockModel->image_path);
                        }

                        $blockModel->update($payload);
                    } else {
                        if ($blockType === LearningGraphBlockType::Image && empty($payload['image_path'])) {
                            throw ValidationException::withMessages([
                                'nodes.'.$nodeIndex.'.blocks.'.$blockIndex.'.upload' => __('Please upload an image.'),
                            ]);
                        }

                        $blockModel = $nodeModel->blocks()->create($payload);
                        $blockData['id'] = $blockModel->id;
                    }

                    $blockIdsToKeep[] = $blockModel->id;
                }

                $nodeModel->blocks()
                    ->whereNotIn('id', $blockIdsToKeep)
                    ->get()
                    ->each(fn (LearningGraphNodeBlock $block) => $block->delete());
            }

            $graph->nodes()
                ->whereNotIn('id', $persistedNodeIds)
                ->get()
                ->each(function (LearningGraphNode $node) {
                    $node->blocks()->each->delete();
                    $node->delete();
                });

            $existingEdges = $graph->edges()->get()->keyBy('id');
            $edgeIdsToKeep = [];

            foreach ($this->edges as &$edgeData) {
                $fromUuid = $edgeData['from'] ?? null;
                $toUuid = $edgeData['to'] ?? null;

                if (! $fromUuid || ! $toUuid) {
                    continue;
                }

                if ($fromUuid === $toUuid) {
                    continue;
                }

                if (! isset($nodeUuidToId[$fromUuid], $nodeUuidToId[$toUuid])) {
                    continue;
                }

                $payload = [
                    'learning_graph_id' => $graph->id,
                    'from_node_id' => $nodeUuidToId[$fromUuid],
                    'to_node_id' => $nodeUuidToId[$toUuid],
                    'direction' => LearningGraphEdgeDirection::from($edgeData['direction']),
                    'label' => $edgeData['label'] ? trim($edgeData['label']) : null,
                ];

                if (! empty($edgeData['id']) && $existingEdges->has($edgeData['id'])) {
                    $edgeModel = $existingEdges->get($edgeData['id']);
                    $edgeModel->update($payload);
                } else {
                    $edgeModel = $graph->edges()->create($payload);
                    $edgeData['id'] = $edgeModel->id;
                }

                $edgeIdsToKeep[] = $edgeModel->id;
            }

            $graph->edges()->whereNotIn('id', $edgeIdsToKeep)->each(function (LearningGraphEdge $edge) {
                $edge->delete();
            });
        });

        $this->loadStateFromModel();

        session()->flash('success', __('Learning graph saved.'));
        $this->redirectRoute('admin.learning-graphs.edit', [$this->group, $this->graph]);
    }

    protected function loadStateFromModel(): void
    {
        if (! $this->graph || ! $this->graph->exists) {
            $this->nodes = [];
            $this->edges = [];

            return;
        }

        $nodes = $this->graph->nodes()->with('blocks')->get();

        $this->nodes = $nodes->map(function (LearningGraphNode $node) {
            return [
                'uuid' => (string) Str::uuid(),
                'id' => $node->id,
                'title' => $node->title,
                'level' => $node->level,
                'order' => $node->order_column,
                'blocks' => $node->blocks->map(function (LearningGraphNodeBlock $block) {
                    return [
                        'uuid' => (string) Str::uuid(),
                        'id' => $block->id,
                        'type' => $block->type->value,
                        'content' => $block->type === LearningGraphBlockType::Text ? $block->content : null,
                        'image_path' => $block->type === LearningGraphBlockType::Image ? $block->image_path : null,
                        'order' => $block->order_column,
                        'upload' => null,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        $idToUuid = [];
        foreach ($this->nodes as $node) {
            if (isset($node['id'])) {
                $idToUuid[$node['id']] = $node['uuid'];
            }
        }

        $this->edges = $this->graph->edges()->get()->map(function (LearningGraphEdge $edge) use ($idToUuid) {
            $fromUuid = $idToUuid[$edge->from_node_id] ?? null;
            $toUuid = $idToUuid[$edge->to_node_id] ?? null;

            if (! $fromUuid || ! $toUuid) {
                return null;
            }

            return [
                'uuid' => (string) Str::uuid(),
                'id' => $edge->id,
                'from' => $fromUuid,
                'to' => $toUuid,
                'direction' => $edge->direction->value,
                'label' => $edge->label,
            ];
        })->filter()->values()->toArray();
    }

    protected function findNodeIndex(string $uuid): ?int
    {
        foreach ($this->nodes as $index => $node) {
            if (($node['uuid'] ?? null) === $uuid) {
                return $index;
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.admin.learning-graphs.graph-form', [
            'statuses' => LearningGraphStatus::cases(),
            'directions' => LearningGraphEdgeDirection::cases(),
            'blockTypes' => LearningGraphBlockType::cases(),
            'nodeOptions' => collect($this->nodes)->map(fn ($node) => [
                'uuid' => $node['uuid'],
                'title' => $node['title'] ?: __('Untitled node'),
            ])->values()->toArray(),
        ]);
    }
}

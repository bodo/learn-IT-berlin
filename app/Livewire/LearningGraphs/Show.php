<?php

namespace App\Livewire\LearningGraphs;

use App\Enums\LearningGraphBlockType;
use App\Models\Group;
use App\Models\LearningGraph;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Group $group;
    public LearningGraph $graph;
    public array $networkNodes = [];
    public array $networkEdges = [];
    public array $nodeDetails = [];
    public bool $canManage = false;

    public function mount(Group $group, LearningGraph $graph): void
    {
        abort_unless($graph->group_id === $group->id, 404);

        $graph->load(['nodes.blocks', 'edges']);
        $this->canManage = $group->canManage(Auth::user());

        if (! $graph->isPublished() && ! $this->canManage) {
            abort(404);
        }

        $this->group = $group;
        $this->graph = $graph;

        $this->prepareGraphData();
    }

    protected function prepareGraphData(): void
    {
        $nodes = $this->graph->nodes->sortBy(fn ($node) => [$node->level, $node->order_column, $node->id]);
        $this->networkNodes = [];
        $this->nodeDetails = [];

        foreach ($nodes as $node) {
            $blocks = [];
            foreach ($node->blocks as $block) {
                if ($block->type === LearningGraphBlockType::Text) {
                    $blocks[] = [
                        'type' => 'text',
                        'html' => $block->contentHtml(),
                    ];
                } elseif ($block->type === LearningGraphBlockType::Image && $block->imageUrl()) {
                    $blocks[] = [
                        'type' => 'image',
                        'url' => $block->imageUrl(),
                    ];
                }
            }

            $tooltip = collect($blocks)
                ->map(function ($block) {
                    return match ($block['type']) {
                        'text' => $block['html'],
                        'image' => '<img src="'.$block['url'].'" alt="" style="max-width: 240px; border-radius: 0.5rem;" />',
                        default => '',
                    };
                })
                ->filter()
                ->implode('<hr class="my-2" />');

            $this->networkNodes[] = [
                'id' => $node->id,
                'label' => $node->title,
                'level' => $node->level,
                'shape' => 'box',
                'margin' => 12,
                'font' => ['multi' => true],
                'widthConstraint' => ['maximum' => 260],
                'title' => $tooltip,
            ];

            $this->nodeDetails[$node->id] = [
                'title' => $node->title,
                'blocks' => $blocks,
                'level' => $node->level,
                'order' => $node->order_column,
            ];
        }

        $this->networkEdges = $this->graph->edges
            ->map(function ($edge) {
                $payload = [
                    'id' => $edge->id,
                    'from' => $edge->from_node_id,
                    'to' => $edge->to_node_id,
                    'arrows' => $edge->arrows(),
                    'smooth' => ['type' => 'curvedCW', 'roundness' => 0.2],
                ];

                if ($edge->label) {
                    $payload['label'] = $edge->label;
                    $payload['font'] = ['align' => 'top'];
                }

                return $payload;
            })
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.learning-graphs.show', [
            'networkNodes' => $this->networkNodes,
            'networkEdges' => $this->networkEdges,
            'nodeDetails' => $this->nodeDetails,
            'canManage' => $this->canManage,
        ])->layout('components.layouts.public');
    }
}

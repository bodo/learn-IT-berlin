<?php

namespace App\Livewire\Admin\LearningGraphs;

use App\Enums\LearningGraphStatus;
use App\Models\Group;
use App\Models\LearningGraph;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GraphIndex extends Component
{
    public Group $group;
    public string $title = '';
    public string $status = LearningGraphStatus::Draft->value;

    public function mount(Group $group): void
    {
        abort_unless($group->canManage(Auth::user()), 403);

        $this->group = $group;
    }

    public function createGraph(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(LearningGraphStatus::class)],
        ]);

        $graph = LearningGraph::create([
            'group_id' => $this->group->id,
            'title' => $validated['title'],
            'status' => LearningGraphStatus::from($validated['status']),
        ]);

        session()->flash('success', __('Learning graph created.'));
        $this->redirectRoute('admin.learning-graphs.edit', [$this->group, $graph]);
    }

    public function deleteGraph(int $graphId): void
    {
        $graph = $this->group->learningGraphs()->whereKey($graphId)->firstOrFail();
        $graph->delete();

        session()->flash('success', __('Learning graph deleted.'));
    }

    public function render()
    {
        $graphs = $this->group->learningGraphs()
            ->withCount(['nodes'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.learning-graphs.graph-index', [
            'graphs' => $graphs,
            'statuses' => LearningGraphStatus::cases(),
        ]);
    }
}

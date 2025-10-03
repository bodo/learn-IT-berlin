<?php

namespace App\Livewire\LearningGraphs;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListByGroup extends Component
{
    public Group $group;
    public bool $canManage = false;

    public function mount(Group $group): void
    {
        $this->group = $group;
        $this->canManage = $group->canManage(Auth::user());
    }

    public function render()
    {
        $query = $this->group->learningGraphs()->withCount('nodes')->orderBy('title');

        if (! $this->canManage) {
            $query->published();
        }

        $graphs = $query->get();

        return view('livewire.learning-graphs.list-by-group', [
            'graphs' => $graphs,
        ]);
    }
}

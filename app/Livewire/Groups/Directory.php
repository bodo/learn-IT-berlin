<?php

namespace App\Livewire\Groups;

use App\Models\Group;
use Livewire\Component;
use Livewire\WithPagination;

class Directory extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $groups = Group::query()
            ->withCount(['allUsers as members_count'])
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->orderBy('title')
            ->paginate(12);

        return view('livewire.groups.directory', [
            'groups' => $groups,
        ]);
    }
}

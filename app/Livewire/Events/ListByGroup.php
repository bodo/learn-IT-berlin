<?php

namespace App\Livewire\Events;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListByGroup extends Component
{
    use WithPagination;

    public Group $group;

    public function mount(Group $group): void
    {
        $this->group = $group;
    }

    public function render()
    {
        $events = $this->group->events()
            ->published()
            ->upcoming()
            ->orderBy('event_datetime')
            ->paginate(9);

        return view('livewire.events.list-by-group', [
            'events' => $events,
            'group' => $this->group,
            'canManage' => $this->group->canManage(Auth::user()),
        ]);
    }
}

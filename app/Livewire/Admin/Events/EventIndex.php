<?php

namespace App\Livewire\Admin\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EventIndex extends Component
{
    use WithPagination;

    public Group $group;
    public string $statusFilter = 'all';

    public function mount(Group $group): void
    {
        abort_unless($group->canManage(Auth::user()), 403);
        $this->group = $group;
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteEvent(int $eventId): void
    {
        $event = $this->group->events()->findOrFail($eventId);
        $event->delete();

        session()->flash('success', __('Event deleted.'));
        $this->resetPage();
    }

    public function render()
    {
        $events = $this->group->events()
            ->withCount('images')
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('event_datetime')
            ->paginate(10);

        return view('livewire.admin.events.event-index', [
            'events' => $events,
            'statuses' => collect(EventStatus::cases())
                ->map(fn (EventStatus $status) => ['value' => $status->value, 'label' => $status->label()])
                ->prepend(['value' => 'all', 'label' => __('All statuses')])
                ->toArray(),
        ]);
    }
}

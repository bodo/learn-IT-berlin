<?php

namespace App\Livewire\Events;

use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class WaitlistAdmin extends Component
{
    public Event $event;

    protected $listeners = [
        'rsvp-updated' => '$refresh',
    ];

    public function mount(Event $event): void
    {
        abort_unless($event->group->canManage(Auth::user()), 403);

        $this->event = $event;
    }

    public function render()
    {
        if (! Schema::hasTable('event_rsvps')) {
            return view('livewire.events.waitlist-admin', [
                'confirmed' => collect(),
                'waitlist' => collect(),
                'interested' => collect(),
            ]);
        }

        $confirmed = EventRsvp::query()
            ->where('event_id', $this->event->id)
            ->where('status', RsvpStatus::Going->value)
            ->whereNull('waitlist_position')
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $waitlist = EventRsvp::query()
            ->where('event_id', $this->event->id)
            ->where('status', RsvpStatus::Going->value)
            ->whereNotNull('waitlist_position')
            ->with('user')
            ->orderBy('waitlist_position')
            ->get();

        $interested = EventRsvp::query()
            ->where('event_id', $this->event->id)
            ->where('status', RsvpStatus::Interested->value)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('livewire.events.waitlist-admin', [
            'confirmed' => $confirmed,
            'waitlist' => $waitlist,
            'interested' => $interested,
        ]);
    }
}


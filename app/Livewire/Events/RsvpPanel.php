<?php

namespace App\Livewire\Events;

use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Services\RsvpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class RsvpPanel extends Component
{
    public Event $event;

    public ?RsvpStatus $myStatus = null;
    public ?int $myWaitlistPos = null;

    public function mount(Event $event): void
    {
        $this->event = $event->load('group');
        $this->loadMyRsvp();
    }

    public function setStatus(string $status): void
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        $enum = RsvpStatus::from($status);

        app(RsvpService::class)->setStatus($this->event, $user, $enum);

        $this->event->refresh();
        $this->loadMyRsvp();
        $this->dispatch('rsvp-updated');
    }

    private function loadMyRsvp(): void
    {
        $this->myStatus = null;
        $this->myWaitlistPos = null;
        if (! Auth::check() || ! Schema::hasTable('event_rsvps')) {
            return;
        }

        /** @var EventRsvp|null $rsvp */
        $rsvp = $this->event->rsvps()
            ->where('user_id', Auth::id())
            ->first();

        if ($rsvp) {
            $this->myStatus = $rsvp->status;
            $this->myWaitlistPos = $rsvp->waitlist_position;
        }
    }

    public function render()
    {
        $goingConfirmed = $this->event->confirmedAttendees()->count();
        $interested = $this->event->rsvps()->where('status', RsvpStatus::Interested->value)->count();
        $waitlistCount = $this->event->rsvps()->onWaitlist()->count();

        $attendees = $this->event->rsvps()
            ->where('status', RsvpStatus::Going->value)
            ->whereNull('waitlist_position')
            ->with('user')
            ->orderBy('created_at')
            ->take(12)
            ->get()
            ->pluck('user');

        return view('livewire.events.rsvp-panel', [
            'goingConfirmed' => $goingConfirmed,
            'interested' => $interested,
            'waitlistCount' => $waitlistCount,
            'attendees' => $attendees,
        ]);
    }
}


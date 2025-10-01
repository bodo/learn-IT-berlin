<?php

namespace App\Livewire;

use App\Enums\EventStatus;
use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $upcomingEvents = Schema::hasTable('events')
            ? Event::query()
                ->published()
                ->orderBy('event_datetime')
                ->with('group')
                ->take(6)
                ->get()
            : collect();

        $confirmedRsvps = collect();
        $waitlistRsvps = collect();
        $interestedRsvps = collect();
        if ($user && Schema::hasTable('event_rsvps') && Schema::hasTable('events')) {
            $baseRsvp = EventRsvp::query()
                ->select('event_rsvps.*')
                ->join('events', 'events.id', '=', 'event_rsvps.event_id')
                ->where('event_rsvps.user_id', $user->id)
                ->where('events.status', EventStatus::Published->value)
                ->where('events.event_datetime', '>=', now())
                ->with(['event.group'])
                ->orderBy('events.event_datetime');

            $confirmedRsvps = (clone $baseRsvp)
                ->where('event_rsvps.status', RsvpStatus::Going->value)
                ->whereNull('event_rsvps.waitlist_position')
                ->take(6)
                ->get();

            $waitlistRsvps = (clone $baseRsvp)
                ->where('event_rsvps.status', RsvpStatus::Going->value)
                ->whereNotNull('event_rsvps.waitlist_position')
                ->orderBy('event_rsvps.waitlist_position')
                ->take(6)
                ->get();

            $interestedRsvps = (clone $baseRsvp)
                ->where('event_rsvps.status', RsvpStatus::Interested->value)
                ->take(6)
                ->get();
        }
        $recentActivity = collect();

        return view('livewire.dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'confirmedRsvps' => $confirmedRsvps,
            'waitlistRsvps' => $waitlistRsvps,
            'interestedRsvps' => $interestedRsvps,
            'recentActivity' => $recentActivity,
            'user' => $user,
        ])->layout('components.layouts.public');
    }
}

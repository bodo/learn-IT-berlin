<?php

namespace App\Livewire;

use App\Models\Event;
use App\Enums\RsvpStatus;
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

        $rsvpEvents = collect();
        if ($user && Schema::hasTable('event_rsvps') && Schema::hasTable('events')) {
            $rsvpEvents = Event::query()
                ->select('events.*')
                ->join('event_rsvps', 'event_rsvps.event_id', '=', 'events.id')
                ->where('event_rsvps.user_id', $user->id)
                ->where('event_rsvps.status', RsvpStatus::Going->value)
                ->whereNull('event_rsvps.waitlist_position')
                ->published()
                ->upcoming()
                ->orderBy('events.event_datetime')
                ->with('group')
                ->take(6)
                ->get();
        }
        $recentActivity = collect();

        return view('livewire.dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'rsvpEvents' => $rsvpEvents,
            'recentActivity' => $recentActivity,
            'user' => $user,
        ])->layout('components.layouts.public');
    }
}

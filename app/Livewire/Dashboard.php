<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $upcomingEvents = Event::query()
            ->published()
            ->orderBy('event_datetime')
            ->with('group')
            ->take(6)
            ->get();

        $rsvpEvents = collect();
        $recentActivity = collect();

        return view('livewire.dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'rsvpEvents' => $rsvpEvents,
            'recentActivity' => $recentActivity,
            'user' => $user,
        ])->layout('components.layouts.public');
    }
}

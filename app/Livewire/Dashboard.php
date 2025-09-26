<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Placeholder collections until event/comment features are implemented.
        $upcomingEvents = collect();
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

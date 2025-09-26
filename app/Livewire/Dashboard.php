<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // For now, just show a placeholder since we don't have events yet
        // This will be populated when events system is implemented
        $upcomingEvents = collect(); // Empty collection for now

        return view('livewire.dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'user' => auth()->user(),
        ])->layout('components.layouts.public');
    }
}

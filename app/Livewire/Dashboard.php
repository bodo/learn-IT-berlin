<?php

namespace App\Livewire;

use App\Models\Event;
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
        $recentActivity = collect();

        return view('livewire.dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'rsvpEvents' => $rsvpEvents,
            'recentActivity' => $recentActivity,
            'user' => $user,
        ])->layout('components.layouts.public');
    }
}

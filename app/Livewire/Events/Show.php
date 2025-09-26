<?php

namespace App\Livewire\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Event $event;
    public bool $canManage = false;

    public function mount(Event $event): void
    {
        if ($event->status !== EventStatus::Published && ! $event->group->canManage(Auth::user())) {
            abort(404);
        }

        $this->event = $event->load(['group', 'images']);
        $this->canManage = $event->group->canManage(Auth::user());
    }

    public function render()
    {
        return view('livewire.events.show');
    }
}

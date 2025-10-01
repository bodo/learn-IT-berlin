<?php

namespace App\Livewire\Notifications;

use App\Notifications\PendingCommentsDigest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Bell extends Component
{
    public int $unreadCount = 0;

    public function markAsRead(string $notificationId): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->whereKey($notificationId)->first();
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render()
    {
        $user = Auth::user();
        $notifications = collect();
        $this->unreadCount = 0;

        if ($user) {
            $this->unreadCount = $user->unreadNotifications()->count();
            $notifications = $user->notifications()
                ->where('type', PendingCommentsDigest::class)
                ->latest()
                ->take(6)
                ->get();
        }

        return view('livewire.notifications.bell', [
            'notifications' => $notifications,
        ]);
    }
}


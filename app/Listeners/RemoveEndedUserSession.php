<?php

namespace App\Listeners;

use App\Models\UserSession;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;

class RemoveEndedUserSession
{
    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $sessionId = Session::getId();

        UserSession::where('user_id', $event->user->getAuthIdentifier())
            ->where('session_id', $sessionId)
            ->delete();
    }
}

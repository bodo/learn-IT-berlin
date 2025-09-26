<?php

namespace App\Livewire\Settings;

use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SessionManager extends Component
{
    public function confirmLogoutOtherSessions(): void
    {
        $this->dispatch('confirming-logout-other-sessions');
    }

    public function logoutOtherSessions(): void
    {
        $currentSessionId = Session::getId();
        $userId = Auth::id();

        $sessions = UserSession::where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->get();

        foreach ($sessions as $userSession) {
            Session::getHandler()->destroy($userSession->session_id);
            $userSession->delete();
        }

        session()->flash('session-update', __('Other sessions have been logged out.'));
    }

    public function logoutSingleSession(int $id): void
    {
        $userSession = UserSession::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if ($userSession->session_id === Session::getId()) {
            return;
        }

        Session::getHandler()->destroy($userSession->session_id);
        $userSession->delete();

        session()->flash('session-update', __('Selected session has been logged out.'));
    }

    public function render()
    {
        $currentSessionId = Session::getId();

        $sessions = Auth::user()
            ->userSessions()
            ->orderByDesc('last_active_at')
            ->get()
            ->map(function (UserSession $session) use ($currentSessionId) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_active_at' => $session->last_active_at?->diffForHumans() ?? __('Unknown'),
                    'current' => $session->session_id === $currentSessionId,
                ];
            });

        return view('livewire.settings.session-manager', [
            'sessions' => $sessions,
        ]);
    }
}

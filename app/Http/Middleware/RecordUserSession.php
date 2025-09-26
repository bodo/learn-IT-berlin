<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RecordUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!Auth::check()) {
            return $response;
        }

        $user = Auth::user();
        $sessionId = Session::getId();

        UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->header('User-Agent'),
                'last_active_at' => Date::now(),
            ],
        );

        return $response;
    }
}

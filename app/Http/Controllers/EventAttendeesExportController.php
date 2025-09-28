<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventAttendeesExportController extends Controller
{
    public function __invoke(Request $request, Event $event): StreamedResponse
    {
        $user = $request->user();
        abort_unless($event->group->canManage($user), 403);

        $filename = 'attendees-event-'.$event->id.'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($event) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'RSVP At']);

            $event->rsvps()
                ->whereNull('waitlist_position')
                ->where('status', 'going')
                ->with('user')
                ->orderBy('created_at')
                ->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $rsvp) {
                        fputcsv($out, [
                            $rsvp->user->display_name ?? $rsvp->user->name,
                            $rsvp->user->email,
                            $rsvp->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}


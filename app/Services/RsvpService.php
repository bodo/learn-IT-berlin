<?php

namespace App\Services;

use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RsvpService
{
    public function setStatus(Event $event, User $user, RsvpStatus $status): EventRsvp
    {
        return DB::transaction(function () use ($event, $user, $status) {
            /** @var EventRsvp $rsvp */
            $rsvp = EventRsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $rsvp) {
                $rsvp = new EventRsvp([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ]);
            }

            $rsvp->status = $status;
            // Interested or NotGoing never on waitlist
            if ($status !== RsvpStatus::Going) {
                $rsvp->waitlist_position = null;
            }

            $rsvp->save();

            // Recompute waitlist + reserved spots whenever an RSVP changes
            $event->refresh();
            $event->recalcRsvps();

            return $rsvp->refresh();
        });
    }
}


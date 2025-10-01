<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PendingCommentsDigest extends Notification
{
    use Queueable;

    public function __construct(
        protected Event $event,
        protected Comment $comment,
        protected int $count = 1
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $event = $this->event;
        $group = $event->group;

        return [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'group_title' => $group?->title,
            'count' => $this->count,
            'latest_comment_id' => $this->comment->id,
            'latest_excerpt' => $this->comment->excerpt(120),
            'event_url' => route('events.show', $event),
            'received_at' => now()->toIso8601String(),
        ];
    }
}


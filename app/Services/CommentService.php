<?php

namespace App\Services;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\CommentModerationLog;
use App\Models\CommentReport;
use App\Models\Event;
use App\Models\User;
use App\Notifications\PendingCommentsDigest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommentService
{
    /**
     * Submit a new comment for an event.
     */
    public function submit(Event $event, User $author, string $rawContent): Comment
    {
        $content = $this->sanitizeContent($rawContent);
        $this->assertContentAllowed($content);

        return DB::transaction(function () use ($event, $author, $content) {
            $comment = new Comment([
                'event_id' => $event->id,
                'user_id' => $author->id,
                'content' => $content,
            ]);

            if ($author->bypassesModeration()) {
                $comment->status = CommentStatus::Approved;
                $comment->approved_by = $author->id;
                $comment->approved_at = now();
            } else {
                $comment->status = CommentStatus::Pending;
            }

            $comment->save();
            $this->log($comment, $author, 'created');

            if ($comment->isApproved()) {
                $this->log($comment, $author, 'auto_approved');
            } else {
                $this->notifyModerators($comment);
            }

            return $comment->fresh(['user', 'approvedBy']);
        });
    }

    /**
     * Update comment content. Returning comment (possibly pending again).
     */
    public function update(Comment $comment, User $actor, string $rawContent): Comment
    {
        $content = $this->sanitizeContent($rawContent);
        $this->assertContentAllowed($content);

        return DB::transaction(function () use ($comment, $actor, $content) {
            $comment->content = $content;

            $comment->save();
            $this->log($comment, $actor, 'edited');

            if (! $actor->bypassesModeration()) {
                $comment->markPending();
                $comment->save();
                $this->log($comment, $actor, 'reset_to_pending');
                $this->notifyModerators($comment);
            }

            return $comment->fresh(['user', 'approvedBy']);
        });
    }

    public function approve(Comment $comment, User $moderator, ?string $notes = null): Comment
    {
        return DB::transaction(function () use ($comment, $moderator, $notes) {
            $comment->markApproved($moderator);
            $comment->save();

            $this->log($comment, $moderator, 'approved', $notes);

            $moderator->notifications()
                ->where('type', PendingCommentsDigest::class)
                ->whereNull('read_at')
                ->where('data->event_id', $comment->event_id)
                ->update(['read_at' => now()]);

            return $comment->fresh(['user', 'approvedBy']);
        });
    }

    public function reject(Comment $comment, User $moderator, ?string $notes = null): Comment
    {
        return DB::transaction(function () use ($comment, $moderator, $notes) {
            $comment->markRejected($moderator);
            $comment->save();

            $this->log($comment, $moderator, 'rejected', $notes);

            $moderator->notifications()
                ->where('type', PendingCommentsDigest::class)
                ->whereNull('read_at')
                ->where('data->event_id', $comment->event_id)
                ->update(['read_at' => now()]);

            return $comment->fresh(['user', 'approvedBy']);
        });
    }

    public function delete(Comment $comment, User $actor): void
    {
        DB::transaction(function () use ($comment, $actor) {
            $comment->delete();
            $this->log($comment, $actor, 'deleted');
        });
    }

    public function report(Comment $comment, User $reporter, ?string $reason = null): CommentReport
    {
        return DB::transaction(function () use ($comment, $reporter, $reason) {
            $report = CommentReport::updateOrCreate([
                'comment_id' => $comment->id,
                'user_id' => $reporter->id,
            ], [
                'reason' => $reason,
            ]);

            $this->log($comment, $reporter, 'reported', $reason);

            $this->notifyModerators($comment);

            return $report;
        });
    }

    protected function sanitizeContent(string $content): string
    {
        $clean = Str::of($content)
            ->replaceMatches('/\r\n?/', "\n")
            ->stripTags()
            ->trim();

        return (string) $clean;
    }

    protected function assertContentAllowed(string $content): void
    {
        if (Str::of($content)->length() === 0) {
            throw ValidationException::withMessages([
                'content' => __('Comment cannot be empty.'),
            ]);
        }

        if (preg_match('/https?:\/\//i', $content)) {
            throw ValidationException::withMessages([
                'content' => __('Links are not allowed in comments.'),
            ]);
        }

        $banned = config('content.moderation.banned_words', ['spam', 'shit', 'fuck']);
        $lower = Str::lower($content);
        foreach ($banned as $word) {
            $word = Str::lower($word);
            if ($word !== '' && Str::contains($lower, $word)) {
                throw ValidationException::withMessages([
                    'content' => __('Please remove inappropriate language.'),
                ]);
            }
        }
    }

    protected function notifyModerators(Comment $comment): void
    {
        if ($comment->isApproved()) {
            return;
        }

        $event = $comment->event()->with(['group.owners', 'group.moderators'])->first();
        $group = $event?->group;
        if (! $group) {
            return;
        }

        $moderators = $group->owners
            ->merge($group->moderators)
            ->filter(fn (User $user) => $user->id !== $comment->user_id)
            ->unique('id');

        foreach ($moderators as $moderator) {
            $existing = $moderator->notifications()
                ->where('type', PendingCommentsDigest::class)
                ->whereNull('read_at')
                ->where('data->event_id', $comment->event_id)
                ->first();

            if ($existing) {
                $data = $existing->data;
                $count = Arr::get($data, 'count', 1) + 1;
                $existing->forceFill([
                    'data' => array_merge($data, [
                        'count' => $count,
                        'latest_comment_id' => $comment->id,
                        'latest_excerpt' => $comment->excerpt(),
                    ]),
                ]);
                $existing->save();
            } else {
                $moderator->notify(new PendingCommentsDigest($event, $comment));
            }
        }
    }

    protected function log(Comment $comment, User $actor, string $action, ?string $notes = null): void
    {
        CommentModerationLog::create([
            'comment_id' => $comment->id,
            'user_id' => $actor->id,
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}

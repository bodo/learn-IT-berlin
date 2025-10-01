<?php

namespace App\Livewire\Events;

use App\Models\Comment;
use App\Models\Event;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Comments extends Component
{
    use AuthorizesRequests;

    public Event $event;

    public string $newComment = '';
    public ?int $editingId = null;
    public string $editComment = '';
    public ?int $reportingId = null;
    public string $reportReason = '';

    public int $maxLength = 500;

    protected $listeners = [
        'comment-updated' => '$refresh',
    ];

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    public function submit(): void
    {
        $this->authorize('create', Comment::class);

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $this->validate([
            'newComment' => ['required', 'string', 'min:3', 'max:'.$this->maxLength],
        ], [], [
            'newComment' => __('Comment'),
        ]);

        $key = $this->rateLimitKey($user->id);
        $maxAttempts = config('content.moderation.rate_limit.max_attempts', 5);
        $decay = config('content.moderation.rate_limit.decay_seconds', 60);
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'newComment' => __('Please slow down. Try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        RateLimiter::hit($key, $decay);

        app(CommentService::class)->submit($this->event->fresh(), $user, $this->newComment);

        $this->reset(['newComment']);
        $this->event->refresh();
        $this->dispatch('comment-updated')->self();
    }

    public function startEditing(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('update', $comment);

        $this->editingId = $comment->id;
        $this->editComment = $comment->content;
    }

    public function cancelEditing(): void
    {
        $this->reset(['editingId', 'editComment']);
    }

    public function saveEdit(): void
    {
        if (! $this->editingId) {
            return;
        }

        $comment = $this->findComment($this->editingId);
        $this->authorize('update', $comment);

        $this->validate([
            'editComment' => ['required', 'string', 'min:3', 'max:'.$this->maxLength],
        ], [], [
            'editComment' => __('Comment'),
        ]);

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        app(CommentService::class)->update($comment, $user, $this->editComment);

        $this->cancelEditing();
        $this->event->refresh();
        $this->dispatch('comment-updated')->self();
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('delete', $comment);

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        app(CommentService::class)->delete($comment, $user);

        $this->event->refresh();
        $this->dispatch('comment-updated')->self();
    }

    public function startReport(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('report', $comment);

        $this->reportingId = $comment->id;
        $this->reportReason = '';
    }

    public function submitReport(): void
    {
        if (! $this->reportingId) {
            return;
        }

        $comment = $this->findComment($this->reportingId);
        $this->authorize('report', $comment);

        $this->validate([
            'reportReason' => ['nullable', 'string', 'max:200'],
        ], [], [
            'reportReason' => __('Reason'),
        ]);

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        app(CommentService::class)->report($comment, $user, $this->reportReason ?: null);

        $this->reset(['reportingId', 'reportReason']);
        $this->event->refresh();
        $this->dispatch('comment-updated')->self();
    }

    public function cancelReport(): void
    {
        $this->reset(['reportingId', 'reportReason']);
    }

    public function render()
    {
        $userId = Auth::id();
        $commentsQuery = $this->event->comments()
            ->with(['user', 'approvedBy', 'reports'])
            ->orderBy('created_at');

        if ($userId) {
            $commentsQuery->where(function ($query) use ($userId) {
                $query->approved()
                    ->orWhere('user_id', $userId);
            });
        } else {
            $commentsQuery->approved();
        }

        $comments = $commentsQuery->get();

        $approvedCount = $this->event->comments()->approved()->count();

        return view('livewire.events.comments', [
            'comments' => $comments,
            'approvedCount' => $approvedCount,
        ]);
    }

    protected function rateLimitKey(int $userId): string
    {
        return 'comment-submit:'.$userId;
    }

    protected function findComment(int $commentId): Comment
    {
        return $this->event->comments()->whereKey($commentId)->firstOrFail();
    }
}

<?php

namespace App\Livewire\Moderation;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Notifications\PendingCommentsDigest;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CommentsQueue extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $status = 'pending';
    public array $selected = [];
    public ?int $focusCommentId = null;
    public string $moderationNote = '';
    public array $visible = [];

    protected array $groupIds = [];

    protected $queryString = [
        'status' => ['except' => 'pending'],
    ];

    protected $listeners = [
        'comment-updated' => '$refresh',
    ];

    public function mount(): void
    {
        $this->authorize('moderate-comments');
        $this->groupIds = $this->resolveGroupIds();
        $this->markNotificationsRead();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function toggleSelectAll(): void
    {
        $visible = collect($this->visible);
        if ($visible->isEmpty()) {
            return;
        }

        $currentlySelected = collect($this->selected);
        if ($visible->diff($currentlySelected)->isEmpty()) {
            $this->selected = $currentlySelected->diff($visible)->values()->all();
        } else {
            $this->selected = $currentlySelected->merge($visible)->unique()->values()->all();
        }
    }

    public function setFocus(int $commentId): void
    {
        $comment = $this->findCommentForModeration($commentId);
        $this->focusCommentId = $comment?->id;
        $this->moderationNote = '';
    }

    public function clearFocus(): void
    {
        $this->focusCommentId = null;
        $this->moderationNote = '';
    }

    public function approveComment(int $commentId): void
    {
        $comment = $this->findCommentForModeration($commentId);
        if (! $comment) {
            return;
        }

        $this->authorize('approve', $comment);
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        app(CommentService::class)->approve($comment, $user);

        $this->afterModeration($commentId);
    }

    public function rejectComment(int $commentId): void
    {
        $comment = $this->findCommentForModeration($commentId);
        if (! $comment) {
            return;
        }

        $this->authorize('reject', $comment);
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        app(CommentService::class)->reject($comment, $user, $this->moderationNote ?: null);

        $this->afterModeration($commentId);
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->findCommentForModeration($commentId);
        if (! $comment) {
            return;
        }

        $this->authorize('delete', $comment);
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        app(CommentService::class)->delete($comment, $user);

        $this->afterModeration($commentId);
    }

    public function bulkApprove(): void
    {
        $this->processSelected(function (Comment $comment, $user) {
            $this->authorize('approve', $comment);
            app(CommentService::class)->approve($comment, $user);
        });
    }

    public function bulkReject(): void
    {
        $this->processSelected(function (Comment $comment, $user) {
            $this->authorize('reject', $comment);
            app(CommentService::class)->reject($comment, $user, null);
        });
    }

    public function bulkDelete(): void
    {
        $this->processSelected(function (Comment $comment, $user) {
            $this->authorize('delete', $comment);
            app(CommentService::class)->delete($comment, $user);
        });
    }

    public function render()
    {
        $comments = $this->commentsQuery()->paginate(15);
        $focus = null;
        if ($this->focusCommentId) {
            $focus = Comment::with(['user', 'event.group', 'reports.user', 'moderationLogs.user'])
                ->whereKey($this->focusCommentId)
                ->whereHas('event', function ($query) {
                    $query->whereIn('group_id', $this->groupIds);
                })
                ->first();
        }

        $this->visible = $comments->pluck('id')->map(fn ($id) => (int) $id)->all();

        return view('livewire.moderation.comments-queue', [
            'comments' => $comments,
            'focusComment' => $focus,
        ])->layout('components.layouts.public');
    }

    protected function resolveGroupIds(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $owned = $user->ownedGroups()->pluck('groups.id');
        $moderated = $user->moderatedGroups()->pluck('groups.id');

        return $owned->merge($moderated)->unique()->values()->all();
    }

    protected function markNotificationsRead(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications()
            ->where('type', PendingCommentsDigest::class)
            ->update(['read_at' => now()]);
    }

    protected function commentsQuery()
    {
        $query = Comment::query()
            ->with(['user', 'event.group', 'reports'])
            ->whereHas('event', function ($builder) {
                $builder->whereIn('group_id', $this->groupIds);
            })
            ->orderBy('created_at');

        if ($this->status === 'pending') {
            $query->pending();
        } elseif ($this->status === 'approved') {
            $query->approved();
        } elseif ($this->status === 'rejected') {
            $query->where('status', CommentStatus::Rejected->value);
        }

        return $query;
    }

    protected function findCommentForModeration(int $commentId): ?Comment
    {
        return Comment::query()
            ->whereKey($commentId)
            ->whereHas('event', function ($query) {
                $query->whereIn('group_id', $this->groupIds);
            })
            ->first();
    }

    protected function afterModeration(int $commentId): void
    {
        $this->selected = array_values(array_filter($this->selected, fn ($id) => (int) $id !== $commentId));
        if ($this->focusCommentId === $commentId) {
            $this->clearFocus();
        }
        $this->dispatch('comment-updated')->self();
    }

    protected function processSelected(callable $callback): void
    {
        $user = Auth::user();
        $ids = collect($this->selected)->map(fn ($id) => (int) $id)->filter()->all();
        if (! $user || empty($ids)) {
            return;
        }

        $comments = Comment::query()
            ->whereIn('id', $ids)
            ->whereHas('event', function ($query) {
                $query->whereIn('group_id', $this->groupIds);
            })
            ->get();

        foreach ($comments as $comment) {
            $callback($comment, $user);
        }

        $this->selected = [];
        if ($this->focusCommentId && in_array($this->focusCommentId, $ids, true)) {
            $this->clearFocus();
        }

        $this->dispatch('comment-updated')->self();
    }
}

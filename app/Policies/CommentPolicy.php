<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id && ! $comment->trashed();
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->trashed()) {
            return false;
        }

        return $comment->user_id === $user->id || $this->canModerate($user, $comment);
    }

    public function approve(User $user, Comment $comment): bool
    {
        return $this->canModerate($user, $comment);
    }

    public function reject(User $user, Comment $comment): bool
    {
        return $this->canModerate($user, $comment);
    }

    public function viewHistory(User $user, Comment $comment): bool
    {
        return $this->canModerate($user, $comment);
    }

    public function report(User $user, Comment $comment): bool
    {
        return $comment->user_id !== $user->id;
    }

    protected function canModerate(User $user, Comment $comment): bool
    {
        if ($user->isSuperuser() || $user->isAdmin()) {
            return true;
        }

        $event = $comment->event()->with('group')->first();
        $group = $event?->group;
        if (! $group) {
            return false;
        }

        return $group->isOwner($user) || $group->isModerator($user);
    }
}


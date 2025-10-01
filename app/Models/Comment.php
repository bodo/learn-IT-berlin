<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'content',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'status' => CommentStatus::class,
        'approved_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(CommentModerationLog::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Pending->value);
    }

    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    public function excerpt(int $limit = 80): string
    {
        return Str::of($this->content)->squish()->limit($limit);
    }

    public function markApproved(User $moderator): void
    {
        $this->status = CommentStatus::Approved;
        $this->approved_by = $moderator->getKey();
        $this->approved_at = now();
    }

    public function markPending(): void
    {
        $this->status = CommentStatus::Pending;
        $this->approved_by = null;
        $this->approved_at = null;
    }

    public function markRejected(User $moderator): void
    {
        $this->status = CommentStatus::Rejected;
        $this->approved_by = $moderator->getKey();
        $this->approved_at = now();
    }

    public function isPending(): bool
    {
        return $this->status === CommentStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === CommentStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === CommentStatus::Rejected;
    }
}

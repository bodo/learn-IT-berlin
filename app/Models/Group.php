<?php

namespace App\Models;

use App\Enums\GroupRole;
use App\Models\LearningGraph;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'banner_image_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', GroupRole::Owner->value);
    }

    public function moderators(): BelongsToMany
    {
        return $this->users()->wherePivot('role', GroupRole::Moderator->value);
    }

    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', GroupRole::Member->value);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function learningGraphs(): HasMany
    {
        return $this->hasMany(LearningGraph::class);
    }

    public function allUsers(): BelongsToMany
    {
        return $this->users();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function scopePublic($query)
    {
        return $query;
    }

    public function isOwner(User $user): bool
    {
        return $this->owners()->whereKey($user->getKey())->exists();
    }

    public function isModerator(User $user): bool
    {
        return $this->moderators()->whereKey($user->getKey())->exists();
    }

    public function hasMember(User $user): bool
    {
        return $this->allUsers()->whereKey($user->getKey())->exists();
    }

    public function canManage(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->isOwner($user) || $user->isAdmin();
    }

    public function bannerUrl(): ?string
    {
        if (! $this->banner_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->banner_image_path);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $group) {
            if ($group->banner_image_path && Storage::disk('public')->exists($group->banner_image_path)) {
                Storage::disk('public')->delete($group->banner_image_path);
            }
        });
    }

    public function assignRole(User $user, GroupRole $role): void
    {
        if ($this->users()->whereKey($user->getKey())->exists()) {
            $this->users()->updateExistingPivot($user->getKey(), [
                'role' => $role->value,
            ]);
        } else {
            $this->users()->attach($user->getKey(), [
                'role' => $role->value,
                'joined_at' => now(),
            ]);
        }
    }

    public function removeUser(User $user): void
    {
        $this->users()->detach($user->getKey());
    }
}

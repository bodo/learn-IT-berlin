<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Accessor for the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Human readable label for the role.
     */
    public function roleLabel(): string
    {
        return $this->role?->label() ?? UserRole::User->label();
    }

    public function isUser(): bool
    {
        return $this->role?->isAtLeast(UserRole::User) ?? false;
    }

    public function isTrustedUser(): bool
    {
        return $this->role?->isAtLeast(UserRole::TrustedUser) ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->role?->isAtLeast(UserRole::Admin) ?? false;
    }

    public function isSuperuser(): bool
    {
        return $this->role === UserRole::Superuser;
    }

    public function canManageGroups(): bool
    {
        return $this->isAdmin();
    }

    public function canModerateComments(): bool
    {
        return $this->isTrustedUser();
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperuser();
    }

    public function canCreateEvents(): bool
    {
        return $this->isAdmin();
    }

    public function bypassesModeration(): bool
    {
        return $this->isTrustedUser();
    }

    /**
     * Query scope helper to filter by a single role.
     */
    public function scopeWithRole(Builder $query, UserRole|string $role): Builder
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $query->where('role', $roleValue);
    }

    public function scopeUsers(Builder $query): Builder
    {
        return $query->where('role', UserRole::User->value);
    }

    public function scopeTrustedUsers(Builder $query): Builder
    {
        return $query->whereIn('role', [
            UserRole::TrustedUser->value,
            UserRole::Admin->value,
            UserRole::Superuser->value,
        ]);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', [
            UserRole::Admin->value,
            UserRole::Superuser->value,
        ]);
    }

    public function scopeSuperusers(Builder $query): Builder
    {
        return $query->where('role', UserRole::Superuser->value);
    }

    public function scopeByRole(Builder $query, UserRole|string $role): Builder
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $query->where('role', $roleValue);
    }
}

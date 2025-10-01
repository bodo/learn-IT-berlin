<?php

namespace App\Providers;

use App\Models\Comment;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;



class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Role-based gates
        Gate::define('manage-groups', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function ($user) {
            return $user->isSuperuser();
        });

        Gate::define('moderate-comments', function ($user) {
            return $user->isSuperuser()
                || $user->isAdmin()
                || $user->moderatedGroups()->exists()
                || $user->ownedGroups()->exists();
        });

        Gate::define('bypass-moderation', function ($user) {
            return $user->isTrustedUser();
        });

        Gate::define('create-events', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('rsvp-events', function ($user) {
            return true; // All authenticated users can RSVP
        });

        Gate::define('add-comments', function ($user) {
            return true; // All authenticated users can add comments
        });
    }
}

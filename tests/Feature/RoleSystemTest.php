<?php

use App\Enums\UserRole;
use App\Livewire\Admin\UserRoleManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('user can be assigned roles', function () {
    $user = User::factory()->create();

    $user->update(['role' => UserRole::User]);

    expect($user->isUser())->toBeTrue();
});

test('trusted user role methods work correctly', function () {
    $user = User::factory()->create();

    $user->update(['role' => UserRole::TrustedUser]);

    expect($user->isTrustedUser())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
    expect($user->isSuperuser())->toBeFalse();
});

test('admin role inherits lower permissions', function () {
    $user = User::factory()->create();

    $user->update(['role' => UserRole::Admin]);

    expect($user->isAdmin())->toBeTrue();
    expect($user->isTrustedUser())->toBeTrue();
    expect($user->isSuperuser())->toBeFalse();
});

test('superuser has all permissions', function () {
    $user = User::factory()->create();

    $user->update(['role' => UserRole::Superuser]);

    expect($user->isSuperuser())->toBeTrue();
    expect($user->isAdmin())->toBeTrue();
    expect($user->isTrustedUser())->toBeTrue();
});

test('role management component authorization works', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();
    $superuser = User::factory()->create();

    $user->update(['role' => UserRole::User]);
    $admin->update(['role' => UserRole::Admin]);
    $superuser->update(['role' => UserRole::Superuser]);

    // Test the mount method authorization directly
    $component = new UserRoleManager;

    // Regular user should get 403
    auth()->login($user);
    expect(fn () => $component->mount())->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    // Admin should get 403
    auth()->login($admin);
    expect(fn () => $component->mount())->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    // Superuser should not throw
    auth()->login($superuser);
    expect(fn () => $component->mount())->not->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('role manager updates user role', function () {
    $superuser = User::factory()->create(['role' => UserRole::Superuser]);
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($superuser);

    Livewire::test(UserRoleManager::class)
        ->call('changeRole', $user, UserRole::Admin->value);

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});

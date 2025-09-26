<?php

use App\Enums\GroupRole;
use App\Enums\UserRole;
use App\Livewire\Admin\Groups\GroupIndex;
use App\Livewire\Admin\Groups\GroupManage;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function admin(): User {
    return User::factory()->create(['role' => UserRole::Admin]);
}

function regularUser(): User {
    return User::factory()->create();
}

test('admin can create a group', function () {
    $admin = admin();
    $this->actingAs($admin);

    Livewire::test(GroupIndex::class)
        ->set('title', 'Algorithms Circle')
        ->set('description', 'Weekly study group for algorithm theory.')
        ->call('createGroup')
        ->assertHasNoErrors();

    $group = Group::whereTitle('Algorithms Circle')->first();

    expect($group)->not->toBeNull()
        ->and($group->owners()->where('users.id', $admin->id)->exists())->toBeTrue();
});

test('non admin cannot access group index', function () {
    $this->actingAs(regularUser());

    $this->get(route('admin.groups.index'))->assertForbidden();
});

test('owners can manage membership', function () {
    $admin = admin();
    $moderator = regularUser();
    $member = regularUser();

    $group = Group::factory()->create();
    $group->assignRole($admin, GroupRole::Owner);

    $this->actingAs($admin);

    Livewire::test(GroupManage::class, ['group' => $group])
        ->set('moderatorEmail', $moderator->email)
        ->call('addModerator')
        ->set('memberEmail', $member->email)
        ->call('addMember')
        ->assertHasNoErrors();

    $group->refresh();

    expect($group->moderators()->where('users.id', $moderator->id)->exists())->toBeTrue()
        ->and($group->members()->where('users.id', $member->id)->exists())->toBeTrue();

    // Removing owner demotes to member but preserves at least one owner
    $secondOwner = regularUser();
    $group->assignRole($secondOwner, GroupRole::Owner);

    $this->actingAs($admin);

    Livewire::test(GroupManage::class, ['group' => $group])
        ->call('removeOwner', $admin->id)
        ->assertHasNoErrors();

    expect($group->fresh()->owners()->count())->toBe(1)
        ->and($group->fresh()->members()->where('users.id', $admin->id)->exists())->toBeTrue();
});

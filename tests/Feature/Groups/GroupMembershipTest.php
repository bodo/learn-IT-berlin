<?php

use App\Enums\GroupRole;
use App\Livewire\Groups\Show as GroupShow;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('authenticated user can join and leave a group', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $this->actingAs($user);

    Livewire::test(GroupShow::class, ['group' => $group])
        ->call('toggleMembership')
        ->assertHasNoErrors()
        ->call('toggleMembership')
        ->assertHasNoErrors();

    expect($group->fresh()->allUsers()->where('users.id', $user->id)->exists())->toBeFalse();
});

test('owner cannot leave if they are the last owner', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->assignRole($user, GroupRole::Owner);

    $this->actingAs($user);

    Livewire::test(GroupShow::class, ['group' => $group])
        ->call('toggleMembership')
        ->assertSeeText(__('You must promote another owner before leaving.'));

    expect($group->fresh()->owners()->where('users.id', $user->id)->exists())->toBeTrue();
});

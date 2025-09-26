<?php

use App\Enums\EventStatus;
use App\Enums\GroupRole;
use App\Enums\UserRole;
use App\Livewire\Events\Show as EventShow;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('published events are viewable by guests', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
    ]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->assertSee($event->title)
        ->assertSee($event->group->title);
});

test('draft events are hidden from guests', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Draft,
    ]);

    $this->get(route('events.show', $event))->assertNotFound();
});

test('group owner can view draft event', function () {
    $owner = User::factory()->create(['role' => UserRole::Admin]);
    $group = Group::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);
    $event = Event::factory()->for($group)->create(['status' => EventStatus::Draft]);

    $this->actingAs($owner);

    Livewire::test(EventShow::class, ['event' => $event])
        ->assertSee($event->title);
});

<?php

use App\Enums\EventStatus;
use App\Enums\GroupRole;
use App\Enums\UserRole;
use App\Livewire\Admin\Events\EventForm;
use App\Livewire\Admin\Events\EventIndex;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('group owner can create a published event with image', function () {
    Storage::fake('public');

    $owner = User::factory()->create(['role' => UserRole::Admin]);
    $group = Group::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);

    $this->actingAs($owner);

    Livewire::test(EventForm::class, ['group' => $group])
        ->set('title', 'Graph Theory Workshop')
        ->set('description', 'Deep dive into graph algorithms.')
        ->set('place', 'Berlin Tech Hub')
        ->set('eventDate', now()->addWeek()->format('Y-m-d'))
        ->set('eventTime', '18:30')
        ->set('timezone', 'Europe/Berlin')
        ->set('maxSpots', 25)
        ->set('status', EventStatus::Published->value)
        ->set('newImages', [UploadedFile::fake()->image('banner.jpg')])
        ->call('save')
        ->assertSessionHas('success');

    $event = Event::whereTitle('Graph Theory Workshop')->firstOrFail();

    expect($event->status)->toBe(EventStatus::Published)
        ->and($event->max_spots)->toBe(25)
        ->and($event->group_id)->toBe($group->id)
        ->and($event->images()->count())->toBe(1);
});

test('superuser can access event creation for any group', function () {
    $superuser = User::factory()->create(['role' => UserRole::Superuser]);
    $group = Group::factory()->create();

    $this->actingAs($superuser)
        ->get(route('admin.events.create', $group))
        ->assertOk()
        ->assertSee(__('Create event'));
});

test('group owner can access event creation', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);

    $this->actingAs($owner)
        ->get(route('admin.events.create', $group))
        ->assertOk()
        ->assertSee(__('Create event'));
});

test('non owners cannot access event index', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('admin.events.index', $group))->assertForbidden();
});

test('event creator can delete event from index', function () {
    $owner = User::factory()->create(['role' => UserRole::Admin]);
    $group = Group::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);
    $event = Event::factory()->for($group)->create();

    $this->actingAs($owner);

    Livewire::test(EventIndex::class, ['group' => $group])
        ->call('deleteEvent', $event->id);

    expect(Event::find($event->id))->toBeNull();
});

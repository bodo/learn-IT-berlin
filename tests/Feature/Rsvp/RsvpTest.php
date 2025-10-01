<?php

use App\Enums\EventStatus;
use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use App\Services\RsvpService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows going on unlimited event without waitlist', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'max_spots' => null,
        'reserved_spots' => 0,
        'status' => EventStatus::Published,
    ]);

    actingAs($user);
    $service = app(RsvpService::class);
    $service->setStatus($event, $user, RsvpStatus::Going);

    $event->refresh();
    expect($event->reserved_spots)->toBe(1);

    $r = $event->rsvps()->where('user_id', $user->id)->first();
    expect($r->status)->toBe(RsvpStatus::Going)
        ->and($r->waitlist_position)->toBeNull();
});

it('waitlists when spots full and promotes on vacancy', function () {
    $group = Group::factory()->create();
    $event = Event::factory()->for($group)->create([
        'max_spots' => 1,
        'reserved_spots' => 0,
        'status' => EventStatus::Published,
    ]);

    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $service = app(RsvpService::class);
    $service->setStatus($event, $alice, RsvpStatus::Going);
    $service->setStatus($event, $bob, RsvpStatus::Going);

    $event->refresh();
    expect($event->reserved_spots)->toBe(1);

    $aliceR = $event->rsvps()->where('user_id', $alice->id)->firstOrFail();
    $bobR = $event->rsvps()->where('user_id', $bob->id)->firstOrFail();

    expect($aliceR->waitlist_position)->toBeNull();
    expect($bobR->waitlist_position)->toBe(1);

    // Alice cancels -> Bob promoted
    $service->setStatus($event, $alice, RsvpStatus::NotGoing);
    $event->refresh();

    $bobR->refresh();
    expect($event->reserved_spots)->toBe(1);
    expect($bobR->waitlist_position)->toBeNull();
});

it('interested does not affect reserved spots', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'max_spots' => 5,
        'reserved_spots' => 0,
        'status' => EventStatus::Published,
    ]);

    app(RsvpService::class)->setStatus($event, $user, RsvpStatus::Interested);
    $event->refresh();
    expect($event->reserved_spots)->toBe(0);
});

it('promotes waitlisted users when capacity increases', function () {
    $event = Event::factory()->create([
        'max_spots' => 1,
        'reserved_spots' => 0,
        'status' => EventStatus::Published,
    ]);

    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $service = app(RsvpService::class);
    $service->setStatus($event, $alice, RsvpStatus::Going);
    $service->setStatus($event, $bob, RsvpStatus::Going);

    $bobRsvp = $event->rsvps()->where('user_id', $bob->id)->firstOrFail();
    expect($bobRsvp->waitlist_position)->toBe(1);

    $event->update(['max_spots' => 2]);
    $event->refresh();
    $event->recalcRsvps();
    $event->refresh();

    $bobRsvp->refresh();
    expect($event->reserved_spots)->toBe(2);
    expect($bobRsvp->waitlist_position)->toBeNull();
});

it('switching from going to interested frees spot and promotes waitlist', function () {
    $event = Event::factory()->create([
        'max_spots' => 1,
        'reserved_spots' => 0,
        'status' => EventStatus::Published,
    ]);

    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $service = app(RsvpService::class);
    $service->setStatus($event, $alice, RsvpStatus::Going);
    $service->setStatus($event, $bob, RsvpStatus::Going);

    $service->setStatus($event, $alice, RsvpStatus::Interested);
    $event->refresh();

    $aliceRsvp = $event->rsvps()->where('user_id', $alice->id)->firstOrFail();
    $bobRsvp = $event->rsvps()->where('user_id', $bob->id)->firstOrFail();

    expect($aliceRsvp->status)->toBe(RsvpStatus::Interested);
    expect($aliceRsvp->waitlist_position)->toBeNull();
    expect($bobRsvp->waitlist_position)->toBeNull();
    expect($event->reserved_spots)->toBe(1);
});

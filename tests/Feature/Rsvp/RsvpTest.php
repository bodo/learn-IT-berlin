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

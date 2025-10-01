<?php

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createPublishedEvent(array $overrides = []): Event
{
    $defaults = [
        'status' => EventStatus::Published,
        'event_datetime' => Carbon::now()->addDays(3),
        'reserved_spots' => 0,
    ];

    return Event::factory()->for(Group::factory())->create(array_merge($defaults, $overrides));
}

it('filters published events by search term', function () {
    createPublishedEvent(['title' => 'Laravel Deep Dive']);
    createPublishedEvent(['title' => 'React Native Bootcamp']);

    $response = get(route('events.index', ['search' => 'Laravel']));

    $response->assertOk()
        ->assertSee('Deep Dive')
        ->assertDontSee('React Native Bootcamp')
        ->assertSee('<mark>Laravel</mark>', false);
});

it('supports today and tomorrow date filters', function () {
    $tz = config('app.timezone');
    $todayEvent = createPublishedEvent([
        'title' => 'Today Event',
        'event_datetime' => Carbon::now($tz)->startOfDay()->addHours(10)->setTimezone('UTC'),
    ]);

    $tomorrowEvent = createPublishedEvent([
        'title' => 'Tomorrow Event',
        'event_datetime' => Carbon::now($tz)->addDay()->startOfDay()->addHours(12)->setTimezone('UTC'),
    ]);

    $upcomingEvent = createPublishedEvent([
        'title' => 'Next Week Event',
        'event_datetime' => Carbon::now()->addDays(5),
    ]);

    get(route('events.index', ['filter' => 'today']))
        ->assertOk()
        ->assertSee($todayEvent->title)
        ->assertDontSee($tomorrowEvent->title)
        ->assertDontSee($upcomingEvent->title);

    get(route('events.index', ['filter' => 'tomorrow']))
        ->assertOk()
        ->assertSee($tomorrowEvent->title)
        ->assertDontSee($todayEvent->title);
});

it('paginates the feed', function () {
    $events = Event::factory()
        ->count(12)
        ->for(Group::factory())
        ->sequence(fn ($sequence) => [
            'title' => 'Event '.$sequence->index,
            'status' => EventStatus::Published,
            'event_datetime' => Carbon::now()->addDays($sequence->index + 1),
        ])
        ->create();

    $firstPage = get(route('events.index'));
    $firstPage->assertOk()
        ->assertSee('Event 1')
        ->assertSee('Event 8')
        ->assertDontSee('Event 10');

    $secondPage = get(route('events.index', ['page' => 2]));
    $secondPage->assertOk()
        ->assertSee('Event 10');
});

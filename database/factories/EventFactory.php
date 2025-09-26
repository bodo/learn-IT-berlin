<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $timezone = fake()->timezone();
        $start = Carbon::now($timezone)->addDays(fake()->numberBetween(1, 30))->setHour(fake()->numberBetween(9, 19));

        return [
            'group_id' => Group::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraphs(2, true),
            'place' => fake()->address(),
            'event_datetime' => $start,
            'timezone' => $timezone,
            'max_spots' => fake()->boolean(60) ? fake()->numberBetween(10, 100) : null,
            'reserved_spots' => 0,
            'status' => EventStatus::Draft,
        ];
    }
}

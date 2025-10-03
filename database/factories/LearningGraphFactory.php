<?php

namespace Database\Factories;

use App\Enums\LearningGraphStatus;
use App\Models\Group;
use App\Models\LearningGraph;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningGraph>
 */
class LearningGraphFactory extends Factory
{
    protected $model = LearningGraph::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'title' => $this->faker->sentence(4),
            'status' => LearningGraphStatus::Draft,
        ];
    }

    public function published(): self
    {
        return $this->state(fn () => ['status' => LearningGraphStatus::Published]);
    }
}

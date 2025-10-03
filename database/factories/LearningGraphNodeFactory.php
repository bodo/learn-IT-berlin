<?php

namespace Database\Factories;

use App\Models\LearningGraph;
use App\Models\LearningGraphNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningGraphNode>
 */
class LearningGraphNodeFactory extends Factory
{
    protected $model = LearningGraphNode::class;

    public function definition(): array
    {
        return [
            'learning_graph_id' => LearningGraph::factory(),
            'title' => $this->faker->sentence(3),
            'level' => $this->faker->numberBetween(0, 5),
            'order_column' => $this->faker->numberBetween(0, 10),
        ];
    }
}

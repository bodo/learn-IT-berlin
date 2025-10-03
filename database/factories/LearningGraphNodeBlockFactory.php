<?php

namespace Database\Factories;

use App\Enums\LearningGraphBlockType;
use App\Models\LearningGraphNode;
use App\Models\LearningGraphNodeBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningGraphNodeBlock>
 */
class LearningGraphNodeBlockFactory extends Factory
{
    protected $model = LearningGraphNodeBlock::class;

    public function definition(): array
    {
        return [
            'learning_graph_node_id' => LearningGraphNode::factory(),
            'type' => LearningGraphBlockType::Text,
            'content' => $this->faker->paragraph(),
            'image_path' => null,
            'order_column' => 0,
        ];
    }

    public function image(): self
    {
        return $this->state(fn () => [
            'type' => LearningGraphBlockType::Image,
            'content' => null,
            'image_path' => 'learning-graphs/example.jpg',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\LearningGraphEdgeDirection;
use App\Models\LearningGraph;
use App\Models\LearningGraphEdge;
use App\Models\LearningGraphNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningGraphEdge>
 */
class LearningGraphEdgeFactory extends Factory
{
    protected $model = LearningGraphEdge::class;

    public function definition(): array
    {
        return [
            'learning_graph_id' => null,
            'from_node_id' => null,
            'to_node_id' => null,
            'direction' => LearningGraphEdgeDirection::To,
            'label' => $this->faker->optional()->words(2, true),
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(function (LearningGraphEdge $edge) {
            $graph = $edge->learning_graph_id
                ? LearningGraph::find($edge->learning_graph_id)
                : null;

            if (! $graph) {
                $graph = LearningGraph::factory()->create();
            }

            $edge->learning_graph_id = $graph->id;

            if (! $edge->from_node_id) {
                $edge->from_node_id = LearningGraphNode::factory()
                    ->create(['learning_graph_id' => $graph->id])
                    ->id;
            }

            if (! $edge->to_node_id) {
                $edge->to_node_id = LearningGraphNode::factory()
                    ->create(['learning_graph_id' => $graph->id])
                    ->id;
            }
        });
    }
}

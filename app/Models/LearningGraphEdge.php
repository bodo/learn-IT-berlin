<?php

namespace App\Models;

use App\Enums\LearningGraphEdgeDirection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningGraphEdge extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_graph_id',
        'from_node_id',
        'to_node_id',
        'direction',
        'label',
    ];

    protected $casts = [
        'direction' => LearningGraphEdgeDirection::class,
    ];

    public function graph(): BelongsTo
    {
        return $this->belongsTo(LearningGraph::class, 'learning_graph_id');
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(LearningGraphNode::class, 'from_node_id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(LearningGraphNode::class, 'to_node_id');
    }

    public function arrows(): string
    {
        return $this->direction?->arrows() ?? '';
    }
}

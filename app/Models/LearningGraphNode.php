<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningGraphNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_graph_id',
        'title',
        'level',
        'order_column',
    ];

    protected $casts = [
        'level' => 'integer',
        'order_column' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function graph(): BelongsTo
    {
        return $this->belongsTo(LearningGraph::class, 'learning_graph_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(LearningGraphNodeBlock::class)->orderBy('order_column');
    }

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(LearningGraphEdge::class, 'from_node_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(LearningGraphEdge::class, 'to_node_id');
    }
}

<?php

namespace App\Models;

use App\Enums\LearningGraphBlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningGraphNodeBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_graph_node_id',
        'type',
        'content',
        'image_path',
        'order_column',
    ];

    protected $casts = [
        'type' => LearningGraphBlockType::class,
        'order_column' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(LearningGraphNode::class, 'learning_graph_node_id');
    }

    public function contentHtml(): string
    {
        if ($this->type !== LearningGraphBlockType::Text) {
            return '';
        }

        return Str::markdown($this->content ?? '');
    }

    public function imageUrl(): ?string
    {
        if ($this->type !== LearningGraphBlockType::Image || ! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $block) {
            if ($block->type === LearningGraphBlockType::Image && $block->image_path) {
                if (Storage::disk('public')->exists($block->image_path)) {
                    Storage::disk('public')->delete($block->image_path);
                }
            }
        });
    }
}

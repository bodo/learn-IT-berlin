<?php

namespace App\Models;

use App\Enums\LearningGraphStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningGraph extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'title',
        'status',
    ];

    protected $casts = [
        'status' => LearningGraphStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(LearningGraphNode::class)->orderBy('level')->orderBy('order_column');
    }

    public function edges(): HasMany
    {
        return $this->hasMany(LearningGraphEdge::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', LearningGraphStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === LearningGraphStatus::Published;
    }

    protected static function booted(): void
    {
        static::deleting(function (self $graph) {
            $graph->nodes()->with('blocks')->get()->each(function ($node) {
                $node->blocks->each->delete();
            });
        });
    }
}

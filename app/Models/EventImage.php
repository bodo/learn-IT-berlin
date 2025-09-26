<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EventImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'image_path',
        'alt_text',
        'order_column',
    ];

    protected $casts = [
        'order_column' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

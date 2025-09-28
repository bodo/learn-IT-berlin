<?php

namespace App\Models;

use App\Enums\RsvpStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'waitlist_position',
    ];

    protected $casts = [
        'status' => RsvpStatus::class,
        'waitlist_position' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGoing(Builder $query): Builder
    {
        return $query->where('status', RsvpStatus::Going->value);
    }

    public function scopeInterested(Builder $query): Builder
    {
        return $query->where('status', RsvpStatus::Interested->value);
    }

    public function scopeNotGoing(Builder $query): Builder
    {
        return $query->where('status', RsvpStatus::NotGoing->value);
    }

    public function scopeOnWaitlist(Builder $query): Builder
    {
        return $query->going()->whereNotNull('waitlist_position');
    }
}


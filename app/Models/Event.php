<?php

namespace App\Models;

use App\Enums\EventStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'title',
        'description',
        'place',
        'event_datetime',
        'timezone',
        'max_spots',
        'reserved_spots',
        'status',
    ];

    protected $casts = [
        'event_datetime' => 'datetime',
        'max_spots' => 'integer',
        'reserved_spots' => 'integer',
        'status' => EventStatus::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $event) {
            if ($event->reserved_spots === null) {
                $event->reserved_spots = 0;
            }

            if ($event->event_datetime && $event->timezone) {
                $event->event_datetime = Carbon::parse($event->event_datetime, $event->timezone)->setTimezone('UTC');
            }

            if ($event->max_spots !== null) {
                $event->reserved_spots = min($event->reserved_spots, $event->max_spots);
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)->orderBy('order_column');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function confirmedAttendees(): HasMany
    {
        return $this->rsvps()->where('status', \App\Enums\RsvpStatus::Going->value)->whereNull('waitlist_position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Published);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_datetime', '>=', now());
    }

    public function scopeByGroup(Builder $query, Group $group): Builder
    {
        return $query->where('group_id', $group->id);
    }

    public function getLocalEventDateAttribute(): ?Carbon
    {
        if (! $this->event_datetime || ! $this->timezone) {
            return null;
        }

        return $this->event_datetime->copy()->setTimezone($this->timezone);
    }

    public function spotsRemaining(): ?int
    {
        if ($this->max_spots === null) {
            return null;
        }

        return max(0, $this->max_spots - $this->reserved_spots);
    }

    public function isFull(): bool
    {
        if ($this->max_spots === null) {
            return false;
        }

        return $this->reserved_spots >= $this->max_spots;
    }

    public function waitlistPosition(): ?int
    {
        if (! $this->isFull()) {
            return null;
        }

        return $this->reserved_spots - $this->max_spots + 1;
    }

    /**
     * Recalculate waitlist positions and reserved spots based on current RSVPs.
     */
    public function recalcRsvps(): void
    {
        DB::transaction(function () {
            $going = $this->rsvps()
                ->where('status', \App\Enums\RsvpStatus::Going->value)
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($this->max_spots === null) {
                // Unlimited spots: everyone confirmed
                foreach ($going as $r) {
                    if ($r->waitlist_position !== null) {
                        $r->waitlist_position = null;
                        $r->save();
                    }
                }
                $this->reserved_spots = $going->count();
                $this->save();
                return;
            }

            $capacity = (int) $this->max_spots;
            $confirmed = 0;
            $waitPos = 1;
            foreach ($going as $index => $r) {
                if ($index < $capacity) {
                    if ($r->waitlist_position !== null) {
                        $r->waitlist_position = null;
                        $r->save();
                    }
                    $confirmed++;
                } else {
                    if ($r->waitlist_position !== $waitPos) {
                        $r->waitlist_position = $waitPos;
                        $r->save();
                    }
                    $waitPos++;
                }
            }

            $this->reserved_spots = $confirmed;
            $this->save();
        });
    }
}

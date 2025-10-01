<?php

namespace App\Livewire\Events;

use App\Enums\RsvpStatus;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'filter')]
    public string $timeframe = 'upcoming';

    #[Url(as: 'from')]
    public ?string $customStart = null;

    #[Url(as: 'to')]
    public ?string $customEnd = null;

    protected array $searchTerms = [];

    public array $permittedFilters = [
        'upcoming',
        'today',
        'tomorrow',
        'week',
        'range',
    ];

    public function updated($property): void
    {
        if (in_array($property, ['search', 'timeframe', 'customStart', 'customEnd'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $events = $this->queryEvents();

        return view('livewire.events.feed', [
            'events' => $events,
            'activeFilter' => $this->timeframe,
            'searchTerms' => $this->searchTerms,
        ])->layout('components.layouts.public');
    }

    protected function queryEvents(): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = Event::query()
            ->published()
            ->with(['group', 'images' => fn ($q) => $q->orderBy('order_column')->limit(1)])
            ->withCount([
                'rsvps as going_count' => fn ($q) => $q->where('status', RsvpStatus::Going->value)->whereNull('waitlist_position'),
                'rsvps as interested_count' => fn ($q) => $q->where('status', RsvpStatus::Interested->value),
            ])
            ->orderBy('event_datetime');

        if ($user) {
            $query->with(['rsvps' => fn ($relation) => $relation
                ->where('user_id', $user->id)
                ->limit(1),
            ]);
        }

        $this->applySearch($query);
        $this->applyDateFilter($query);

        return $query->paginate(9)->withQueryString();
    }

    protected function applySearch(Builder $query): void
    {
        $search = trim($this->search);
        $this->searchTerms = [];

        if ($search === '') {
            return;
        }

        $terms = collect(preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($term) => Str::of($term)->trim()->lower())
            ->filter()
            ->unique()
            ->values();

        if ($terms->isEmpty()) {
            return;
        }

        $this->searchTerms = $terms->all();

        $query->where(function (Builder $sub) use ($terms) {
            foreach ($terms as $term) {
                $like = '%'.addcslashes($term, '%_').'%';
                $sub->where(function (Builder $inner) use ($like) {
                    $inner->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
                });
            }
        });
    }

    protected function applyDateFilter(Builder $query): void
    {
        if (! in_array($this->timeframe, $this->permittedFilters, true)) {
            $this->timeframe = 'upcoming';
        }

        $tz = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);

        $range = match ($this->timeframe) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'tomorrow' => [
                $now->copy()->addDay()->startOfDay(),
                $now->copy()->addDay()->endOfDay(),
            ],
            'week' => [
                $now->copy()->startOfDay(),
                $now->copy()->addDays(7)->endOfDay(),
            ],
            'range' => $this->customRange($tz),
            default => null,
        };

        if ($range && $range[0] && $range[1]) {
            $query->whereBetween('event_datetime', [
                $range[0]->clone()->setTimezone('UTC'),
                $range[1]->clone()->setTimezone('UTC'),
            ]);
        } else {
            $query->where('event_datetime', '>=', $now->clone()->setTimezone('UTC'));
        }
    }

    protected function customRange(string $tz): array
    {
        $start = $this->parseDate($this->customStart, $tz)?->startOfDay();
        $end = $this->parseDate($this->customEnd, $tz)?->endOfDay();

        if (! $start || ! $end || $start->greaterThan($end)) {
            return [null, null];
        }

        return [$start, $end];
    }

    protected function parseDate(?string $date, string $tz): ?Carbon
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function highlight(string $text): string
    {
        if (empty($this->searchTerms)) {
            return e(Str::limit($text ?? '', 160));
        }

        $excerpt = Str::limit($text ?? '', 160);
        $highlighted = e($excerpt);

        foreach ($this->searchTerms as $term) {
            $highlighted = preg_replace_callback(
                '/('.preg_quote($term, '/').')/i',
                static fn ($matches) => '<mark>'.$matches[0].'</mark>',
                $highlighted
            );
        }

        return $highlighted;
    }

    public function userRsvpStatus(Event $event): ?RsvpStatus
    {
        $rsvp = $event->relationLoaded('rsvps') ? $event->rsvps->first() : null;

        return $rsvp?->status;
    }
}

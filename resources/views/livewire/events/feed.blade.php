<div class="space-y-8">
    <section class="card bg-base-100 shadow">
        <div class="card-body space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold">{{ __('Discover events') }}</h1>
                    <p class="text-base-content/70">{{ __('Browse upcoming study sessions, talks, and meetups across Learn IT Berlin.') }}</p>
                </div>
                <div class="form-control w-full lg:w-80">
                    <label class="input input-bordered flex items-center gap-2">
                        <x-lucide-search class="h-4 w-4" />
                        <input
                            type="search"
                            placeholder="{{ __('Search events...') }}"
                            wire:model.debounce.400ms="search"
                            class="grow"
                        />
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @php($filters = [
                    'upcoming' => __('Upcoming'),
                    'today' => __('Today'),
                    'tomorrow' => __('Tomorrow'),
                    'week' => __('This week'),
                    'range' => __('Custom range'),
                ])

                @foreach ($filters as $value => $label)
                    <button
                        type="button"
                        class="btn btn-sm {{ $activeFilter === $value ? 'btn-primary' : 'btn-outline' }}"
                        wire:click="$set('timeframe', '{{ $value }}')"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if ($activeFilter === 'range')
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="form-control">
                        <label class="label" for="from-date">
                            <span class="label-text">{{ __('Start date') }}</span>
                        </label>
                        <input id="from-date" type="date" class="input input-bordered" wire:model.lazy="customStart" max="{{ now()->addYear()->format('Y-m-d') }}" />
                    </div>
                    <div class="form-control">
                        <label class="label" for="to-date">
                            <span class="label-text">{{ __('End date') }}</span>
                        </label>
                        <input id="to-date" type="date" class="input input-bordered" wire:model.lazy="customEnd" max="{{ now()->addYear()->format('Y-m-d') }}" />
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="space-y-6">
        @if ($events->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body items-center text-center">
                    <x-lucide-calendar-x class="h-10 w-10 text-base-content/40" />
                    <h2 class="card-title mt-2">{{ __('No events found') }}</h2>
                    <p class="text-base-content/70">{{ __('Try adjusting your search or filters to discover more events.') }}</p>
                </div>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($events as $event)
                    @php($userStatus = auth()->check() ? $this->userRsvpStatus($event) : null)
                    <article class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                        <a href="{{ route('events.show', $event) }}" class="block">
                            <figure class="h-40 w-full overflow-hidden bg-base-200">
                                @if ($event->images->first())
                                    <img src="{{ Storage::disk('public')->url($event->images->first()->image_path) }}" alt="{{ $event->images->first()->alt_text }}" class="h-full w-full object-cover" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-base-content/40">
                                        <x-lucide-image-off class="h-8 w-8" />
                                    </div>
                                @endif
                            </figure>
                        </a>
                        <div class="card-body space-y-4">
                            <div class="space-y-1">
                                <a href="{{ route('events.show', $event) }}" class="card-title hover:underline">{!! $this->highlight($event->title) !!}</a>
                                <a href="{{ route('groups.show', $event->group) }}" class="text-sm text-primary hover:underline flex items-center gap-1">
                                    <x-lucide-users class="h-4 w-4" />
                                    <span>{{ $event->group->title }}</span>
                                </a>
                            </div>
                            <p class="text-sm text-base-content/70 leading-relaxed line-clamp-3">
                                {!! $this->highlight($event->description ?? '') !!}
                            </p>

                            <dl class="grid gap-2 text-sm text-base-content/70">
                                <div class="flex items-center gap-2">
                                    <x-lucide-calendar class="h-4 w-4" />
                                    <span>{{ $event->local_event_date?->format('M j, Y g:i A') }} @if($event->timezone) ({{ $event->timezone }}) @endif</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-map-pin class="h-4 w-4" />
                                    <span>{{ $event->place }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-users-round class="h-4 w-4" />
                                    <span>
                                        {{ trans_choice('feed.going', $event->going_count, ['count' => $event->going_count]) }} ·
                                        {{ trans_choice('feed.interested', $event->interested_count, ['count' => $event->interested_count]) }}
                                    </span>
                                </div>
                                @if (! is_null($event->max_spots))
                                    <div class="flex items-center gap-2">
                                        <x-lucide-ticket class="h-4 w-4" />
                                        <span>{{ __(':reserved of :max spots taken', ['reserved' => $event->reserved_spots, 'max' => $event->max_spots]) }}</span>
                                    </div>
                                @endif
                            </dl>

                            @auth
                                <div>
                                    @if (! $userStatus)
                                        <span class="badge badge-outline">{{ __('No RSVP yet') }}</span>
                                    @elseif($userStatus === \App\Enums\RsvpStatus::Going)
                                        <span class="badge badge-success badge-outline">{{ __('You are going') }}</span>
                                    @elseif($userStatus === \App\Enums\RsvpStatus::Interested)
                                        <span class="badge badge-info badge-outline">{{ __('Interested') }}</span>
                                    @else
                                        <span class="badge badge-neutral badge-outline">{{ __('Not going') }}</span>
                                    @endif
                                </div>
                            @endauth

                            <div class="card-actions justify-end">
                                <a href="{{ route('events.show', $event) }}" class="btn btn-outline btn-sm">
                                    {{ __('View details') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $events->links() }}
            </div>
        @endif
    </section>
</div>

<div class="space-y-8">
    <x-ui.breadcrumbs :items="[
        ['label' => __('Events'), 'url' => route('events.index')],
        ['label' => $event->group->title, 'url' => route('groups.show', $event->group)],
        ['label' => $event->title],
    ]" />

    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <h1 class="text-3xl font-bold">{{ $event->title }}</h1>
                    <div class="text-base-content/70 whitespace-pre-line">{{ $event->description }}</div>
                </div>
                <div class="card bg-base-200 shadow-inner w-full max-w-sm">
                    <div class="card-body space-y-2">
                        <h2 class="card-title">{{ __('Event details') }}</h2>
                        <div class="flex items-center gap-2 text-sm">
                            <x-lucide-clock class="w-4 h-4" />
                            <span>{{ $event->local_event_date?->format('F j, Y g:i A') }} ({{ $event->timezone }})</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <x-lucide-map-pin class="w-4 h-4" />
                            <span>{{ $event->place }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <x-lucide-users class="w-4 h-4" />
                            @if ($event->max_spots)
                                <span>{{ __(':reserved of :max spots taken', ['reserved' => $event->reserved_spots, 'max' => $event->max_spots]) }}</span>
                            @else
                                <span>{{ __('Unlimited spots') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <x-lucide-flag class="w-4 h-4" />
                            <a href="{{ route('groups.show', $event->group) }}" class="link link-primary">{{ $event->group->title }}</a>
                        </div>
                        @if ($canManage)
                            <div class="pt-2 space-y-2">
                                <a href="{{ route('admin.events.edit', [$event->group, $event]) }}" class="btn btn-sm btn-outline w-full">{{ __('Edit event') }}</a>
                                <a href="{{ route('admin.events.attendees.export', [$event->group, $event]) }}" class="btn btn-sm btn-outline w-full">{{ __('Export attendees (CSV)') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="carousel w-full space-x-4">
                @forelse ($event->images as $image)
                    <div class="carousel-item w-full">
                        <img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text }}" class="rounded-xl w-full object-cover" />
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-base-300 p-10 text-center text-base-content/60">
                        {{ __('No event images yet.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <livewire:events.rsvp-panel :event="$event" />

    <livewire:events.comments :event="$event" :key="'comments-'.$event->id" />

    @if ($canManage)
        <livewire:events.waitlist-admin :event="$event" :key="'waitlist-admin-'.$event->id" />
    @endif
</div>

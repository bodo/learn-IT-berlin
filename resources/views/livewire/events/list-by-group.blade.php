<div class="space-y-6">
    <div class="space-y-2 sm:space-y-0 sm:flex sm:items-center sm:justify-between">
        <div class="text-center sm:text-left">
            <h2 class="text-2xl font-bold">{{ __('Upcoming events') }}</h2>
            <p class="text-base-content/70">{{ __('All published events from this group.') }}</p>
        </div>

        @if ($canManage ?? false)
            <a href="{{ route('admin.events.index', $group) }}" class="btn btn-primary">
                {{ __('Manage events') }}
            </a>
        @endif
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($events as $event)
            <a href="{{ route('events.show', $event) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <figure class="h-36 w-full overflow-hidden bg-base-200">
                    @if ($event->images->first())
                        <img src="{{ Storage::disk('public')->url($event->images->first()->image_path) }}" alt="{{ $event->images->first()->alt_text }}" class="h-full w-full object-cover" />
                    @else
                        <div class="flex h-full w-full items-center justify-center text-base-content/40">
                            <x-lucide-calendar class="w-10 h-10" />
                        </div>
                    @endif
                </figure>
                <div class="card-body">
                    <h3 class="card-title">{{ $event->title }}</h3>
                    <p class="text-sm text-base-content/70 line-clamp-2">{{ $event->description }}</p>
                    <div class="card-actions flex-col items-start gap-1 text-sm text-base-content/60">
                        <span>{{ $event->local_event_date?->format('M j, Y g:i A') }} ({{ $event->timezone }})</span>
                        <span>{{ $event->place }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center text-base-content/70 py-12">
                {{ __('No upcoming events yet.') }}
            </div>
        @endforelse
    </div>

    <div>
        {{ $events->links() }}
    </div>
</div>

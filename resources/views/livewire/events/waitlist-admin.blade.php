<div class="card bg-base-100 shadow" wire:poll.10s>
    <div class="card-body space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="card-title">{{ __('Attendee Management') }}</h2>
                <p class="text-sm text-base-content/70">{{ __('Monitor confirmed spots, waitlist, and interested members.') }}</p>
            </div>
            <div class="text-sm text-base-content/60">
                {{ __('Confirmed: :confirmed | Waitlist: :waitlist | Interested: :interested', [
                    'confirmed' => $confirmed->count(),
                    'waitlist' => $waitlist->count(),
                    'interested' => $interested->count(),
                ]) }}
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-3">
                <h3 class="font-semibold text-base-content">{{ __('Confirmed Attendees') }}</h3>
                @if ($confirmed->isEmpty())
                    <div class="rounded-lg border border-dashed border-base-300 p-4 text-sm text-base-content/70">
                        {{ __('No confirmed attendees yet.') }}
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($confirmed as $rsvp)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-base-200 bg-base-200/40 p-3">
                                <div>
                                    <div class="font-medium">{{ $rsvp->user->display_name ?? $rsvp->user->name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $rsvp->user->email }}</div>
                                </div>
                                <div class="text-xs text-base-content/60">
                                    {{ __('RSVP\'d :timeAgo', ['timeAgo' => optional($rsvp->created_at)->diffForHumans()]) }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="space-y-3">
                <h3 class="font-semibold text-base-content">{{ __('Waitlist') }}</h3>
                @if ($waitlist->isEmpty())
                    <div class="rounded-lg border border-dashed border-base-300 p-4 text-sm text-base-content/70">
                        {{ __('No one on the waitlist.') }}
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($waitlist as $rsvp)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-warning/40 bg-warning/10 p-3">
                                <div>
                                    <div class="font-medium">{{ $rsvp->user->display_name ?? $rsvp->user->name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $rsvp->user->email }}</div>
                                </div>
                                <div class="flex flex-col items-end text-xs text-base-content/70">
                                    <span class="badge badge-sm badge-warning badge-outline">{{ __('Position :pos', ['pos' => $rsvp->waitlist_position]) }}</span>
                                    <span>{{ __('RSVP\'d :timeAgo', ['timeAgo' => optional($rsvp->created_at)->diffForHumans()]) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="font-semibold text-base-content">{{ __('Interested Users') }}</h3>
            @if ($interested->isEmpty())
                <div class="rounded-lg border border-dashed border-base-300 p-4 text-sm text-base-content/70">
                    {{ __('No interested users yet.') }}
                </div>
            @else
                <ul class="space-y-3">
                    @foreach ($interested as $rsvp)
                        <li class="flex items-center justify-between gap-3 rounded-lg border border-info/40 bg-info/10 p-3">
                            <div>
                                <div class="font-medium">{{ $rsvp->user->display_name ?? $rsvp->user->name }}</div>
                                <div class="text-xs text-base-content/60">{{ $rsvp->user->email }}</div>
                            </div>
                            <div class="text-xs text-base-content/70">
                                {{ __('Marked interested :timeAgo', ['timeAgo' => optional($rsvp->created_at)->diffForHumans()]) }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

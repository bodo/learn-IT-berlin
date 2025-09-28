<div class="card bg-base-100 shadow" wire:poll.visible.10s="loadMyRsvp">
    <div class="card-body space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="card-title">{{ __('RSVP') }}</h2>
            <div class="text-sm text-base-content/70">
                @if ($event->max_spots)
                    {{ __(':taken of :max spots', ['taken' => $goingConfirmed, 'max' => $event->max_spots]) }}
                @else
                    {{ __('Unlimited spots') }}
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @auth
                <button class="btn btn-primary btn-sm" wire:click="setStatus('going')" @disabled($myStatus === \App\Enums\RsvpStatus::Going)>
                    {{ __('I\'m going') }}
                </button>
                <button class="btn btn-outline btn-sm" wire:click="setStatus('interested')" @disabled($myStatus === \App\Enums\RsvpStatus::Interested)>
                    {{ __('Interested') }}
                </button>
                <button class="btn btn-ghost btn-sm" wire:click="setStatus('not_going')" @disabled($myStatus === \App\Enums\RsvpStatus::NotGoing)>
                    {{ __('Not going') }}
                </button>
            @else
                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">{{ __('Sign in to RSVP') }}</a>
            @endauth
        </div>

        @auth
            <div class="text-sm">
                @if ($myStatus === \App\Enums\RsvpStatus::Going)
                    @if ($myWaitlistPos)
                        <div class="badge badge-warning badge-outline">
                            {{ __('On waitlist: position :pos', ['pos' => $myWaitlistPos]) }}
                        </div>
                    @else
                        <div class="badge badge-success badge-outline">
                            {{ __('You are confirmed') }}
                        </div>
                    @endif
                @elseif ($myStatus === \App\Enums\RsvpStatus::Interested)
                    <div class="badge badge-info badge-outline">{{ __('Marked interested') }}</div>
                @elseif ($myStatus === \App\Enums\RsvpStatus::NotGoing)
                    <div class="badge badge-neutral badge-outline">{{ __('Not going') }}</div>
                @else
                    <div class="text-base-content/70">{{ __('Choose an RSVP option.') }}</div>
                @endif
            </div>
        @endauth

        <div class="divider my-2"></div>

        <div class="flex items-center gap-4 text-sm">
            <div>{{ __('Going') }}: <span class="font-medium">{{ $goingConfirmed }}</span></div>
            <div>{{ __('Interested') }}: <span class="font-medium">{{ $interested }}</span></div>
            @if ($event->max_spots)
                <div>{{ __('Waitlist') }}: <span class="font-medium">{{ $waitlistCount }}</span></div>
            @endif
        </div>

        <div class="mt-2">
            <div class="text-sm font-medium mb-2">{{ __('Attendees') }}</div>
            <div class="flex -space-x-3">
                @forelse ($attendees as $user)
                    <div class="avatar tooltip" data-tip="{{ $user->display_name ?? $user->name }}">
                        <div class="w-9 rounded-full ring ring-base-200 ring-offset-base-100 ring-offset-2">
                            @if ($user->avatarUrl())
                                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->display_name ?? $user->name }}">
                            @else
                                <div class="w-9 h-9 bg-base-300 flex items-center justify-center rounded-full text-xs">{{ $user->initials() }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-base-content/70 text-sm">{{ __('Be the first to RSVP!') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

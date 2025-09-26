<div class="space-y-6">
    {{-- Welcome Section --}}
    @auth
        <div class="mb-8">
            <h1 class="text-3xl font-bold">{{ __('Welcome back, :name!', ['name' => $user->name]) }}</h1>
            <p class="text-base-content/70 mt-2">{{ __('Here are your upcoming events and important information.') }}</p>
            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-base-200 px-4 py-1 text-sm text-base-content/80">
                <x-lucide-shield class="h-4 w-4" />
                <span>{{ __('Role: :role', ['role' => $user->roleLabel()]) }}</span>
            </div>
        </div>
    @else
        <div class="mb-8">
            <h1 class="text-3xl font-bold">{{ __('Welcome to Learn-it Berlin') }}</h1>
            <p class="text-base-content/70 mt-2">{{ __('Discover upcoming computer science learning events in Berlin.') }}</p>
        </div>
    @endauth

    {{-- Quick Actions for Authenticated Users --}}
    @auth
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @if($user->isAdmin())
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">{{ __('Manage Groups') }}</h2>
                        <p class="text-base-content/70">{{ __('Create and manage learning groups') }}</p>
                        <div class="card-actions justify-end">
                            <a href="/admin/groups" class="btn btn-primary btn-sm">{{ __('Manage Groups') }}</a>
                        </div>
                    </div>
                </div>
            @endif

            @if($user->isTrustedUser())
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">{{ __('Moderate Comments') }}</h2>
                        <p class="text-base-content/70">{{ __('Review and approve user comments') }}</p>
                        <div class="card-actions justify-end">
                            <a href="/moderate/comments" class="btn btn-outline btn-sm">{{ __('Moderate') }}</a>
                        </div>
                    </div>
                </div>
            @endif

            @if($user->isSuperuser())
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">{{ __('Manage Users') }}</h2>
                        <p class="text-base-content/70">{{ __('Manage user roles and permissions') }}</p>
                        <div class="card-actions justify-end">
                            <a href="/admin/users" class="btn btn-outline btn-sm">{{ __('Manage Users') }}</a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">{{ __('Browse Events') }}</h2>
                    <p class="text-base-content/70">{{ __('Discover all upcoming events') }}</p>
                    <div class="card-actions justify-end">
                        <a href="/events" class="btn btn-outline btn-sm">{{ __('Browse Events') }}</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">{{ __('Join Groups') }}</h2>
                    <p class="text-base-content/70">{{ __('Find and join learning groups') }}</p>
                    <div class="card-actions justify-end">
                        <a href="/groups" class="btn btn-outline btn-sm">{{ __('Browse Groups') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    {{-- Upcoming Events Section --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">{{ __('Upcoming Events') }}</h2>
            <p class="text-base-content/70">{{ __('Don\'t miss these exciting learning opportunities') }}</p>

        @if($upcomingEvents->count() > 0)
            <div class="space-y-4">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="block rounded-lg border border-base-200 bg-base-100 p-4 transition-colors hover:border-primary/80">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-base-content">{{ $event->title }}</h3>
                                    <p class="text-sm text-base-content/70">{{ $event->group->title }}</p>
                                </div>
                                <span class="badge badge-outline">{{ $event->status->label() }}</span>
                            </div>
                            <div class="text-sm text-base-content/70 flex flex-wrap gap-4">
                                <span>
                                    {{ optional($event->local_event_date)->format('M j, Y g:i A') ?? $event->event_datetime?->format('M j, Y g:i A') }}
                                    @if ($event->timezone)
                                        ({{ $event->timezone }})
                                    @endif
                                </span>
                                <span class="flex items-center gap-2">
                                    <x-lucide-map-pin class="h-4 w-4" />
                                    <span>{{ $event->place }}</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 mb-4">
                    <x-lucide-calendar class="h-6 w-6 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('No upcoming events') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 max-w-md mx-auto">
                        {{ __('Events will appear here once they are created.') }}
                        @auth
                            @if($user->isAdmin())
                                {{ __('As an admin, you can create groups and events.') }}
                            @endif
                        @else
                            <a href="/register" class="text-blue-600 hover:text-blue-500 underline">{{ __('Sign up') }}</a> {{ __('to get notified about new events.') }}
                        @endauth
                    </p>
                </div>
            </div>
        @endif

        @if($upcomingEvents->count() > 0)
            <div class="card-actions justify-center mt-6">
                <a href="/events" class="btn btn-outline">{{ __('View All Events') }}</a>
            </div>
        @endif
        </div>
    </div>

    @auth
        {{-- RSVPs Section --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">{{ __('Your RSVPs') }}</h2>
                <p class="text-base-content/70">{{ __('Keep track of the events you plan to attend.') }}</p>

                @if ($rsvpEvents->count() > 0)
                    <div class="space-y-4">
                        @foreach ($rsvpEvents as $event)
                            <div class="p-4 border border-base-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold">{{ $event->title }}</h3>
                                        <p class="text-sm text-base-content/70">{{ $event->date }}</p>
                                    </div>
                                    <span class="badge badge-outline">{{ $event->status ?? __('Going') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-sm text-base-content/70">
                        {{ __('RSVP to an event and it will appear here.') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">{{ __('Recent Activity') }}</h2>
                <p class="text-base-content/70">{{ __('Comments, RSVPs, and other actions appear here.') }}</p>

                @if ($recentActivity->count() > 0)
                    <ul class="timeline timeline-vertical timeline-compact">
                        @foreach ($recentActivity as $activity)
                            <li>
                                <div class="timeline-middle">
                                    <span class="badge badge-primary"></span>
                                </div>
                                <div class="timeline-end timeline-box">
                                    <p class="font-semibold">{{ data_get($activity, 'title', __('Activity')) }}</p>
                                    <p class="text-sm text-base-content/70">{{ data_get($activity, 'description', __('Details coming soon.')) }}</p>
                                    @php($timestamp = data_get($activity, 'timestamp'))
                                    <span class="text-xs text-base-content/60">
                                        {{ $timestamp ? $timestamp->diffForHumans() : __('Just now') }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-10 text-sm text-base-content/70">
                        {{ __('Once you start participating, your activity log will show up here.') }}
                    </div>
                @endif
            </div>
        </div>
    @endauth

    {{-- Call to Action for Non-Authenticated Users --}}
    @guest
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body text-center">
                <h2 class="card-title justify-center">{{ __('Join Learn-it Berlin') }}</h2>
                <p class="text-base-content/70">
                    {{ __('Sign up to RSVP for events, join groups, and connect with the Berlin tech community.') }}
                </p>
                <div class="card-actions justify-center gap-4 mt-6">
                    <a href="/register" class="btn btn-primary">{{ __('Sign Up') }}</a>
                    <a href="/login" class="btn btn-outline">{{ __('Sign In') }}</a>
                </div>
            </div>
        </div>
    @endguest
</div>

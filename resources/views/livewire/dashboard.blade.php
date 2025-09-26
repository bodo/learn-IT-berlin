<div class="space-y-6">
    {{-- Welcome Section --}}
    @auth
        <div class="mb-8">
            <h1 class="text-3xl font-bold">{{ __('Welcome back, :name!', ['name' => $user->name]) }}</h1>
            <p class="text-base-content/70 mt-2">{{ __('Here are your upcoming events and important information.') }}</p>
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
                    {{-- Event cards will go here when events system is implemented --}}
                    <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <h3 class="font-semibold">{{ $event->title }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $event->date }}</p>
                    </div>
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

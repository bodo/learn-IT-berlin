<div class="space-y-8">
    <div class="card bg-base-100 shadow">
        <figure class="h-48 w-full overflow-hidden bg-base-200">
            @if ($group->bannerUrl())
                <img src="{{ $group->bannerUrl() }}" alt="{{ $group->title }}" class="h-full w-full object-cover" />
            @else
                <div class="flex h-full w-full items-center justify-center text-base-content/40">
                    <x-lucide-users class="w-12 h-12" />
                </div>
            @endif
        </figure>
        <div class="card-body">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold">{{ $group->title }}</h1>
                    <p class="text-base-content/70">{{ $group->description ?? __('No description provided.') }}</p>
                    <div class="flex gap-4 text-sm text-base-content/60">
                        <span>{{ trans_choice('{0}No members yet|{1}:count member|[2,*]:count members', $group->members_count, ['count' => $group->members_count]) }}</span>
                        <span>{{ __('Created :time', ['time' => $group->created_at?->diffForHumans() ?? __('n/a')]) }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 w-full max-w-xs">
                    @if (session()->has('error'))
                        <div class="alert alert-error">
                            <x-lucide-alert-triangle class="w-4 h-4" />
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @auth
                        <button class="btn btn-primary" wire:click="toggleMembership">
                            {{ $isMember ? __('Leave group') : __('Join group') }}
                        </button>

                        @if ($group->canManage(auth()->user()))
                            <a href="{{ route('admin.events.index', $group) }}" class="btn btn-outline">
                                {{ __('Manage events') }}
                            </a>
                            <a href="{{ route('admin.groups.manage', $group) }}" class="btn btn-outline">
                                {{ __('Manage group') }}
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            {{ __('Sign in to join') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">{{ __('Owners & moderators') }}</h2>
                <p class="text-sm text-base-content/70">{{ __('Reach out to group admins if you have questions.') }}</p>
                <div class="space-y-3">
                    @foreach ($owners as $owner)
                        <div class="rounded-lg border border-base-200 p-3">
                            <p class="font-semibold">{{ $owner->name }}</p>
                            <p class="text-sm text-base-content/70">{{ __('Owner') }}</p>
                        </div>
                    @endforeach
                    @foreach ($moderators as $moderator)
                        <div class="rounded-lg border border-base-200 p-3">
                            <p class="font-semibold">{{ $moderator->name }}</p>
                            <p class="text-sm text-base-content/70">{{ __('Moderator') }}</p>
                        </div>
                    @endforeach
                    @if ($owners->isEmpty() && $moderators->isEmpty())
                        <p class="text-sm text-base-content/60">{{ __('No admins listed yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-4">
                <h2 class="card-title">{{ __('Upcoming events') }}</h2>
                <p class="text-sm text-base-content/70">{{ __('Events will appear here once the schedule is published.') }}</p>
                <div class="rounded-lg border border-dashed border-base-300 p-6 text-center text-base-content/60">
                    {{ __('No events yet. Check back soon!') }}
                </div>
            </div>
        </div>
    </div>

    <livewire:events.list-by-group :group="$group" />
</div>

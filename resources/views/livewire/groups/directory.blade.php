<div class="space-y-6">
    <div class="text-center space-y-2">
        <h1 class="text-3xl font-bold">{{ __('Groups') }}</h1>
        <p class="text-base-content/70">{{ __('Explore public study groups and join the ones you like.') }}</p>
    </div>

    <div class="flex justify-center">
        <input
            type="search"
            wire:model.debounce.300ms="search"
            placeholder="{{ __('Search groups...') }}"
            class="input input-bordered w-full max-w-xl"
        />
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($groups as $group)
            <a href="{{ route('groups.show', $group) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <figure class="h-36 w-full overflow-hidden bg-base-200">
                    @if ($group->bannerUrl())
                        <img src="{{ $group->bannerUrl() }}" alt="{{ $group->title }}" class="h-full w-full object-cover" />
                    @else
                        <div class="flex h-full w-full items-center justify-center text-base-content/40">
                            <x-lucide-users class="w-10 h-10" />
                        </div>
                    @endif
                </figure>
                <div class="card-body">
                    <h2 class="card-title">{{ $group->title }}</h2>
                    <p class="text-sm text-base-content/70 line-clamp-3">{{ $group->description ?? __('No description provided.') }}</p>
                    <div class="card-actions justify-between items-center text-sm text-base-content/60">
                        <span>{{ trans_choice('{0}No members|{1}:count member|[2,*]:count members', $group->members_count, ['count' => $group->members_count]) }}</span>
                        <span>{{ __('View details') }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center text-base-content/70 py-12">
                {{ __('No groups found.') }}
            </div>
        @endforelse
    </div>

    <div>
        {{ $groups->links() }}
    </div>
</div>

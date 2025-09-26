<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold">{{ __('Groups') }}</h1>
            <p class="text-base-content/70">{{ __('Create and manage learning groups.') }}</p>
        </div>
        <div class="flex gap-2">
            <input
                type="search"
                wire:model.debounce.300ms="search"
                placeholder="{{ __('Search groups...') }}"
                class="input input-bordered w-full sm:w-72"
            />
            <button class="btn btn-primary" wire:click="openCreateModal">{{ __('Create group') }}</button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success">
            <x-lucide-check class="w-4 h-4" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Group') }}</th>
                            <th>{{ __('Created') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle h-12 w-12 bg-base-200">
                                                @if ($group->bannerUrl())
                                                    <img src="{{ $group->bannerUrl() }}" alt="{{ $group->title }}" />
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-base-content/60">
                                                        <x-lucide-users class="w-6 h-6" />
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-semibold">{{ $group->title }}</div>
                                            <div class="text-sm text-base-content/70 line-clamp-1">
                                                {{ $group->description ?? __('No description provided.') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $group->created_at?->diffForHumans() }}</td>
                                <td class="text-right">
                                    <div class="join join-horizontal justify-end">
                                        <a
                                            href="{{ route('admin.events.index', $group) }}"
                                            class="btn btn-sm btn-outline join-item"
                                        >
                                            {{ __('Events') }}
                                        </a>
                                        <a
                                            href="{{ route('admin.groups.manage', $group) }}"
                                            class="btn btn-sm btn-outline join-item"
                                        >
                                            {{ __('Manage') }}
                                        </a>
                                        <button
                                            wire:click="deleteGroup({{ $group->id }})"
                                            class="btn btn-sm btn-error join-item"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this group?') }}')"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-12 text-center text-base-content/70">
                                    {{ __('No groups found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $groups->links() }}
            </div>
        </div>
    </div>

    <dialog class="modal" @if($showCreateModal) open @endif>
        <div class="modal-box space-y-4">
            <h3 class="text-lg font-bold">{{ __('Create group') }}</h3>

            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="title">
                        <span class="label-text">{{ __('Title') }}</span>
                    </label>
                    <input id="title" type="text" wire:model="title" class="input input-bordered" required />
                    @error('title')<span class="text-error text-sm">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="description">
                        <span class="label-text">{{ __('Description') }}</span>
                    </label>
                    <textarea id="description" wire:model="description" class="textarea textarea-bordered" rows="4"></textarea>
                    @error('description')<span class="text-error text-sm">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="banner">
                        <span class="label-text">{{ __('Banner image') }}</span>
                    </label>
                    <input id="banner" type="file" wire:model="banner" class="file-input file-input-bordered" accept="image/*" />
                    @error('banner')<span class="text-error text-sm">{{ $message }}</span>@enderror
                    <div wire:loading wire:target="banner" class="text-sm text-base-content/70 mt-2">
                        {{ __('Uploading...') }}
                    </div>
                </div>
            </div>

            <div class="modal-action">
                <button class="btn" wire:click="$set('showCreateModal', false)">{{ __('Cancel') }}</button>
                <button class="btn btn-primary" wire:click="createGroup">{{ __('Create') }}</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="submit" wire:click="$set('showCreateModal', false)">close</button>
        </form>
    </dialog>
</div>

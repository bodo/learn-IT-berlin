<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.groups.index') }}" class="btn btn-ghost btn-sm">
                <x-lucide-arrow-left class="w-4 h-4" />
                {{ __('Back to groups') }}
            </a>
            <div>
                <h1 class="text-3xl font-bold">{{ $group->title }}</h1>
                <p class="text-base-content/70">{{ __('Manage group details and membership.') }}</p>
            </div>
        </div>

        <div class="flex gap-2 md:self-start">
            <a href="{{ route('admin.events.index', $group) }}" class="btn btn-primary">
                {{ __('Manage events') }}
            </a>
            <a href="{{ route('admin.events.create', $group) }}" class="btn btn-outline">
                {{ __('Create event') }}
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success">
            <x-lucide-check class="w-4 h-4" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error">
            <x-lucide-alert-triangle class="w-4 h-4" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-4">
                <h2 class="card-title">{{ __('Group details') }}</h2>
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label" for="title">
                            <span class="label-text">{{ __('Title') }}</span>
                        </label>
                        <input id="title" type="text" wire:model="title" class="input input-bordered" />
                        @error('title')<span class="text-error text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="description">
                            <span class="label-text">{{ __('Description') }}</span>
                        </label>
                        <textarea id="description" wire:model="description" class="textarea textarea-bordered" rows="4"></textarea>
                        @error('description')<span class="text-error text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="space-y-3">
                        <label class="label" for="banner">
                            <span class="label-text">{{ __('Banner image') }}</span>
                        </label>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="h-32 w-full rounded-lg bg-base-200 sm:w-48 overflow-hidden flex items-center justify-center">
                                @if ($banner)
                                    <img src="{{ $banner->temporaryUrl() }}" alt="{{ __('Banner preview') }}" class="h-full w-full object-cover" />
                                @elseif ($group->bannerUrl())
                                    <img src="{{ $group->bannerUrl() }}" alt="{{ __('Current banner') }}" class="h-full w-full object-cover" />
                                @else
                                    <x-lucide-image-off class="w-8 h-8 text-base-content/40" />
                                @endif
                            </div>
                            <div class="flex flex-col gap-2">
                                <input id="banner" type="file" wire:model="banner" class="file-input file-input-bordered" accept="image/*" />
                                <div class="flex gap-2">
                                    <button wire:click="removeBanner" class="btn btn-outline btn-sm" @disabled(! $group->banner_image_path && ! $banner)>
                                        {{ __('Remove banner') }}
                                    </button>
                                </div>
                                @error('banner')<span class="text-error text-sm">{{ $message }}</span>@enderror
                                <div wire:loading wire:target="banner" class="text-sm text-base-content/70">{{ __('Uploading...') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-actions justify-end">
                    <button class="btn btn-primary" wire:click="updateGroup">{{ __('Save changes') }}</button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <h2 class="card-title">{{ __('Owners') }}</h2>
                    <p class="text-sm text-base-content/70">{{ __('Owners can edit group details, manage membership, and delete the group.') }}</p>

                    <div class="form-control">
                        <label class="label" for="owner-email">
                            <span class="label-text">{{ __('Add owner by email') }}</span>
                        </label>
                        <div class="join">
                            <input id="owner-email" type="email" wire:model="ownerEmail" class="input input-bordered join-item" placeholder="{{ __('user@example.com') }}" />
                            <button class="btn btn-primary join-item" wire:click="addOwner">{{ __('Add') }}</button>
                        </div>
                    </div>

                    <ul class="space-y-3">
                        @foreach ($owners as $owner)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-base-200 p-3">
                                <div>
                                    <p class="font-semibold">{{ $owner->name }}</p>
                                    <p class="text-sm text-base-content/70">{{ $owner->email }}</p>
                                </div>
                                <button class="btn btn-outline btn-sm" wire:click="removeOwner({{ $owner->id }})">
                                    {{ __('Demote to member') }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <h2 class="card-title">{{ __('Moderators') }}</h2>
                    <p class="text-sm text-base-content/70">{{ __('Moderators can help with member management and comment moderation.') }}</p>

                    <div class="form-control">
                        <label class="label" for="moderator-email">
                            <span class="label-text">{{ __('Add moderator by email') }}</span>
                        </label>
                        <div class="join">
                            <input id="moderator-email" type="email" wire:model="moderatorEmail" class="input input-bordered join-item" placeholder="{{ __('user@example.com') }}" />
                            <button class="btn btn-primary join-item" wire:click="addModerator">{{ __('Add') }}</button>
                        </div>
                    </div>

                    <ul class="space-y-3">
                        @forelse ($moderators as $moderator)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-base-200 p-3">
                                <div>
                                    <p class="font-semibold">{{ $moderator->name }}</p>
                                    <p class="text-sm text-base-content/70">{{ $moderator->email }}</p>
                                </div>
                                <button class="btn btn-outline btn-sm" wire:click="removeModerator({{ $moderator->id }})">
                                    {{ __('Demote to member') }}
                                </button>
                            </li>
                        @empty
                            <li class="text-sm text-base-content/60">{{ __('No moderators yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <h2 class="card-title">{{ __('Members') }}</h2>
                    <p class="text-sm text-base-content/70">{{ __('Members can join group discussions and events.') }}</p>

                    <div class="form-control">
                        <label class="label" for="member-email">
                            <span class="label-text">{{ __('Invite member by email') }}</span>
                        </label>
                        <div class="join">
                            <input id="member-email" type="email" wire:model="memberEmail" class="input input-bordered join-item" placeholder="{{ __('user@example.com') }}" />
                            <button class="btn btn-primary join-item" wire:click="addMember">{{ __('Invite') }}</button>
                        </div>
                    </div>

                    <ul class="space-y-3 max-h-60 overflow-y-auto">
                        @forelse ($members as $member)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-base-200 p-3">
                                <div>
                                    <p class="font-semibold">{{ $member->name }}</p>
                                    <p class="text-sm text-base-content/70">{{ $member->email }}</p>
                                </div>
                                <button class="btn btn-outline btn-sm" wire:click="removeMember({{ $member->id }})">
                                    {{ __('Remove') }}
                                </button>
                            </li>
                        @empty
                            <li class="text-sm text-base-content/60">{{ __('No members yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

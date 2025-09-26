<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.events.index', $group) }}" class="btn btn-ghost btn-sm">
            <x-lucide-arrow-left class="w-4 h-4" />
            {{ __('Back to events') }}
        </a>
        <div>
            <h1 class="text-3xl font-bold">{{ $event ? __('Edit event') : __('Create event') }}</h1>
            <p class="text-base-content/70">{{ __('Set up event details. Drafts remain private until published.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
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
                        <textarea id="description" wire:model="description" rows="6" class="textarea textarea-bordered"></textarea>
                        @error('description')<span class="text-error text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="form-control">
                            <label class="label" for="place">
                                <span class="label-text">{{ __('Place') }}</span>
                            </label>
                            <input id="place" type="text" wire:model="place" class="input input-bordered" required />
                            @error('place')<span class="text-error text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label" for="timezone">
                                <span class="label-text">{{ __('Timezone') }}</span>
                            </label>
                            <select id="timezone" wire:model="timezone" class="select select-bordered">
                                @foreach ($timezones as $tz)
                                    <option value="{{ $tz }}">{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone')<span class="text-error text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="form-control">
                            <label class="label" for="event_date">
                                <span class="label-text">{{ __('Date') }}</span>
                            </label>
                            <input id="event_date" type="date" wire:model="eventDate" class="input input-bordered" />
                            @error('eventDate')<span class="text-error text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label" for="event_time">
                                <span class="label-text">{{ __('Time') }}</span>
                            </label>
                            <input id="event_time" type="time" wire:model="eventTime" class="input input-bordered" />
                            @error('eventTime')<span class="text-error text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-3">
                    <h2 class="card-title">{{ __('Event images') }}</h2>
                    <p class="text-sm text-base-content/70">{{ __('Upload photos to showcase the event. Drag to reorder after saving.') }}</p>

                    <input type="file" wire:model="newImages" class="file-input file-input-bordered w-full" multiple accept="image/*" />
                    @error('newImages.*')<span class="text-error text-sm">{{ $message }}</span>@enderror

                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach ($existingImages as $image)
                            <div class="rounded-lg border border-base-200 overflow-hidden">
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt_text'] }}" class="h-32 w-full object-cover" />
                                <div class="p-2 space-y-2">
                                    <input type="text" class="input input-bordered input-sm w-full" placeholder="{{ __('Alt text') }}" wire:model="imageAltTexts.{{ $image['id'] }}" />
                                    <button class="btn btn-error btn-sm w-full" wire:click="removeImage({{ $image['id'] }})">
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <h2 class="card-title">{{ __('Spot settings') }}</h2>
                    <div class="form-control">
                        <label class="label" for="max_spots">
                            <span class="label-text">{{ __('Maximum spots (optional)') }}</span>
                        </label>
                        <input id="max_spots" type="number" min="1" wire:model="maxSpots" class="input input-bordered" />
                        <span class="text-xs text-base-content/60">{{ __('Leave empty for unlimited spots.') }}</span>
                        @error('maxSpots')<span class="text-error text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="status">
                            <span class="label-text">{{ __('Status') }}</span>
                        </label>
                        <select id="status" wire:model="status" class="select select-bordered">
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')<span class="text-error text-sm">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="space-y-3">
                        <h2 class="card-title">{{ __('Summary') }}</h2>
                        <p class="text-sm text-base-content/70">
                            {{ __('Review your event details before saving. Published events are visible to everyone; drafts stay private to owners.') }}
                        </p>
                    </div>
                    <div class="card-actions justify-end mt-6">
                        <button class="btn btn-primary" wire:click="save">{{ __('Save event') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

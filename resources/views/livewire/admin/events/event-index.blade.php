<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold">{{ __('Events for :group', ['group' => $group->title]) }}</h1>
            <p class="text-base-content/70">{{ __('Create and manage events. Drafts are visible only to owners.') }}</p>
        </div>
        <div class="flex gap-2">
            <select wire:model="statusFilter" class="select select-bordered">
                @foreach ($statuses as $status)
                    <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.events.create', $group) }}" class="btn btn-primary">
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

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Date & time') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Spots') }}</th>
                            <th>{{ __('Images') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $event->title }}</div>
                                    <div class="text-sm text-base-content/70 line-clamp-1">{{ $event->place }}</div>
                                </td>
                                <td>
                                    <div>{{ $event->local_event_date?->format('M j, Y g:i A') }}</div>
                                    <div class="text-sm text-base-content/60">{{ $event->timezone }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $event->status === App\Enums\EventStatus::Published ? 'badge-success' : 'badge-neutral' }}">{{ $event->status->label() }}</span>
                                </td>
                                <td>
                                    @if ($event->max_spots)
                                        <span>{{ $event->reserved_spots }} / {{ $event->max_spots }}</span>
                                    @else
                                        <span>{{ __('Unlimited') }}</span>
                                    @endif
                                </td>
                                <td>{{ $event->images_count }}</td>
                                <td class="text-right">
                                    <div class="join">
                                        <a href="{{ route('admin.events.edit', [$group, $event]) }}" class="btn btn-sm btn-outline join-item">{{ __('Edit') }}</a>
                                        <button class="btn btn-sm btn-error join-item" wire:click="deleteEvent({{ $event->id }})" onclick="return confirm('{{ __('Delete this event?') }}')">{{ __('Delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-base-content/70">{{ __('No events yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</div>

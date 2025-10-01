<div class="dropdown dropdown-end" wire:poll.45s>
    <label tabindex="0" class="btn btn-ghost btn-circle">
        <div class="indicator">
            <x-lucide-bell class="w-5 h-5" />
            @if ($unreadCount > 0)
                <span class="badge badge-sm badge-error indicator-item">{{ $unreadCount }}</span>
            @endif
        </div>
    </label>
    <div tabindex="0" class="card card-compact dropdown-content w-80 bg-base-100 shadow">
        <div class="card-body space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="card-title text-base">{{ __('Notifications') }}</h3>
                <button class="btn btn-ghost btn-xs" wire:click="markAllAsRead" @disabled($unreadCount === 0)>{{ __('Mark all as read') }}</button>
            </div>

            @if ($notifications->isEmpty())
                <p class="text-sm text-base-content/60">{{ __('You are all caught up!') }}</p>
            @else
                <ul class="space-y-2">
                    @foreach ($notifications as $notification)
                        @php($data = $notification->data)
                        <li class="rounded-lg border border-base-200 bg-base-200/40 p-3 space-y-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium">{{ $data['event_title'] ?? __('Event update') }}</p>
                                    <p class="text-xs text-base-content/60">{{ $data['group_title'] ?? __('Group') }}</p>
                                </div>
                                @if (is_null($notification->read_at))
                                    <span class="badge badge-xs badge-warning"></span>
                                @endif
                            </div>
                            <p class="text-sm text-base-content/70">{{ __('Pending comments: :count', ['count' => $data['count'] ?? 1]) }}</p>
                            @if (! empty($data['latest_excerpt']))
                                <p class="text-xs text-base-content/60 italic">“{{ $data['latest_excerpt'] }}”</p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-base-content/60">
                                <span>{{ \Illuminate\Support\Carbon::parse($data['received_at'] ?? $notification->created_at)->diffForHumans() }}</span>
                                <div class="flex gap-2">
                                    <a href="{{ route('moderate.comments') }}" class="link link-primary text-xs">{{ __('Review') }}</a>
                                    @if (is_null($notification->read_at))
                                        <button class="link link-hover text-xs" wire:click="markAsRead('{{ $notification->id }}')">{{ __('Mark read') }}</button>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

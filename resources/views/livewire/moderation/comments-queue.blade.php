<div class="space-y-6" wire:init="">
    <x-auth-header
        :title="__('Comment Moderation')"
        :description="__('Review, approve, or reject comments submitted to your groups\' events.')"
    />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="btn-group">
                            <button class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-outline' }}" wire:click="$set('status','pending')">{{ __('Pending') }}</button>
                            <button class="btn btn-sm {{ $status === 'approved' ? 'btn-primary' : 'btn-outline' }}" wire:click="$set('status','approved')">{{ __('Approved') }}</button>
                            <button class="btn btn-sm {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline' }}" wire:click="$set('status','rejected')">{{ __('Rejected') }}</button>
                            <button class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline' }}" wire:click="$set('status','all')">{{ __('All') }}</button>
                        </div>
                        <div class="flex flex-wrap gap-2 md:justify-end">
                            <button class="btn btn-outline btn-xs" wire:click="toggleSelectAll">{{ __('Select all') }}</button>
                            <button class="btn btn-success btn-xs" wire:click="bulkApprove" @disabled(empty($selected))>{{ __('Approve selected') }}</button>
                            <button class="btn btn-warning btn-xs" wire:click="bulkReject" @disabled(empty($selected))>{{ __('Reject selected') }}</button>
                            <button class="btn btn-error btn-xs" wire:click="bulkDelete" @disabled(empty($selected))>{{ __('Delete selected') }}</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ __('Comment') }}</th>
                                    <th>{{ __('Event') }}</th>
                                    <th>{{ __('Submitted') }}</th>
                                    <th class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comments as $comment)
                                    <tr wire:key="comment-row-{{ $comment->id }}" class="cursor-pointer hover">
                                        <td>
                                            <input type="checkbox" class="checkbox checkbox-sm" value="{{ $comment->id }}" wire:model="selected">
                                        </td>
                                        <td wire:click="setFocus({{ $comment->id }})">
                                            <div class="space-y-1">
                                                <p class="font-medium">{{ $comment->user->display_name ?? $comment->user->name }}</p>
                                                <p class="text-xs text-base-content/60">{{ $comment->excerpt(80) }}</p>
                                                @if ($comment->reports->count() > 0)
                                                    <span class="badge badge-warning badge-outline badge-xs">{{ __('Reports') }}: {{ $comment->reports->count() }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td wire:click="setFocus({{ $comment->id }})">
                                            <div class="space-y-1">
                                                <p class="font-medium">{{ $comment->event->title }}</p>
                                                <p class="text-xs text-base-content/60">{{ $comment->event->group?->title }}</p>
                                            </div>
                                        </td>
                                        <td wire:click="setFocus({{ $comment->id }})">
                                            <span class="text-xs text-base-content/60">{{ $comment->created_at?->diffForHumans() }}</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <button class="btn btn-ghost btn-xs" wire:click.stop="approveComment({{ $comment->id }})">{{ __('Approve') }}</button>
                                                <button class="btn btn-ghost btn-xs" wire:click.stop="rejectComment({{ $comment->id }})">{{ __('Reject') }}</button>
                                                <button class="btn btn-ghost btn-xs text-error" wire:click.stop="deleteComment({{ $comment->id }})">{{ __('Delete') }}</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-base-content/60">{{ __('No comments available for this filter.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $comments->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    @if ($focusComment)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="card-title text-lg">{{ __('Comment detail') }}</h3>
                                <button class="btn btn-ghost btn-xs" wire:click="clearFocus">{{ __('Close') }}</button>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="avatar">
                                    <div class="w-10 rounded-full ring ring-base-200 ring-offset-base-100 ring-offset-2">
                                        @if ($focusComment->user->avatarUrl())
                                            <img src="{{ $focusComment->user->avatarUrl() }}" alt="{{ $focusComment->user->display_name ?? $focusComment->user->name }}">
                                        @else
                                            <div class="w-10 h-10 bg-base-300 flex items-center justify-center rounded-full text-sm font-semibold">
                                                {{ $focusComment->user->initials() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $focusComment->user->display_name ?? $focusComment->user->name }}</p>
                                    <p class="text-xs text-base-content/60">{{ $focusComment->created_at?->toDayDateTimeString() }}</p>
                                </div>
                            </div>

                            <div class="rounded-lg border border-base-200 bg-base-200/40 p-3 text-sm whitespace-pre-line">
                                {{ $focusComment->content }}
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="badge badge-outline">{{ __('Status: :status', ['status' => $focusComment->status->label()]) }}</span>
                                <a class="badge badge-outline" href="{{ route('events.show', $focusComment->event) }}" target="_blank">{{ __('View event') }}</a>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="moderation-note">{{ __('Moderator note (optional)') }}</label>
                                <textarea id="moderation-note" wire:model.defer="moderationNote" class="textarea textarea-bordered w-full" rows="2"></textarea>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button class="btn btn-success btn-sm" wire:click="approveComment({{ $focusComment->id }})">{{ __('Approve') }}</button>
                                <button class="btn btn-warning btn-sm" wire:click="rejectComment({{ $focusComment->id }})">{{ __('Reject') }}</button>
                                <button class="btn btn-error btn-sm" wire:click="deleteComment({{ $focusComment->id }})">{{ __('Delete') }}</button>
                            </div>

                            <div class="space-y-3">
                                <h4 class="font-medium">{{ __('Reports') }}</h4>
                                @if ($focusComment->reports->isEmpty())
                                    <p class="text-xs text-base-content/60">{{ __('No reports submitted for this comment.') }}</p>
                                @else
                                    <ul class="space-y-2 text-sm">
                                        @foreach ($focusComment->reports as $report)
                                            <li class="rounded-lg border border-warning/40 bg-warning/10 p-2">
                                                <div class="flex justify-between text-xs text-base-content/70">
                                                    <span>{{ $report->user->display_name ?? $report->user->name }}</span>
                                                    <span>{{ $report->created_at?->diffForHumans() }}</span>
                                                </div>
                                                @if ($report->reason)
                                                    <p class="mt-1 text-sm">{{ $report->reason }}</p>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="space-y-3">
                                <h4 class="font-medium">{{ __('History') }}</h4>
                                @if ($focusComment->moderationLogs->isEmpty())
                                    <p class="text-xs text-base-content/60">{{ __('No moderation actions recorded yet.') }}</p>
                                @else
                                    <ul class="timeline timeline-vertical timeline-compact">
                                        @foreach ($focusComment->moderationLogs->sortByDesc('created_at') as $log)
                                            <li>
                                                <div class="timeline-middle">
                                                    <span class="badge badge-outline"></span>
                                                </div>
                                                <div class="timeline-end timeline-box">
                                                    <p class="font-semibold text-sm">{{ __(ucfirst(str_replace('_', ' ', $log->action))) }}</p>
                                                    <p class="text-xs text-base-content/60">{{ $log->user?->display_name ?? $log->user?->name ?? __('System') }} · {{ $log->created_at?->diffForHumans() }}</p>
                                                    @if ($log->notes)
                                                        <p class="text-sm mt-1">{{ $log->notes }}</p>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-base-content/60 py-10">
                            {{ __('Select a comment to see details.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

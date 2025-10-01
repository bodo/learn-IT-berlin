<div class="card bg-base-100 shadow">
    <div class="card-body space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <h2 class="card-title">{{ __('Comments') }}</h2>
            <span class="badge badge-outline">
                @if ($approvedCount === 0)
                    {{ __('No comments yet') }}
                @elseif ($approvedCount === 1)
                    {{ __('1 Comment') }}
                @else
                    {{ __(':count Comments', ['count' => $approvedCount]) }}
                @endif
            </span>
        </div>

        <div class="alert alert-info">
            <div>
                <h3 class="font-semibold">{{ __('Comment guidelines') }}</h3>
                <p class="text-sm text-base-content/70">{{ __('Keep it respectful, avoid links, and stay on topic.') }}</p>
            </div>
        </div>

        @auth
            <div class="space-y-3">
                <textarea
                    wire:model.defer="newComment"
                    maxlength="{{ $maxLength }}"
                    class="textarea textarea-bordered w-full"
                    rows="3"
                    placeholder="{{ __('Share your thoughts on this event...') }}"
                ></textarea>
                @error('newComment')
                    <div class="text-error text-sm">{{ $message }}</div>
                @enderror
                <div class="flex items-center justify-between text-xs text-base-content/60">
                    <span>{{ __(':remaining characters remaining', ['remaining' => $maxLength - mb_strlen($newComment ?? '')]) }}</span>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="submit">{{ __('Post comment') }}</button>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-base-content/70">{{ __('Sign in to join the conversation.') }}</p>
                <div class="flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">{{ __('Sign In') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">{{ __('Register') }}</a>
                </div>
            </div>
        @endauth

        <div class="space-y-4">
            @php($viewerId = auth()->id())
            @forelse ($comments as $comment)
                <article class="rounded-lg border border-base-200 bg-base-200/40 p-4 space-y-3">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="avatar">
                                <div class="w-10 rounded-full ring ring-base-200 ring-offset-base-100 ring-offset-2">
                                    @if ($comment->user->avatarUrl())
                                        <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->user->display_name ?? $comment->user->name }}">
                                    @else
                                        <div class="w-10 h-10 bg-base-300 flex items-center justify-center rounded-full text-sm font-semibold">
                                            {{ $comment->user->initials() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $comment->user->display_name ?? $comment->user->name }}</span>
                                    <span class="text-xs text-base-content/60">{{ $comment->created_at?->diffForHumans() }}</span>
                                </div>
                                @if ($comment->isPending() && $viewerId === $comment->user_id)
                                    <span class="badge badge-warning badge-outline text-xs">{{ __('Pending moderation') }}</span>
                                @endif
                                @if ($comment->isRejected() && $viewerId === $comment->user_id)
                                    <span class="badge badge-error badge-outline text-xs">{{ __('Rejected by moderators') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($viewerId === $comment->user_id)
                                @if ($editingId === $comment->id)
                                    <button class="btn btn-ghost btn-xs" wire:click="cancelEditing">{{ __('Cancel') }}</button>
                                @else
                                    <button class="btn btn-ghost btn-xs" wire:click="startEditing({{ $comment->id }})">{{ __('Edit') }}</button>
                                @endif
                                <button class="btn btn-ghost btn-xs text-error" wire:click="deleteComment({{ $comment->id }})">{{ __('Delete') }}</button>
                            @elseif($viewerId)
                                @php($alreadyReported = $comment->reports->contains('user_id', $viewerId))
                                @if (! $alreadyReported)
                                    <button class="btn btn-ghost btn-xs" wire:click="startReport({{ $comment->id }})">{{ __('Report') }}</button>
                                @else
                                    <span class="badge badge-neutral badge-outline text-xs">{{ __('Reported') }}</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if ($editingId === $comment->id)
                        <div class="space-y-3">
                            <textarea
                                wire:model.defer="editComment"
                                maxlength="{{ $maxLength }}"
                                rows="3"
                                class="textarea textarea-bordered w-full"
                            ></textarea>
                            @error('editComment')
                                <div class="text-error text-sm">{{ $message }}</div>
                            @enderror
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelEditing">{{ __('Cancel') }}</button>
                                <button type="button" class="btn btn-primary btn-sm" wire:click="saveEdit">{{ __('Save changes') }}</button>
                            </div>
                        </div>
                    @else
                        <p class="text-sm leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                    @endif

                    @if ($reportingId === $comment->id)
                        <div class="rounded-lg border border-warning/40 bg-warning/10 p-3 space-y-3">
                            <p class="text-xs text-base-content/70">{{ __('Tell us what is wrong with this comment (optional).') }}</p>
                            <textarea wire:model.defer="reportReason" class="textarea textarea-bordered w-full" rows="2" maxlength="200"></textarea>
                            @error('reportReason')
                                <div class="text-error text-sm">{{ $message }}</div>
                            @enderror
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" class="btn btn-ghost btn-xs" wire:click="cancelReport">{{ __('Cancel') }}</button>
                                <button type="button" class="btn btn-warning btn-xs" wire:click="submitReport">{{ __('Submit report') }}</button>
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-base-300 p-6 text-center text-base-content/60">
                    {{ __('Be the first to comment on this event.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

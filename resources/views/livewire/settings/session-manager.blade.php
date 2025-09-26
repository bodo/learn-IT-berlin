<div class="space-y-4">
    @if (session()->has('session-update'))
        <div class="alert alert-success">
            <x-lucide-check class="w-4 h-4" />
            <span>{{ session('session-update') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <h3 class="card-title">{{ __('Active Sessions') }}</h3>

            <p class="text-sm text-base-content/70">
                {{ __('These devices are currently signed in to your account. Sign out of any sessions you do not recognize.') }}
            </p>

            <div class="space-y-3">
                @forelse ($sessions as $session)
                    <div class="border border-base-200 rounded-lg p-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="badge {{ $session['current'] ? 'badge-primary' : 'badge-outline' }}">
                                    {{ $session['current'] ? __('Current session') : __('Active session') }}
                                </span>
                                @if ($session['ip_address'])
                                    <span class="text-sm text-base-content/70">{{ $session['ip_address'] }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-base-content/70 break-all">
                                {{ $session['user_agent'] ?? __('Unknown device') }}
                            </p>
                            <p class="text-xs text-base-content/60">
                                {{ __('Last active :time', ['time' => $session['last_active_at']]) }}
                            </p>
                        </div>

                        @unless ($session['current'])
                            <button wire:click="logoutSingleSession({{ $session['id'] }})" class="btn btn-sm btn-outline">
                                {{ __('Sign out') }}
                            </button>
                        @endunless
                    </div>
                @empty
                    <div class="text-sm text-base-content/70">
                        {{ __('No additional sessions found.') }}
                    </div>
                @endforelse
            </div>

            <div class="flex justify-end">
                <button wire:click="logoutOtherSessions" class="btn btn-error btn-sm" @disabled($sessions->where('current', false)->isEmpty())>
                    {{ __('Sign out of other sessions') }}
                </button>
            </div>
        </div>
    </div>
</div>

<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div class="card bg-base-100 shadow" wire:cloak x-data="{ showRecoveryCodes: false }">
    <div class="card-body space-y-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <x-lucide-lock class="w-4 h-4" />
                <h3 class="text-lg font-semibold">{{ __('2FA Recovery Codes') }}</h3>
            </div>
            <p class="text-sm text-base-content/70">
                {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button
                type="button"
                class="btn btn-primary"
                x-show="!showRecoveryCodes"
                @click="showRecoveryCodes = true"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
            >
                <x-lucide-eye class="w-4 h-4" />
                {{ __('View Recovery Codes') }}
            </button>

            <button
                type="button"
                class="btn btn-primary"
                x-show="showRecoveryCodes"
                @click="showRecoveryCodes = false"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
            >
                <x-lucide-eye-off class="w-4 h-4" />
                {{ __('Hide Recovery Codes') }}
            </button>

            @if (filled($recoveryCodes))
                <button
                    type="button"
                    class="btn btn-ghost"
                    x-show="showRecoveryCodes"
                    wire:click="regenerateRecoveryCodes"
                >
                    <x-lucide-refresh-cw class="w-4 h-4" />
                    {{ __('Regenerate Codes') }}
                </button>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-transition
            id="recovery-codes-section"
            class="space-y-3"
            x-bind:aria-hidden="!showRecoveryCodes"
        >
            @error('recoveryCodes')
                <div class="alert alert-error">
                    <x-lucide-alert-triangle class="w-4 h-4" />
                    <span>{{ $message }}</span>
                </div>
            @enderror

            @if (filled($recoveryCodes))
                <div class="grid gap-1 rounded-lg bg-base-200 p-4 font-mono text-sm" role="list" aria-label="Recovery codes">
                    @foreach($recoveryCodes as $code)
                        <div
                            role="listitem"
                            class="select-text"
                            wire:loading.class="opacity-50 animate-pulse"
                        >
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-base-content/60">
                    {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}
                </p>
            @endif
        </div>
    </div>
</div>

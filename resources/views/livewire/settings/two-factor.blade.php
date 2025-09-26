<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Symfony\Component\HttpFoundation\Response;

new class extends Component {
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(auth()->user());

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Two-Factor Authentication Enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify Authentication Code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable Two-Factor Authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
} ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Two Factor Authentication')"
        :subheading="__('Manage your two-factor authentication settings')"
    >
        <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="badge badge-success badge-lg">{{ __('Enabled') }}</span>
                    </div>

                    <p class="text-base-content/70">
                        {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                    </p>

                    <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>

                    <div class="flex justify-start">
                        <button
                            type="button"
                            class="btn btn-error"
                            wire:click="disable"
                        >
                            <x-lucide-shield-alert class="w-4 h-4" />
                            {{ __('Disable 2FA') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="badge badge-neutral badge-lg">{{ __('Disabled') }}</span>
                    </div>

                    <p class="text-base-content/70">
                        {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                    </p>

                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="enable"
                    >
                        <x-lucide-shield-check class="w-4 h-4" />
                        {{ __('Enable 2FA') }}
                    </button>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <div x-data="{ open: @entangle('showModal').live }">
        <div class="modal" :class="{ 'modal-open': open }">
            <div class="modal-box max-w-lg space-y-6">
                <div class="space-y-4 text-center">
                    <div class="flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <x-lucide-shield-check class="w-8 h-8" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold">{{ $this->modalConfig['title'] }}</h3>
                        <p class="text-base-content/70">{{ $this->modalConfig['description'] }}</p>
                    </div>
                </div>

                @if ($showVerificationStep)
                    <div class="space-y-6">
                        <div class="flex flex-col items-center space-y-3">
                            <x-input-otp
                                :digits="6"
                                name="code"
                                wire:model="code"
                                autocomplete="one-time-code"
                            />
                            @error('code')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="button" class="btn btn-outline flex-1" wire:click="resetVerification">
                                {{ __('Back') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary flex-1"
                                wire:click="confirmTwoFactor"
                                @disabled(strlen($code) < 6)
                            >
                                {{ __('Confirm') }}
                            </button>
                        </div>
                    </div>
                @else
                    @error('setupData')
                        <div class="alert alert-error">
                            <x-lucide-alert-triangle class="w-4 h-4" />
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="flex justify-center">
                        <div class="relative w-64 aspect-square overflow-hidden rounded-xl border border-base-300 bg-base-100">
                            @empty($qrCodeSvg)
                                <div class="absolute inset-0 flex items-center justify-center animate-pulse">
                                    <span class="loading loading-spinner loading-lg"></span>
                                </div>
                            @else
                                <div class="flex h-full items-center justify-center p-4">
                                    {!! $qrCodeSvg !!}
                                </div>
                            @endempty
                        </div>
                    </div>

                    <div>
                        <button
                            type="button"
                            class="btn btn-primary w-full"
                            @disabled($errors->has('setupData'))
                            wire:click="showVerificationIfNecessary"
                        >
                            {{ $this->modalConfig['buttonText'] }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="divider text-sm">{{ __('or, enter the code manually') }}</div>

                        <div
                            class="flex items-center gap-2"
                            x-data="{
                                copied: false,
                                async copy(value) {
                                    try {
                                        await navigator.clipboard.writeText(value);
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 1500);
                                    } catch (e) {
                                        console.warn('Could not copy to clipboard');
                                    }
                                }
                            }"
                        >
                            <div class="flex-1">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $manualSetupKey }}"
                                    class="input input-bordered w-full"
                                />
                            </div>
                            <button type="button" class="btn" @click="copy('{{ $manualSetupKey }}')">
                                <x-lucide-copy class="w-4 h-4" x-show="!copied" />
                                <x-lucide-check class="w-4 h-4 text-success" x-show="copied" />
                            </button>
                        </div>
                    </div>
                @endif

                <div class="modal-action justify-between">
                    <button type="button" class="btn btn-ghost" @click="open = false; $wire.closeModal()">{{ __('Close') }}</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click.prevent="open = false; $wire.closeModal()">close</button>
            </form>
        </div>
    </div>
</section>

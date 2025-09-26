<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">{{ __('Email Address') }}</span>
            </label>
            <input
                id="email"
                wire:model="email"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
                class="input input-bordered w-full"
            />
            @error('email')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full" data-test="email-password-reset-link-button">
            {{ __('Email password reset link') }}
        </button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        <span>{{ __('Or, return to') }}</span>
        <a href="{{ route('login') }}" wire:navigate class="link link-primary">{{ __('log in') }}</a>
    </div>
</div>

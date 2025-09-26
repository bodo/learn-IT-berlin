<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <div class="form-control">
                <label class="label" for="current_password">
                    <span class="label-text">{{ __('Current password') }}</span>
                </label>
                <input
                    id="current_password"
                    wire:model="current_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="input input-bordered w-full"
                />
                @error('current_password')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label" for="new_password">
                    <span class="label-text">{{ __('New password') }}</span>
                </label>
                <input
                    id="new_password"
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="input input-bordered w-full"
                />
                @error('password')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label" for="password_confirmation">
                    <span class="label-text">{{ __('Confirm Password') }}</span>
                </label>
                <input
                    id="password_confirmation"
                    wire:model="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="input input-bordered w-full"
                />
                @error('password_confirmation')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-primary" data-test="update-password-button">
                        {{ __('Save') }}
                    </button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>

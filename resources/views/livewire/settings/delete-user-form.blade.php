<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <div class="space-y-1">
        <h3 class="text-lg font-semibold">{{ __('Delete account') }}</h3>
        <p class="text-sm text-base-content/70">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <button type="button" class="btn btn-error" data-test="delete-user-button" @click="open = true">
        {{ __('Delete account') }}
    </button>

    <div class="modal" :class="{ 'modal-open': open || @js($errors->isNotEmpty()) }">
        <div class="modal-box max-w-lg space-y-6">
            <h3 class="text-xl font-semibold">{{ __('Are you sure you want to delete your account?') }}</h3>
            <p class="text-sm text-base-content/70">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <form method="POST" wire:submit="deleteUser" class="space-y-4">
                <div class="form-control">
                    <label class="label" for="delete_password">
                        <span class="label-text">{{ __('Password') }}</span>
                    </label>
                    <input
                        id="delete_password"
                        wire:model="password"
                        type="password"
                        class="input input-bordered w-full"
                    />
                    @error('password')
                        <span class="mt-2 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-error" data-test="confirm-delete-user-button">
                        {{ __('Delete account') }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button @click.prevent="open = false">close</button>
        </form>
    </div>
</section>

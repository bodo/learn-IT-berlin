<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public ?string $display_name = null;
    public ?string $bio = null;
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['display_name'] = $validated['display_name'] ?: $validated['name'];
        $validated['role'] = UserRole::User;

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="form-control">
            <label class="label" for="name">
                <span class="label-text">{{ __('Name') }}</span>
            </label>
            <input
                id="name"
                wire:model="name"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="{{ __('Full name') }}"
                class="input input-bordered w-full"
            />
            @error('name')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Display Name -->
        <div class="form-control">
            <label class="label" for="display_name">
                <span class="label-text">{{ __('Display name (optional)') }}</span>
            </label>
            <input
                id="display_name"
                wire:model="display_name"
                type="text"
                autocomplete="nickname"
                placeholder="{{ __('How should we address you?') }}"
                class="input input-bordered w-full"
            />
            @error('display_name')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">{{ __('Email address') }}</span>
            </label>
            <input
                id="email"
                wire:model="email"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
                class="input input-bordered w-full"
            />
            @error('email')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Bio -->
        <div class="form-control">
            <label class="label" for="bio">
                <span class="label-text">{{ __('Short bio (optional)') }}</span>
            </label>
            <textarea
                id="bio"
                wire:model="bio"
                class="textarea textarea-bordered"
                rows="3"
                placeholder="{{ __('Tell others what you are interested in learning.') }}"
            ></textarea>
            @error('bio')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text">{{ __('Password') }}</span>
            </label>
            <input
                id="password"
                wire:model="password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="{{ __('Password') }}"
                class="input input-bordered w-full"
            />
            @error('password')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-control">
            <label class="label" for="password_confirmation">
                <span class="label-text">{{ __('Confirm password') }}</span>
            </label>
            <input
                id="password_confirmation"
                wire:model="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                placeholder="{{ __('Confirm password') }}"
                class="input input-bordered w-full"
            />
            @error('password_confirmation')
                <span class="mt-2 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="btn btn-primary w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <a href="{{ route('login') }}" wire:navigate class="link link-primary">{{ __('Log in') }}</a>
    </div>
</div>

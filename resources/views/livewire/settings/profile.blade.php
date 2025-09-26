<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $display_name = '';
    public ?string $bio = null;
    public string $email = '';
    public $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->display_name = Auth::user()->display_name ?? Auth::user()->name;
        $this->bio = Auth::user()->bio;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'avatar' => ['nullable', 'image', 'max:1024'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');

            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $avatarPath;
        }

        $user->save();

        Auth::setUser($user->fresh());

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" enctype="multipart/form-data">
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
                    class="input input-bordered w-full"
                />
                @error('name')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">{{ __('Email') }}</span>
                    </label>
                    <input
                        id="email"
                        wire:model="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="input input-bordered w-full"
                    />
                    @error('email')
                        <span class="mt-2 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-4 text-sm text-base-content/80">
                            {{ __('Your email address is unverified.') }}

                            <button type="button" class="link link-primary text-sm" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm text-success">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="form-control">
                <label class="label" for="display_name">
                    <span class="label-text">{{ __('Display name') }}</span>
                </label>
                <input
                    id="display_name"
                    wire:model="display_name"
                    type="text"
                    class="input input-bordered w-full"
                />
                @error('display_name')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label" for="bio">
                    <span class="label-text">{{ __('Short bio') }}</span>
                </label>
                <textarea
                    id="bio"
                    wire:model="bio"
                    class="textarea textarea-bordered"
                    rows="4"
                    placeholder="{{ __('Share a few words about yourself') }}"
                ></textarea>
                @error('bio')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label" for="role">
                    <span class="label-text">{{ __('Current role') }}</span>
                </label>
                <input
                    id="role"
                    type="text"
                    class="input input-bordered w-full"
                    value="{{ auth()->user()->roleLabel() }}"
                    disabled
                />
            </div>

            <div class="form-control">
                <label class="label" for="avatar">
                    <span class="label-text">{{ __('Avatar') }}</span>
                </label>
                <div class="flex items-center gap-4">
                    <div class="avatar">
                        <div class="w-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="{{ $avatar ? $avatar->temporaryUrl() : auth()->user()->avatarUrl() }}" alt="{{ __('Profile avatar preview') }}">
                        </div>
                    </div>
                    <input
                        id="avatar"
                        wire:model="avatar"
                        type="file"
                        class="file-input file-input-bordered w-full max-w-xs"
                        accept="image/*"
                    />
                </div>
                @error('avatar')
                    <span class="mt-2 text-sm text-error">{{ $message }}</span>
                @enderror
                <div wire:loading wire:target="avatar" class="text-sm text-base-content/70 mt-2">
                    {{ __('Uploading...') }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-primary" data-test="update-profile-button">
                        {{ __('Save') }}
                    </button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
        <livewire:settings.session-manager class="mt-10" />
    </x-settings.layout>
</section>

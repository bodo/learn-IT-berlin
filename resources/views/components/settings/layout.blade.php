<div class="flex flex-col gap-8 lg:flex-row">
    <aside class="w-full max-w-xs">
        <ul class="menu menu-vertical bg-base-100 shadow rounded-box">
            <li>
                <a href="{{ route('profile.edit') }}" wire:navigate @class(['active' => request()->routeIs('profile.edit')])>
                    {{ __('Profile') }}
                </a>
            </li>
            <li>
                <a href="{{ route('password.edit') }}" wire:navigate @class(['active' => request()->routeIs('password.edit')])>
                    {{ __('Password') }}
                </a>
            </li>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <li>
                    <a href="{{ route('two-factor.show') }}" wire:navigate @class(['active' => request()->routeIs('two-factor.show')])>
                        {{ __('Two-Factor Auth') }}
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('appearance.edit') }}" wire:navigate @class(['active' => request()->routeIs('appearance.edit')])>
                    {{ __('Appearance') }}
                </a>
            </li>
        </ul>
    </aside>

    <section class="flex-1 space-y-6">
        <div class="space-y-2">
            <h2 class="text-2xl font-semibold">{{ $heading ?? '' }}</h2>
            @if (!empty($subheading))
                <p class="text-base-content/70">{{ $subheading }}</p>
            @endif
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-6">
                {{ $slot }}
            </div>
        </div>
    </section>
</div>

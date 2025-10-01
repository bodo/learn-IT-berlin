<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div
            x-data="appearanceSettings()"
            class="space-y-4"
        >
            <div class="join">
                <button type="button" class="join-item btn" :class="buttonClasses('light')" @click="setTheme('light')">
                    <x-lucide-sun class="w-4 h-4" />
                    <span>{{ __('Light') }}</span>
                </button>
                <button type="button" class="join-item btn" :class="buttonClasses('dark')" @click="setTheme('dark')">
                    <x-lucide-moon class="w-4 h-4" />
                    <span>{{ __('Dark') }}</span>
                </button>
                <button type="button" class="join-item btn" :class="buttonClasses('system')" @click="setTheme('system')">
                    <x-lucide-monitor class="w-4 h-4" />
                    <span>{{ __('System') }}</span>
                </button>
            </div>

            <p class="text-sm text-base-content/70">
                {{ __('Your preference is saved on this device and applied automatically on future visits.') }}
            </p>
        </div>
    </x-settings.layout>
</section>

@push('scripts')
    <script>
        function appearanceSettings() {
            const KEY = 'learnit-theme';
            const manager = window.learnItTheme;
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

            const currentTheme = () => {
                if (manager) {
                    return manager.current();
                }

                return localStorage.getItem(KEY) ?? 'system';
            };

            const applyTheme = (value) => {
                if (manager) {
                    manager.set(value);
                    return;
                }

                if (value === 'system') {
                    localStorage.removeItem(KEY);
                    const prefersDark = mediaQuery.matches;
                    document.documentElement.dataset.theme = prefersDark ? 'dark' : 'light';
                    document.documentElement.classList.toggle('dark', prefersDark);
                    return;
                }

                localStorage.setItem(KEY, value);
                document.documentElement.dataset.theme = value;
                document.documentElement.classList.toggle('dark', value === 'dark');
            };

            return {
                theme: currentTheme(),
                buttonClasses(value) {
                    return this.theme === value ? 'btn-primary text-primary-content' : 'btn-ghost';
                },
                setTheme(value) {
                    this.theme = value;
                    applyTheme(value);
                },
            };
        }
    </script>
@endpush

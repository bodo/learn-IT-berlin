<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200 antialiased">
        @if (session()->has('success'))
            <x-ui.toast type="success" :message="session('success')" />
        @endif
        <div class="min-h-screen flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-md space-y-8">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-content">
                        <x-app-logo-icon class="w-8 h-8" />
                    </span>
                    <span class="text-sm text-base-content/70">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body space-y-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @stack('scripts')
        @livewireScripts
    </body>
</html>

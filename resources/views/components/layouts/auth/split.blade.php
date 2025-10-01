<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200 antialiased">
        @if (session()->has('success'))
            <x-ui.toast type="success" :message="session('success')" />
        @endif
        <div class="grid min-h-screen lg:grid-cols-2">
            <div class="relative hidden bg-gradient-to-br from-primary to-secondary text-primary-content lg:flex">
                <div class="absolute inset-0 opacity-80"></div>
                <div class="relative z-10 flex flex-1 flex-col p-12">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 text-xl font-semibold" wire:navigate>
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-content text-primary">
                            <x-app-logo-icon class="w-8 h-8" />
                        </span>
                        {{ config('app.name', 'Laravel') }}
                    </a>

                    @php
                        [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                    @endphp

                    <div class="mt-auto space-y-4">
                        <h2 class="text-3xl font-bold leading-tight">&ldquo;{{ trim($message) }}&rdquo;</h2>
                        <p class="text-lg font-medium">{{ trim($author) }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md space-y-8">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 lg:hidden" wire:navigate>
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
        </div>

        @stack('scripts')
        @livewireScripts
    </body>
</html>

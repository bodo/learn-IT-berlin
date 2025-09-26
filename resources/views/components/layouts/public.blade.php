<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-base-200">
        <!-- Skip link for accessibility -->
        <a href="#main-content" class="skip-link">{{ __('Skip to main content') }}</a>

        <!-- Public Navigation -->
        <div class="navbar bg-base-100 border-b border-base-200">
            <div class="navbar-start">
                <a href="{{ route('home') }}" class="btn btn-ghost normal-case text-xl" wire:navigate>
                    <x-app-logo />
                </a>
            </div>

            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1">
                    @auth
                        <li><a href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</a></li>
                    @endauth
                    <li><a href="/events" wire:navigate>{{ __('Events') }}</a></li>
                    <li><a href="{{ route('groups.index') }}" wire:navigate>{{ __('Groups') }}</a></li>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li>
                                <details>
                                    <summary>{{ __('Admin') }}</summary>
                                    <ul class="p-2 bg-base-100 w-52">
                                        <li><a href="{{ route('admin.groups.index') }}" wire:navigate>
                                            <x-lucide-building class="w-4 h-4" />
                                            {{ __('Manage Groups') }}
                                        </a></li>
                                        @if(auth()->user()->isSuperuser())
                                            <li><a href="/admin/users" wire:navigate>
                                                <x-lucide-user-cog class="w-4 h-4" />
                                                {{ __('Manage Users') }}
                                            </a></li>
                                        @endif
                                    </ul>
                                </details>
                            </li>
                        @endif
                        @if(auth()->user()->isTrustedUser())
                            <li><a href="/moderate/comments" wire:navigate>{{ __('Moderate') }}</a></li>
                        @endif
                    @endauth
                </ul>
            </div>

            <div class="navbar-end">
                @auth
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                            <div class="avatar placeholder">
                                <div class="bg-neutral text-neutral-content rounded-full w-8">
                                    <span class="text-xs">{{ auth()->user()->initials() }}</span>
                                </div>
                            </div>
                        </div>
                        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                            <li><a href="{{ route('dashboard') }}" wire:navigate>
                                <x-lucide-home class="w-4 h-4" />
                                {{ __('Dashboard') }}
                            </a></li>
                            <li><a href="{{ route('profile.edit') }}" wire:navigate>
                                <x-lucide-settings class="w-4 h-4" />
                                {{ __('Settings') }}
                            </a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full">
                                        <x-lucide-log-out class="w-4 h-4 mr-2" />
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <div class="flex gap-2">
                        <a href="/login" class="btn btn-outline btn-sm">{{ __('Sign In') }}</a>
                        <a href="/register" class="btn btn-primary btn-sm">{{ __('Sign Up') }}</a>
                    </div>
                @endauth

                <!-- Mobile Menu Button -->
                <div class="dropdown lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost">
                        <x-lucide-menu class="w-5 h-5" />
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        @auth
                            <li><a href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</a></li>
                        @endauth
                        <li><a href="/events" wire:navigate>{{ __('Events') }}</a></li>
                        <li><a href="{{ route('groups.index') }}" wire:navigate>{{ __('Groups') }}</a></li>
                        @auth
                            @if(auth()->user()->isAdmin())
                                <li><a href="{{ route('admin.groups.index') }}" wire:navigate>{{ __('Manage Groups') }}</a></li>
                                @if(auth()->user()->isSuperuser())
                                    <li><a href="/admin/users" wire:navigate>{{ __('Manage Users') }}</a></li>
                                @endif
                            @endif
                            @if(auth()->user()->isTrustedUser())
                                <li><a href="/moderate/comments" wire:navigate>{{ __('Moderate') }}</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>
            </div>
        </div>


        <!-- Main Content -->
        <main id="main-content" class="container mx-auto px-4 py-8" role="main">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-base-200 bg-base-100">
            <div class="container mx-auto px-4 py-6">
                <div class="text-center text-sm text-base-content/70">
                    <p>&copy; {{ date('Y') }} Learn-it Berlin. {{ __('Bringing together the Berlin tech community.') }}</p>
                </div>
            </div>
        </footer>
        @stack('scripts')
        @livewireScripts
    </body>
</html>

<x-layouts.public title="{{ __('Page Not Found') }}">
    <div class="text-center py-12">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-base-200 mb-6">
            <x-lucide-file-question class="h-8 w-8 text-primary" />
        </div>
        <div class="space-y-3">
            <h1 class="text-3xl font-bold">{{ __('Page Not Found') }}</h1>
            <p class="text-base-content/70 max-w-md mx-auto">
                {{ __('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.') }}
            </p>
            <div class="pt-4">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    {{ __('Go Home') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>

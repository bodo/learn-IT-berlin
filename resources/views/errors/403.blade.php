<x-layouts.public title="{{ __('Access Denied') }}">
    <div class="text-center py-12">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-error/10 mb-6">
            <x-lucide-shield-x class="h-8 w-8 text-error" />
        </div>
        <div class="space-y-3">
            <h1 class="text-3xl font-bold">{{ __('Access Denied') }}</h1>
            <p class="text-base-content/70 max-w-md mx-auto">
                {{ __('You do not have permission to access this resource.') }}
            </p>
            <div class="pt-4">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    {{ __('Go Home') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>

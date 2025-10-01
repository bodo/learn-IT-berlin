@props([
    'type' => 'info',
    'message' => null,
    'timeout' => 5000,
])

@php
    $message ??= '';
    $type = in_array($type, ['info', 'success', 'warning', 'error']) ? $type : 'info';
@endphp

@if ($message !== '')
    <div
        class="toast toast-end z-50"
        x-data="{ open: true }"
        x-cloak
        x-show="open"
        x-transition.opacity
        x-init="setTimeout(() => open = false, {{ (int) $timeout }})"
    >
        <div class="alert alert-{{ $type }} shadow-lg" role="status">
            <span>{{ $message }}</span>
            <button
                type="button"
                class="btn btn-ghost btn-xs"
                x-on:click="open = false"
            >
                <x-lucide-x class="h-4 w-4" />
                <span class="sr-only">{{ __('Close') }}</span>
            </button>
        </div>
    </div>
@endif

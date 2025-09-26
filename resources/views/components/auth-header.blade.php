@props([
    'title',
    'description',
])

<div class="text-center space-y-2">
    <h1 class="text-3xl font-bold">{{ $title }}</h1>
    <p class="text-base-content/70">{{ $description }}</p>
</div>

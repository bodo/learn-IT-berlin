@props([
    'items' => [],
])

@if (! empty($items))
    <nav aria-label="{{ __('Breadcrumb') }}" class="breadcrumbs text-sm text-base-content/60">
        <ul>
            @foreach ($items as $item)
                @php($label = $item['label'] ?? null)
                @continue(empty($label))

                @if (! empty($item['url']) && ! $loop->last)
                    <li>
                        <a href="{{ $item['url'] }}" @class(['link-hover'])>
                            {{ $label }}
                        </a>
                    </li>
                @else
                    <li class="font-semibold text-base-content" aria-current="page">{{ $label }}</li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif

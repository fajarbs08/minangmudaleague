@php
    $isLink = isset($href) && filled($href);
    $classes = trim('dropdown-item d-flex align-items-center gap-2 '.($class ?? ''));
    $type = $type ?? 'button';
    $attributes = $attributes ?? [];

    if (!empty($disabled ?? false)) {
        $attributes['disabled'] = true;
    }
@endphp

@if ($isLink)
    <a
        href="{{ $href }}"
        class="{{ $classes }}"
        @foreach ($attributes as $attribute => $value)
            @if (is_bool($value))
                @if ($value) {{ $attribute }} @endif
            @elseif (!is_null($value))
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
    >
        <i data-lucide="{{ $icon }}" class="fs-14"></i>
        <span>{{ $label }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        class="{{ $classes }}"
        @foreach ($attributes as $attribute => $value)
            @if (is_bool($value))
                @if ($value) {{ $attribute }} @endif
            @elseif (!is_null($value))
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
    >
        <i data-lucide="{{ $icon }}" class="fs-14"></i>
        <span>{{ $label }}</span>
    </button>
@endif

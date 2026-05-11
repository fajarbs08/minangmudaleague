@php
    $isLink = isset($href) && filled($href);
    $classes = trim('dropdown-item competition-action-entry '.($class ?? ''));
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
        <span class="competition-action-icon" aria-hidden="true">
            <i data-lucide="{{ $icon }}" class="fs-14"></i>
        </span>
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
        <span class="competition-action-icon" aria-hidden="true">
            <i data-lucide="{{ $icon }}" class="fs-14"></i>
        </span>
        <span>{{ $label }}</span>
    </button>
@endif

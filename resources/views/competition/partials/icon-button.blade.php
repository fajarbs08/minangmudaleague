@php
    $href = $href ?? null;
    $label = $label ?? '';
    $icon = $icon ?? null;
    $class = trim($class ?? '');
    $type = $type ?? 'button';
    $attributes = $attributes ?? [];
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        class="btn d-inline-flex align-items-center gap-2 {{ $class }}"
        @foreach ($attributes as $attribute => $value)
            @if (is_bool($value))
                @if ($value) {{ $attribute }} @endif
            @elseif (!is_null($value))
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
    >
        @if ($icon)
            <i data-lucide="{{ $icon }}" class="fs-14"></i>
        @endif
        <span>{{ $label }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        class="btn d-inline-flex align-items-center gap-2 {{ $class }}"
        @foreach ($attributes as $attribute => $value)
            @if (is_bool($value))
                @if ($value) {{ $attribute }} @endif
            @elseif (!is_null($value))
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
    >
        @if ($icon)
            <i data-lucide="{{ $icon }}" class="fs-14"></i>
        @endif
        <span>{{ $label }}</span>
    </button>
@endif

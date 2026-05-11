@php
    $imageUrl = $imageUrl ?? null;
    $label = trim((string) ($label ?? ''));
    $imgClass = trim((string) ($imgClass ?? ''));
    $badgeClass = trim((string) ($badgeClass ?? ''));
    $initials = trim((string) ($initials ?? ''));
    $loading = trim((string) ($loading ?? 'lazy'));
    $decoding = trim((string) ($decoding ?? 'async'));
    $fetchpriority = trim((string) ($fetchpriority ?? ''));
    $width = filled($width ?? null) ? (string) $width : '';
    $height = filled($height ?? null) ? (string) $height : '';

    if ($initials === '') {
        $initials = \Illuminate\Support\Str::of($label !== '' ? $label : 'Klub')
            ->replaceMatches('/[^A-Za-z0-9 ]+/', ' ')
            ->upper()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
            ->implode('');
    }

    if ($initials === '') {
        $initials = 'KL';
    }
@endphp

@if (filled($imageUrl))
    <img
        src="{{ $imageUrl }}"
        alt="{{ $label !== '' ? $label : 'Identitas' }}"
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        @if ($width !== '') width="{{ $width }}" @endif
        @if ($height !== '') height="{{ $height }}" @endif
        @if ($fetchpriority !== '') fetchpriority="{{ $fetchpriority }}" @endif
        @class([$imgClass])
    >
@else
    <span @class([$badgeClass]) aria-hidden="true">{{ $initials }}</span>
@endif

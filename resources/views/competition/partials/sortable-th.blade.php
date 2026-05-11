@php
    $sortParam = $sortParam ?? 'sort';
    $directionParam = $directionParam ?? 'direction';
    $currentSort = request($sortParam, $defaultSort ?? null);
    $currentDirection = request($directionParam, $defaultDirection ?? 'desc');
    $isActive = $currentSort === $key;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $indicator = $isActive ? ($currentDirection === 'asc' ? '↑' : '↓') : '↕';
@endphp
<th class="{{ $class ?? '' }}">
    <a
        href="{{ request()->fullUrlWithQuery([$sortParam => $key, $directionParam => $nextDirection, 'page' => 1]) }}"
        class="competition-sort-link {{ $isActive ? 'active' : '' }}"
    >
        <span>{{ $label }}</span>
        <span class="competition-sort-indicator" aria-hidden="true">{{ $indicator }}</span>
    </a>
</th>

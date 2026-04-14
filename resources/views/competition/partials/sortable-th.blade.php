@php
    $currentSort = request('sort', $defaultSort ?? null);
    $currentDirection = request('direction', $defaultDirection ?? 'desc');
    $isActive = $currentSort === $key;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $indicator = $isActive ? ($currentDirection === 'asc' ? '↑' : '↓') : '↕';
@endphp
<th class="{{ $class ?? '' }}">
    <a
        href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => $nextDirection, 'page' => 1]) }}"
        class="competition-sort-link {{ $isActive ? 'active' : '' }}"
    >
        <span>{{ $label }}</span>
        <span class="competition-sort-indicator" aria-hidden="true">{{ $indicator }}</span>
    </a>
</th>

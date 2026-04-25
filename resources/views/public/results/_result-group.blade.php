@php
    $groupSlug = \Illuminate\Support\Str::slug($dateLabel);
@endphp

<section aria-labelledby="result-group-{{ $groupSlug }}" class="lap-results-group">
    <header class="lap-results-group-header">
        <h3 id="result-group-{{ $groupSlug }}">{{ $dateLabel }}</h3>
        <span>{{ $matches->count() }} pertandingan</span>
    </header>

    @foreach ($matches as $row)
        @include('public.results._result-row', ['row' => $row])
    @endforeach
</section>

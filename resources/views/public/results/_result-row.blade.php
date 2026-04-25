@php
    $rowStatusLabel = match ($row['status']) {
        'LIVE' => 'LIVE',
        'SCHEDULED' => 'UPCOMING',
        default => 'FT',
    };
    $matchMeta = collect([$row['time'], $row['competition_format_label'], $row['age_group']])
        ->filter()
        ->map(fn ($value) => strtoupper((string) $value))
        ->implode(' · ');
@endphp

<article class="match-single sm lap-result-match">
    <div class="match-single-content">
        <div class="match-scores">
            <div class="club club1">
                <div class="club-logo mr--20">
                    <span class="club-name">{{ strtoupper($row['home_name']) }}</span>
                    @include('public.partials.identity-mark', ['imageUrl' => $row['home_logo'], 'label' => $row['home_name'], 'badgeClass' => 'lap-results-club-mark'])
                </div>
            </div>

            <div class="colon lap-result-score">@include('public.partials.match-score', ['homeScore' => $row['home_score'], 'awayScore' => $row['away_score'], 'separator' => ' : '])<small>{{ $rowStatusLabel }}</small></div>

            <div class="club club2">
                <div class="club-logo ml--20">
                    @include('public.partials.identity-mark', ['imageUrl' => $row['away_logo'], 'label' => $row['away_name'], 'badgeClass' => 'lap-results-club-mark'])
                    <span class="club-name">{{ strtoupper($row['away_name']) }}</span>
                </div>
            </div>
        </div>

        <div class="block-wrap">
            <span class="match-date">{{ $matchMeta }}</span>
            <span class="stadium-name">{{ strtoupper($row['venue']) }}</span>
        </div>
    </div>

    <div class="match-bottom-action">
        <span class="action-item first-child">{{ $rowStatusLabel }}</span>
        @if ($row['detail_url'])
            <a href="{{ $row['detail_url'] }}" class="action-item">DETAIL LAGA</a>
        @else
            <span class="action-item">DETAIL BELUM ADA</span>
        @endif
    </div>
</article>

@php
    $homeScore = is_null($homeScore ?? null) ? null : (int) $homeScore;
    $awayScore = is_null($awayScore ?? null) ? null : (int) $awayScore;
    $separator = $separator ?? ' - ';
    $homeWinning = $homeScore !== null && $awayScore !== null && $homeScore > $awayScore;
    $awayWinning = $homeScore !== null && $awayScore !== null && $awayScore > $homeScore;
    $homeLosing = $homeScore !== null && $awayScore !== null && $homeScore < $awayScore;
    $awayLosing = $homeScore !== null && $awayScore !== null && $awayScore < $homeScore;
@endphp

<span class="lap-match-score-value{{ $homeWinning ? ' is-winner' : '' }}{{ $homeLosing ? ' is-loser' : '' }}" style="{{ $homeWinning ? 'color: var(--lap-match-score-winner, #f97316); font-weight: 800;' : '' }}">{{ $homeScore ?? '-' }}</span>{{ $separator }}<span class="lap-match-score-value{{ $awayWinning ? ' is-winner' : '' }}{{ $awayLosing ? ' is-loser' : '' }}" style="{{ $awayWinning ? 'color: var(--lap-match-score-winner, #f97316); font-weight: 800;' : '' }}">{{ $awayScore ?? '-' }}</span>

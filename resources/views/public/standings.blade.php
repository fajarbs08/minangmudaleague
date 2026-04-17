@extends('public.layout')

@php
    $leagueGroupCount = $publicStandings->count();
    $knockoutGroupCount = $publicKnockoutBrackets->count();
    $knockoutMatchCount = $publicKnockoutBrackets->sum(fn ($bracket) => collect($bracket['rounds'] ?? [])->sum(fn ($round) => collect($round['matches'] ?? [])->count()));
    $hasCompetitionData = $leagueGroupCount > 0 || $knockoutGroupCount > 0;

    $nextPowerOfTwo = function (int $value): int {
        $power = 1;

        while ($power < max(1, $value)) {
            $power *= 2;
        }

        return $power;
    };

    $roundCountForPairs = function (int $pairCount): int {
        $rounds = 0;
        $matches = max(1, $pairCount);

        while ($matches >= 1) {
            $rounds++;

            if ($matches === 1) {
                break;
            }

            $matches = intdiv($matches, 2);
        }

        return $rounds;
    };

    $bracketLibraryPayloads = $publicKnockoutBrackets
        ->values()
        ->map(function ($bracket, int $index) use ($nextPowerOfTwo, $roundCountForPairs) {
            $rounds = collect($bracket['rounds'] ?? [])->values();
            $firstRoundMatches = collect(optional($rounds->first())['matches'] ?? [])->values();
            $firstRoundPairCount = $nextPowerOfTwo($firstRoundMatches->count());
            $roundCount = max($roundCountForPairs($firstRoundPairCount), $rounds->count());
            $finalMatch = collect(optional($rounds->last())['matches'] ?? [])->first();

            $championName = null;

            if ($finalMatch?->is_finished && $finalMatch->score_club_a !== null && $finalMatch->score_club_b !== null && $finalMatch->score_club_a !== $finalMatch->score_club_b) {
                $championName = $finalMatch->score_club_a > $finalMatch->score_club_b
                    ? ($finalMatch->clubA?->short_name ?: $finalMatch->clubA?->name)
                    : ($finalMatch->clubB?->short_name ?: $finalMatch->clubB?->name);
            }

            $teams = collect(range(0, $firstRoundPairCount - 1))
                ->map(function (int $slot) use ($firstRoundMatches) {
                    $match = $firstRoundMatches->get($slot);

                    return [
                        $match?->clubA?->short_name ?: $match?->clubA?->name,
                        $match?->clubB?->short_name ?: $match?->clubB?->name,
                    ];
                })
                ->values()
                ->all();

            $expectedMatches = $firstRoundPairCount;
            $results = [];

            for ($roundIndex = 0; $roundIndex < $roundCount; $roundIndex++) {
                $roundMatches = collect(optional($rounds->get($roundIndex))['matches'] ?? [])->values();
                $results[] = collect(range(0, max(0, $expectedMatches - 1)))
                    ->map(function (int $slot) use ($roundMatches, $roundIndex) {
                        $match = $roundMatches->get($slot);

                        return [
                            $match?->score_club_a,
                            $match?->score_club_b,
                            $match ? [
                                'id' => $match->id,
                                'round_label' => $match->round_display_label,
                                'match_day' => $match->match_day ?: 'Matchday',
                                'match_date' => optional($match->match_date)->translatedFormat('d F Y'),
                                'kickoff_time' => optional($match->kickoff_time)->format('H:i'),
                                'venue' => $match->venue ?: 'Venue belum diisi',
                                'club_a' => $match->clubA?->name ?: 'TBD',
                                'club_b' => $match->clubB?->name ?: 'TBD',
                                'club_a_short' => $match->clubA?->short_name ?: $match->clubA?->name ?: 'TBD',
                                'club_b_short' => $match->clubB?->short_name ?: $match->clubB?->name ?: 'TBD',
                                'score_a' => $match->score_club_a,
                                'score_b' => $match->score_club_b,
                                'is_finished' => (bool) $match->is_finished,
                                'result_summary' => $match->is_finished ? $match->result_summary : 'Pertandingan belum selesai',
                                'goal_reports' => collect([$match->clubA, $match->clubB])
                                    ->filter()
                                    ->map(function ($club) use ($match) {
                                        return [
                                            'club' => $club->short_name ?: $club->name,
                                            'items' => $match->goalReportForClub($club->id),
                                        ];
                                    })
                                    ->filter(fn ($item) => !empty($item['items']))
                                    ->values()
                                    ->all(),
                                'round_index' => $roundIndex,
                            ] : null,
                        ];
                    })
                    ->values()
                    ->all();

                $expectedMatches = max(1, intdiv($expectedMatches, 2));
            }

            return [
                'id' => 'public-bracket-'.$index,
                'title' => $bracket['age_group']?->name ?: 'Knockout',
                'round_labels' => $rounds->map(fn ($round) => $round['label'])->values()->all(),
                'match_count' => $rounds->sum(fn ($round) => collect($round['matches'] ?? [])->count()),
                'champion_name' => $championName,
                'init' => [
                    'teams' => $teams,
                    'results' => [$results],
                ],
                'matches' => $rounds->flatMap(fn ($round) => collect($round['matches'] ?? []))->values(),
            ];
        })
        ->values();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('kester-assets/vendors/jquery-bracket/jquery.bracket.min.css') }}">
    <style>
        .standings-overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 40px;
        }

        .standings-overview-card {
            position: relative;
            overflow: hidden;
            min-height: 150px;
            border-radius: 20px;
            padding: 24px;
            background: linear-gradient(145deg, #121b31 0%, #1e325d 100%);
            color: #fff;
            box-shadow: 0 24px 50px rgba(12, 20, 38, 0.18);
        }

        .standings-overview-card::after {
            content: '';
            position: absolute;
            inset: auto -24px -34px auto;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .standings-overview-label {
            display: inline-block;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }

        .standings-overview-value {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: 42px;
            line-height: 1;
            color: #fff;
        }

        .standings-overview-copy {
            position: relative;
            z-index: 1;
            margin-top: 10px;
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.82);
        }

        .competition-block + .competition-block {
            margin-top: 55px;
        }

        .competition-subtitle {
            max-width: 760px;
            margin: 10px auto 0;
            text-align: center;
            color: #6c757d;
        }

        .lap-bracket-panel {
            background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
            border: 1px solid rgba(28, 49, 94, 0.08);
            border-radius: 24px;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.07);
            padding: 24px;
        }

        .lap-bracket-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .lap-bracket-summary-copy {
            max-width: 600px;
        }

        .lap-bracket-summary-copy h3 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        .lap-bracket-summary-copy p {
            margin-bottom: 0;
            color: #667085;
        }

        .lap-bracket-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .lap-bracket-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 14px;
            background: #eef3ff;
            color: #1c315e;
            font-size: 13px;
            font-weight: 700;
        }

        .lap-bracket-badge.is-champion {
            background: linear-gradient(135deg, #fff2bf 0%, #ffd76a 100%);
            color: #6b4b00;
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.24);
        }

        .lap-bracket-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            padding: 14px 4px 10px;
        }

        .lap-bracket-canvas {
            display: inline-block;
            min-width: 100%;
        }

        .lap-bracket-panel .jQBracket {
            font-family: inherit;
            font-size: 14px;
        }

        .lap-bracket-panel .jQBracket .match {
            cursor: pointer;
        }

        .lap-bracket-panel .jQBracket .team {
            background: #f8faff;
            border: 1px solid rgba(28, 49, 94, 0.08);
            border-radius: 12px;
            color: #14213d;
            overflow: hidden;
        }

        .lap-bracket-panel .jQBracket .team:first-child {
            border-bottom: 1px solid rgba(28, 49, 94, 0.08);
        }

        .lap-bracket-panel .jQBracket .team div.label,
        .lap-bracket-panel .jQBracket .team div.score {
            height: 44px;
            line-height: 38px;
            padding: 3px 12px;
        }

        .lap-bracket-panel .jQBracket .team.win {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .lap-bracket-panel .jQBracket .match:hover .team.win,
        .lap-bracket-panel .jQBracket .match:hover .team.lose,
        .lap-bracket-panel .jQBracket .match:hover .team.na,
        .lap-bracket-panel .jQBracket .match:hover .team.bye,
        .lap-bracket-panel .jQBracket .match:hover .team.np {
            transform: translateY(-1px);
        }

        .lap-bracket-panel .jQBracket .team.win div.score {
            color: #0f7b0f;
            background: rgba(16, 185, 129, 0.08);
        }

        .lap-bracket-panel .jQBracket .team.lose {
            background: #eef2f7;
            color: #8a94a6;
        }

        .lap-bracket-panel .jQBracket .team.lose div.score {
            color: #c2410c;
            background: rgba(249, 115, 22, 0.08);
        }

        .lap-bracket-panel .jQBracket .team.na,
        .lap-bracket-panel .jQBracket .team.bye,
        .lap-bracket-panel .jQBracket .team.np {
            background: #e5e7eb;
            color: #98a2b3;
        }

        .lap-bracket-panel .jQBracket .connector,
        .lap-bracket-panel .jQBracket .connector div.connector {
            border-color: #d2d8e5;
        }

        .lap-bracket-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .knockout-match-card {
            border-radius: 20px;
            padding: 18px;
            background: #fff;
            border: 1px solid rgba(28, 49, 94, 0.08);
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.06);
        }

        .knockout-match-meta {
            margin-bottom: 14px;
            font-size: 13px;
            color: #6c757d;
        }

        .knockout-team-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-weight: 700;
            color: #14213d;
        }

        .knockout-team-row + .knockout-team-row {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed rgba(108, 117, 125, 0.35);
        }

        .knockout-team-name {
            min-width: 0;
        }

        .knockout-score-pill {
            min-width: 40px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #f4f7fb;
            text-align: center;
            font-weight: 800;
            color: #1c315e;
        }

        .knockout-match-status {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(28, 49, 94, 0.08);
            font-size: 13px;
            color: #6c757d;
        }

        .knockout-goals {
            margin-top: 12px;
            padding-left: 18px;
            color: #495057;
        }

        .knockout-goals li + li {
            margin-top: 6px;
        }

        .lap-modal-scoreline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 18px 0;
            padding: 18px;
            border-radius: 20px;
            background: linear-gradient(180deg, #f8faff 0%, #eef3ff 100%);
        }

        .lap-modal-club {
            flex: 1 1 0;
            min-width: 0;
            font-weight: 700;
            color: #14213d;
        }

        .lap-modal-club.is-away {
            text-align: right;
        }

        .lap-modal-score {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 10px 16px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 26px;
            font-weight: 800;
            color: #102a56;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .lap-modal-meta {
            color: #667085;
            font-size: 14px;
        }

        .lap-modal-goals {
            margin: 14px 0 0;
            padding-left: 18px;
        }

        .lap-modal-goals li + li {
            margin-top: 8px;
        }

        @media (max-width: 767.98px) {
            .standings-overview-card {
                min-height: auto;
                padding: 20px;
            }

            .standings-overview-value {
                font-size: 34px;
            }

            .lap-bracket-panel {
                padding: 18px;
            }

            .lap-bracket-summary-copy h3 {
                font-size: 22px;
            }

            .lap-bracket-panel .jQBracket .team div.label,
            .lap-bracket-panel .jQBracket .team div.score {
                height: 40px;
                line-height: 34px;
                padding: 3px 10px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="rts-latest-match">
        <div class="container">
            <div class="latest-match-inner">
                <div class="club-area">
                    <div class="club-logo"><img src="{{ $headlineMatch?->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-1.svg') }}" alt=""></div>
                    <div class="content">
                        <h3 class="club-text">{{ strtoupper($headlineMatch?->clubA?->short_name ?: $headlineMatch?->clubA?->name ?: 'KLUB A') }}</h3>
                        <span class="match-type">{{ strtoupper($headlineMatch?->ageGroup?->name ?: 'KOMPETISI') }}</span>
                    </div>
                </div>
                <div class="match-countdown-area">
                    <div class="countdown">
                        <div class="countdown-el days-c"><span class="value">{{ $leagueGroupCount }}</span></div>
                        <span class="letter">LIGA</span>
                        <div class="countdown-el hours-c"><span class="value">{{ $knockoutGroupCount }}</span></div>
                        <span class="letter">KNOCKOUT</span>
                    </div>
                </div>
                <div class="club-area">
                    <div class="content text-end ml--40 mr--0">
                        <h3 class="club-text">{{ strtoupper($headlineMatch?->clubB?->short_name ?: $headlineMatch?->clubB?->name ?: 'KLUB B') }}</h3>
                        <span class="match-type">{{ strtoupper($headlineMatch?->round_display_label ?: 'BRACKET') }}</span>
                    </div>
                    <div class="club-logo ml--40 mr--0"><img src="{{ $headlineMatch?->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-2.svg') }}" alt=""></div>
                </div>
            </div>
        </div>
    </div>

    <div class="rts-point-table-section section-gap">
        <div class="container">
            @if ($hasCompetitionData)
                <div class="standings-overview-grid">
                    <div class="standings-overview-card">
                        <span class="standings-overview-label">Grup Liga</span>
                        <h3 class="standings-overview-value">{{ $leagueGroupCount }}</h3>
                        <p class="standings-overview-copy">Klasemen berdasarkan pertandingan liga yang telah selesai.</p>
                    </div>
                    <div class="standings-overview-card">
                        <span class="standings-overview-label">Bracket Knockout</span>
                        <h3 class="standings-overview-value">{{ $knockoutGroupCount }}</h3>
                        <p class="standings-overview-copy">Jalur pertandingan gugur per kelompok usia.</p>
                    </div>
                    <div class="standings-overview-card">
                        <span class="standings-overview-label">Laga Knockout</span>
                        <h3 class="standings-overview-value">{{ $knockoutMatchCount }}</h3>
                        <p class="standings-overview-copy">Total partai yang masuk susunan bracket.</p>
                    </div>
                </div>
            @endif

            @if ($publicStandings->isNotEmpty())
                <div class="competition-block">
                    <div class="section-title-area section-title-area-inner mb--40">
                        <h1 class="section-title">KLASEMEN LIGA</h1>
                        <p class="competition-subtitle">Posisi klub dihitung dari poin, selisih gol, dan produktivitas gol pada pertandingan format liga.</p>
                    </div>

                    @foreach ($publicStandings as $standing)
                        <div class="title-area">
                            <h2 class="title text-center">{{ strtoupper($standing['age_group']?->name ?: 'LEAGUE') }}</h2>
                        </div>
                        <div class="table-area table-full mb--50">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr class="head-tr">
                                        <th>POSISI</th>
                                        <th>NAMA KLUB</th>
                                        <th>MAIN</th>
                                        <th>MENANG</th>
                                        <th>IMBANG</th>
                                        <th>KALAH</th>
                                        <th>SELISIH</th>
                                        <th>POIN</th>
                                    </tr>
                                    @foreach ($standing['rows'] as $row)
                                        <tr>
                                            <td><span class="position-number">{{ str_pad((string) $row['position'], 2, '0', STR_PAD_LEFT) }}</span></td>
                                            <td><div class="player-name-area"><h4 class="player-name">{{ $row['club_name'] }}</h4></div></td>
                                            <td><span class="match-count">{{ $row['played'] }}</span></td>
                                            <td><span class="win-count">{{ $row['won'] }}</span></td>
                                            <td><span class="draw-count">{{ $row['drawn'] }}</span></td>
                                            <td><span class="lose-count">{{ $row['lost'] }}</span></td>
                                            <td><span class="due-count">{{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}</span></td>
                                            <td><span class="pts-count">{{ $row['points'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($bracketLibraryPayloads->isNotEmpty())
                <div class="competition-block">
                    <div class="section-title-area section-title-area-inner mb--40">
                        <h1 class="section-title">BRACKET KNOCKOUT</h1>
                        <p class="competition-subtitle">Format gugur sekarang dirender dengan library bracket khusus agar alur antar babak lebih jelas dan lebih enak dipindai.</p>
                    </div>

                    @foreach ($bracketLibraryPayloads as $payload)
                        <div class="lap-bracket-panel mb--50">
                            <div class="lap-bracket-summary">
                                <div class="lap-bracket-summary-copy">
                                    <h3>{{ strtoupper($payload['title']) }}</h3>
                                    <p>Bracket ini menampilkan jalur utama knockout. Detail skor, jadwal, dan status tiap laga tetap tersedia di bawah bracket.</p>
                                </div>
                                <div class="lap-bracket-badges">
                                    <span class="lap-bracket-badge">{{ $payload['match_count'] }} pertandingan</span>
                                    @if ($payload['champion_name'])
                                        <span class="lap-bracket-badge is-champion">Juara: {{ $payload['champion_name'] }}</span>
                                    @endif
                                    @foreach ($payload['round_labels'] as $label)
                                        <span class="lap-bracket-badge">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="lap-bracket-scroll">
                                <div
                                    id="{{ $payload['id'] }}"
                                    class="lap-bracket-canvas"
                                    data-bracket-init-id="{{ $payload['id'] }}-data"
                                ></div>
                                <script type="application/json" id="{{ $payload['id'] }}-data">@json($payload['init'])</script>
                            </div>

                            <div class="lap-bracket-detail-grid">
                                @foreach ($payload['matches'] as $match)
                                    <div class="knockout-match-card">
                                        <div class="knockout-match-meta">
                                            {{ $match->round_display_label }} · {{ $match->match_day ?: 'Matchday' }} · {{ optional($match->match_date)->translatedFormat('d M Y') ?: '-' }} · {{ optional($match->kickoff_time)->format('H:i') ?: '--:--' }}
                                        </div>
                                        <div class="knockout-team-row">
                                            <span class="knockout-team-name">{{ $match->clubA?->short_name ?: $match->clubA?->name ?: 'TBD' }}</span>
                                            <span class="knockout-score-pill">{{ $match->score_club_a ?? '-' }}</span>
                                        </div>
                                        <div class="knockout-team-row">
                                            <span class="knockout-team-name">{{ $match->clubB?->short_name ?: $match->clubB?->name ?: 'TBD' }}</span>
                                            <span class="knockout-score-pill">{{ $match->score_club_b ?? '-' }}</span>
                                        </div>
                                        <div class="knockout-match-status">
                                            {{ $match->is_finished ? $match->result_summary : 'Pertandingan belum selesai' }}
                                        </div>

                                        @if ($match->goalEvents->isNotEmpty())
                                            <ul class="knockout-goals">
                                                @foreach ([$match->clubA, $match->clubB] as $club)
                                                    @php
                                                        $goalReport = $match->goalReportForClub($club?->id);
                                                    @endphp
                                                    @if ($club && !empty($goalReport))
                                                        <li><strong>{{ $club->short_name ?: $club->name }}:</strong> {{ implode(', ', $goalReport) }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @unless ($hasCompetitionData)
                <div class="lap-summary-card">
                    <h3 class="section-title mb--20">Klasemen dan bracket belum tersedia</h3>
                    <p class="lap-copy mb-0">Data akan tampil setelah hasil liga atau susunan pertandingan knockout tercatat dalam sistem.</p>
                </div>
            @endunless
        </div>
    </div>

    <div class="modal fade" id="bracketMatchDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" data-bracket-modal-title>Detail Pertandingan</h5>
                        <div class="lap-modal-meta" data-bracket-modal-meta></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="lap-modal-scoreline">
                        <div class="lap-modal-club" data-bracket-modal-club-a>TBD</div>
                        <div class="lap-modal-score" data-bracket-modal-score>-</div>
                        <div class="lap-modal-club is-away" data-bracket-modal-club-b>TBD</div>
                    </div>
                    <p class="mb-3 fw-semibold text-dark" data-bracket-modal-summary>Belum ada hasil</p>
                    <div class="lap-modal-meta" data-bracket-modal-venue>Venue belum diisi</div>
                    <ul class="lap-modal-goals" data-bracket-modal-goals hidden></ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('kester-assets/vendors/jquery-bracket/jquery.bracket.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.bracket !== 'function') {
                return;
            }

            const modalElement = document.getElementById('bracketMatchDetailModal');
            const modalInstance = modalElement && window.bootstrap ? new window.bootstrap.Modal(modalElement) : null;

            const modalRefs = modalElement ? {
                title: modalElement.querySelector('[data-bracket-modal-title]'),
                meta: modalElement.querySelector('[data-bracket-modal-meta]'),
                clubA: modalElement.querySelector('[data-bracket-modal-club-a]'),
                clubB: modalElement.querySelector('[data-bracket-modal-club-b]'),
                score: modalElement.querySelector('[data-bracket-modal-score]'),
                summary: modalElement.querySelector('[data-bracket-modal-summary]'),
                venue: modalElement.querySelector('[data-bracket-modal-venue]'),
                goals: modalElement.querySelector('[data-bracket-modal-goals]')
            } : null;

            const renderGoalReports = (goalReports) => {
                if (!modalRefs || !modalRefs.goals) {
                    return;
                }

                if (!Array.isArray(goalReports) || goalReports.length === 0) {
                    modalRefs.goals.innerHTML = '';
                    modalRefs.goals.hidden = true;
                    return;
                }

                modalRefs.goals.hidden = false;
                modalRefs.goals.innerHTML = goalReports.map((report) => {
                    const items = Array.isArray(report.items) ? report.items.join(', ') : '';

                    return `<li><strong>${report.club}:</strong> ${items}</li>`;
                }).join('');
            };

            const openMatchModal = (matchData) => {
                if (!modalInstance || !modalRefs || !matchData) {
                    return;
                }

                modalRefs.title.textContent = matchData.round_label || 'Detail Pertandingan';
                modalRefs.meta.textContent = [matchData.match_day, matchData.match_date, matchData.kickoff_time ? `${matchData.kickoff_time} WIB` : null]
                    .filter(Boolean)
                    .join(' · ');
                modalRefs.clubA.textContent = matchData.club_a_short || matchData.club_a || 'TBD';
                modalRefs.clubB.textContent = matchData.club_b_short || matchData.club_b || 'TBD';
                modalRefs.score.textContent = `${matchData.score_a ?? '-'} - ${matchData.score_b ?? '-'}`;
                modalRefs.summary.textContent = matchData.result_summary || 'Pertandingan belum selesai';
                modalRefs.venue.textContent = matchData.venue || 'Venue belum diisi';
                renderGoalReports(matchData.goal_reports);
                modalInstance.show();
            };

            const createBracketOptions = (init) => {
                const compact = window.innerWidth < 768;

                return {
                    init,
                    skipConsolationRound: true,
                    disableToolbar: true,
                    disableHighlight: false,
                    centerConnectors: true,
                    teamWidth: compact ? 132 : 176,
                    scoreWidth: compact ? 34 : 44,
                    matchMargin: compact ? 28 : 36,
                    roundMargin: compact ? 36 : 56,
                    onMatchClick: openMatchModal
                };
            };

            document.querySelectorAll('[data-bracket-init-id]').forEach((element) => {
                const dataId = element.getAttribute('data-bracket-init-id');

                if (!dataId) {
                    return;
                }

                const source = document.getElementById(dataId);

                if (!source) {
                    return;
                }

                const init = JSON.parse(source.textContent || '{}');
                window.jQuery(element).bracket(createBracketOptions(init));
            });
        });
    </script>
@endpush

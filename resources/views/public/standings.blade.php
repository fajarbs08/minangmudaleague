@extends('public.public-layout')

@push('styles')
    <style>
        .lap-public,
        .lap-public .lap-page-shell {
            background: #ffffff;
        }

        .lap-public .latest-world-ranking-section {
            background: #ffffff;
            padding-top: 80px;
            padding-bottom: 96px;
            color: #030523;
        }

        .lap-public .latest-world-ranking-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-public .latest-world-ranking-section .container {
                max-width: 1680px;
            }
        }

        .lap-public .latest-world-ranking-wrapper .content h3,
        .lap-public .latest-world-ranking-wrapper .ranking-box-style p,
        .lap-public .latest-world-ranking-wrapper .ranking-box-style span,
        .lap-public .latest-world-ranking-wrapper .ranking-box-style h3,
        .lap-public .latest-world-ranking-wrapper table th,
        .lap-public .latest-world-ranking-wrapper table td {
            color: #030523;
        }

        .lap-public .latest-world-ranking-wrapper .content h3 {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-wrapper .text-item p {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-wrapper .ranking-box-style p,
        .lap-public .latest-world-ranking-wrapper .ranking-box-style span,
        .lap-public .latest-world-ranking-wrapper .ranking-box-style h3,
        .lap-public .latest-world-ranking-wrapper table th,
        .lap-public .latest-world-ranking-wrapper table td {
            font-family: 'Chakra Petch', sans-serif;
            letter-spacing: .01em;
        }

        .lap-public .latest-world-ranking-wrapper .ranking-box-style p {
            text-transform: uppercase;
            font-size: 16px;
            font-weight: 700;
            color: #030523;
            margin-top: 15px;
            line-height: 1;
        }

        .lap-public .latest-world-ranking-wrapper .ranking-box-style span {
            font-size: 14px;
            font-weight: 600;
        }

        .lap-public .latest-world-ranking-wrapper .ranking-box-style h3 {
            font-size: 32px;
            font-weight: 900;
            margin-top: 7px;
        }

        .lap-public .latest-world-ranking-wrapper .ranking-box-style {
            background-color: #F5F6F6;
            text-align: center;
            padding: 30px 18px;
        }

        .lap-public .latest-world-ranking-table {
            margin-top: 70px;
        }

        @media (max-width: 767px) {
            .lap-public .latest-world-ranking-table {
                margin-top: 40px;
            }
        }

        .lap-public .latest-world-ranking-table .table-responsive {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .lap-public .latest-world-ranking-table table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .85), 0 8px 18px rgba(15, 23, 42, .06);
        }

        .lap-public .latest-world-ranking-table table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background: #ffffff;
            text-align: left;
        }

        .lap-public .latest-world-ranking-table tbody tr {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .lap-public .latest-world-ranking-table tbody tr:nth-child(odd) td {
            background: #ffffff;
        }

        .lap-public .latest-world-ranking-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .lap-public .latest-world-ranking-table tbody tr:hover {
            transform: translateY(-1px);
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td {
            background: #eef4ff;
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td:first-child {
            border-left: 3px solid #e41b23;
            padding-left: 12px;
        }

        .lap-public .latest-world-ranking-table tbody tr td:first-child {
            border-left: 3px solid transparent;
            transition: background-color .18s ease, border-color .18s ease, padding-left .18s ease;
        }

        .lap-public .latest-world-ranking-table tbody tr td {
            transition: background-color .18s ease, color .18s ease;
        }

        @media (max-width: 1399px) {
            .lap-public .latest-world-ranking-table table {
                width: 1100px;
            }
        }

        .lap-public .latest-world-ranking-table table thead {
            background: #f7f7f7;
        }

        .lap-public .latest-world-ranking-table thead th {
            background: transparent;
            border-bottom: 1px solid #eee;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-table table th,
        .lap-public .latest-world-ranking-table table td {
            padding: 14px 15px;
            border-bottom: 1px solid #eee;
            font-size: 18px;
            font-weight: 700;
            color: #030523;
        }

        .lap-public .latest-world-ranking-table table .team {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .lap-public .latest-world-ranking-table table .positive {
            color: green;
            font-weight: 600;
        }

        .lap-public .latest-world-ranking-table table .negative {
            color: #030523;
            font-size: 16px;
            font-weight: 700;
        }

        .lap-public .latest-world-ranking-table table .badge {
            display: inline-block;
            width: 28px;
            height: 28px;
            border-radius: 28px;
            text-align: center;
            line-height: 30px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin-right: 5px;
            padding: 0;
        }

        .lap-public .latest-world-ranking-table table .badge.w {
            background: #ff6f14;
        }

        .lap-public .latest-world-ranking-table table .badge.d {
            background: #464E5E;
        }

        .lap-public .latest-world-ranking-table table .badge.l {
            background: #030523;
        }

        .lap-public .latest-world-ranking-table .team img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 50%;
            flex: 0 0 auto;
        }

        .lap-public .latest-world-ranking-table .team .lap-team-badge,
        .lap-public .ranking-box-style .lap-ranking-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #ffffff;
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            box-shadow: inset 0 0 0 1px rgba(3, 5, 35, .08);
            flex: 0 0 auto;
        }

        .lap-public .latest-world-ranking-table .team .lap-team-badge {
            width: 36px;
            height: 36px;
            font-size: 12px;
        }

        .lap-public .ranking-box-style img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            padding: 6px;
        }

        .lap-public .ranking-box-style .lap-ranking-badge {
            width: 72px;
            height: 72px;
            margin: 0 auto;
            font-size: 22px;
        }

        .lap-public .latest-world-ranking-table .text-center a {
            color: #030523;
        }

        .lap-public .latest-world-ranking-table .lap-table-detail-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            background: linear-gradient(180deg, #ffffff 0%, #f2f6ff 100%);
            border: 1px solid rgba(3, 5, 35, 0.08);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
            transition: transform .18s ease, box-shadow .18s ease, color .18s ease, background-color .18s ease;
        }

        .lap-public .latest-world-ranking-table .lap-table-detail-link i {
            font-size: 15px;
            transition: transform .18s ease;
        }

        .lap-public .latest-world-ranking-table .lap-table-detail-link:hover,
        .lap-public .latest-world-ranking-table .lap-table-detail-link:focus-visible {
            color: #e41b23;
            transform: translateY(-1px) scale(1.06);
            background: linear-gradient(180deg, #ffffff 0%, #fff1f1 100%);
            box-shadow: 0 12px 20px rgba(228, 27, 35, .12);
            outline: none;
        }

        .lap-public .ranking-category-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 24px;
        }

        .lap-public .ranking-category-field {
            min-width: 0;
        }

        .lap-public .standings-group-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 22px 0 0;
        }

        .lap-public .standings-group-nav a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid rgba(3, 5, 35, 0.08);
            color: #030523;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: none;
            transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }

        .lap-public .standings-group-nav a:hover,
        .lap-public .standings-group-nav a:focus-visible {
            border-color: rgba(228, 27, 35, .22);
            box-shadow: 0 10px 18px rgba(15, 23, 42, .06);
            transform: translateY(-1px);
            outline: none;
        }

        .lap-public .standings-group-nav a span:last-child {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(228, 27, 35, 0.08);
            color: #e41b23;
            font-size: 12px;
            font-weight: 800;
        }

        .lap-public .standings-section-grid {
            display: grid;
            gap: 26px;
        }

        .lap-public .standings-section-card {
            border: 1px solid rgba(3, 5, 35, 0.08);
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
            scroll-margin-top: 120px;
        }

        .lap-public .standings-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 24px 18px;
            border-bottom: 1px solid rgba(3, 5, 35, 0.08);
        }

        .lap-public .standings-section-head h4 {
            margin: 0;
            font-family: 'Big Shoulders', sans-serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #030523;
        }

        .lap-public .standings-section-head p {
            margin: 6px 0 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            color: #667085;
        }

        .lap-public .standings-section-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(228, 27, 35, 0.08);
            color: #e41b23;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .lap-public .standings-leaderboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
            margin-top: 34px;
        }

        .lap-public .standings-leaderboard-card {
            border: 1px solid rgba(3, 5, 35, 0.08);
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
        }

        .lap-public .standings-leaderboard-card .leaderboard-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px 18px;
            border-bottom: 1px solid rgba(3, 5, 35, 0.08);
        }

        .lap-public .standings-leaderboard-card .leaderboard-head h4 {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            margin: 0;
        }

        .lap-public .standings-leaderboard-card .leaderboard-head p {
            margin: 6px 0 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #5b6785;
            text-transform: uppercase;
        }

        .lap-public .standings-leaderboard-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(228, 27, 35, 0.08);
            color: #e41b23;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .lap-public .standings-leaderboard-body {
            padding: 22px 24px 24px;
        }

        .lap-public .standings-leaderboard-group + .standings-leaderboard-group {
            margin-top: 24px;
            padding-top: 22px;
            border-top: 1px dashed rgba(3, 5, 35, 0.12);
        }

        .lap-public .standings-leaderboard-group h5 {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0 0 14px;
        }

        .lap-public .standings-leaderboard-list {
            display: grid;
            gap: 12px;
        }

        .lap-public .standings-leaderboard-item {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) auto;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid rgba(3, 5, 35, 0.06);
        }

        .lap-public .standings-leaderboard-rank {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 22px;
            font-weight: 900;
            color: #e41b23;
            text-align: center;
        }

        .lap-public .standings-leaderboard-player {
            min-width: 0;
        }

        .lap-public .standings-leaderboard-player strong,
        .lap-public .standings-leaderboard-player span,
        .lap-public .standings-leaderboard-metric {
            font-family: 'Chakra Petch', sans-serif;
            text-transform: uppercase;
        }

        .lap-public .standings-leaderboard-player strong {
            display: block;
            font-size: 16px;
            font-weight: 700;
            color: #030523;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lap-public .standings-leaderboard-player span {
            display: block;
            margin-top: 2px;
            font-size: 13px;
            font-weight: 600;
            color: #667085;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lap-public .standings-leaderboard-metric {
            min-width: 54px;
            text-align: right;
            font-size: 24px;
            font-weight: 900;
            color: #030523;
        }

        .lap-public .standings-leaderboard-empty {
            padding: 28px 18px;
            text-align: center;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #667085;
            text-transform: uppercase;
            border: 1px dashed rgba(3, 5, 35, 0.14);
            background: #fbfcfe;
        }

        @media (max-width: 1199px) {
            .lap-public .ranking-category-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .lap-public .ranking-category-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 20px;
            }

            .lap-public .standings-section-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .lap-public .standings-leaderboard-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 22px;
            }
        }

        @media (max-width: 575px) {
            .lap-public .ranking-category-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 16px;
            }

            .lap-public .standings-leaderboard-card .leaderboard-head,
            .lap-public .standings-leaderboard-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .lap-public .standings-leaderboard-item {
                grid-template-columns: 42px minmax(0, 1fr) auto;
                gap: 10px;
                padding: 12px 12px;
            }

            .lap-public .standings-leaderboard-rank {
                font-size: 20px;
            }

            .lap-public .standings-leaderboard-player strong {
                font-size: 14px;
            }

            .lap-public .standings-leaderboard-player span {
                font-size: 12px;
            }

            .lap-public .standings-leaderboard-metric {
                min-width: 42px;
                font-size: 20px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $selectedPublicSeason = $selectedPublicSeason ?? null;
        $publicSeasonOptions = $publicSeasonOptions ?? collect();
        $publicSeasonQuery = $publicSeasonQuery ?? [];
        $isHistoricalPublicSeason = $isHistoricalPublicSeason ?? false;
        $standingsGroups = collect($publicStandings ?? []);
        $standingsGroup = $standingsGroups->first();
        $standingsRows = collect($standingsGroup['rows'] ?? []);
        $leaderRow = $standingsRows->first();
        $bestGoalDiffRow = $standingsRows->sortByDesc('goal_difference')->first();
        $bestWinsRow = $standingsRows->sortByDesc('won')->first();
        $bestAttackRow = $standingsRows->sortByDesc('goals_for')->first();
        $bestDefenseRow = $standingsRows->sortBy('goals_against')->first();
        $mostPlayedRow = $standingsRows->sortByDesc('played')->first();
        $ageGroupName = $standingsGroups->count() === 1
            ? ($standingsGroup['age_group']?->name ?? 'Klasemen')
            : 'Klasemen Liga';
        $lastUpdatedAt = $standingsGroup['last_match_date'] ?? null;

        $clubBadge = function ($row): array {
            $logoUrl = $row['club_logo_url'] ?? null;
            $club = $row['club'] ?? null;

            if (! filled($logoUrl) && $club && filled($club->logo_file_url)) {
                $logoUrl = $club->logo_file_url;
            }

            if (! filled($logoUrl) && $club && filled($club->logo_url)) {
                $logoUrl = str_starts_with($club->logo_url, 'http')
                    ? $club->logo_url
                    : url('/storage/'.ltrim($club->logo_url, '/'));
            }

            $label = data_get($row, 'club_short_name') ?: data_get($row, 'club_name') ?: 'Klub';
            $initials = \Illuminate\Support\Str::of($label)
                ->replaceMatches('/[^A-Za-z0-9 ]+/', ' ')
                ->upper()
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
                ->implode('');

            return [
                'logo_url' => $logoUrl,
                'label' => $label,
                'initials' => $initials !== '' ? $initials : 'KL',
            ];
        };

        $leaderBadge = $leaderRow ? $clubBadge($leaderRow) : null;
        $bestGoalDiffBadge = $bestGoalDiffRow ? $clubBadge($bestGoalDiffRow) : null;
        $bestWinsBadge = $bestWinsRow ? $clubBadge($bestWinsRow) : null;
        $mostPlayedBadge = $mostPlayedRow ? $clubBadge($mostPlayedRow) : null;
        $bestDefenseBadge = $bestDefenseRow ? $clubBadge($bestDefenseRow) : null;

        $formatNumber = function ($value, int $decimals = 0): string {
            return number_format((float) ($value ?? 0), $decimals, '.', ',');
        };

        $standingsFilterOptions = $standingsFilterOptions ?? [
            'age_groups' => collect(),
            'years' => collect(),
            'dates' => collect(),
            'clubs' => collect(),
        ];

        $standingsFilters = $standingsFilters ?? [
            'age_group_id' => null,
            'year' => null,
            'date' => null,
            'club_id' => null,
        ];
    @endphp
    <section class="latest-world-ranking-section section-padding">
        <div class="container">
            <div class="latest-world-ranking-wrapper">
                <div class="content">
                    <h3>{{ $ageGroupName }}</h3>
                    <div class="text-item">
                        <p>Pembaruan terakhir: {{ $lastUpdatedAt ? \Illuminate\Support\Carbon::instance($lastUpdatedAt)->translatedFormat('d F Y') : 'Belum ada data' }}</p>
                        <p>
                            {{ $standingsRows->isNotEmpty() ? 'Data klasemen diperbarui dari pertandingan liga yang sudah selesai' : 'Belum ada pertandingan liga yang selesai' }}
                        </p>
                        @if ($standingsGroups->count() > 1)
                            <p>{{ $standingsGroups->count() }} kelompok usia ditampilkan. Gunakan filter atau navigasi cepat untuk fokus ke satu usia.</p>
                        @endif
                        @if ($selectedPublicSeason)
                            <p>{{ $selectedPublicSeason->name }}{{ $isHistoricalPublicSeason ? ' · histori' : ' · aktif' }}</p>
                        @endif
                    </div>
                </div>
                @if ($standingsGroups->count() === 1)
                <div class="row g-4">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="ranking-box-style wow fadeInUp" data-wow-delay=".15s">
                            @if (filled($leaderBadge['logo_url'] ?? null))
                                <img src="{{ $leaderBadge['logo_url'] }}" alt="{{ $leaderBadge['label'] }}" loading="lazy" decoding="async" width="72" height="72">
                            @else
                                <div class="lap-ranking-badge" aria-hidden="true">{{ $leaderBadge['initials'] ?? 'KL' }}</div>
                            @endif
                            <p>Posisi Puncak</p>
                            <span>{{ data_get($leaderRow, 'club_short_name', data_get($leaderRow, 'club_name', 'Tim teratas')) }}</span>
                            <h3>{{ str_pad((string) data_get($leaderRow, 'position', 1), 2, '0', STR_PAD_LEFT) }}</h3>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="ranking-box-style wow fadeInUp" data-wow-delay=".25s">
                            @if (filled($bestGoalDiffBadge['logo_url'] ?? null))
                                <img src="{{ $bestGoalDiffBadge['logo_url'] }}" alt="{{ $bestGoalDiffBadge['label'] }}" loading="lazy" decoding="async" width="72" height="72">
                            @else
                                <div class="lap-ranking-badge" aria-hidden="true">{{ $bestGoalDiffBadge['initials'] ?? 'KL' }}</div>
                            @endif
                            <p>Pergerakan Poin</p>
                            <span>Poin saat ini</span>
                            <h3 class="color-theme">{{ $formatNumber(data_get($leaderRow, 'points', 0), 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="ranking-box-style wow fadeInUp" data-wow-delay=".35s">
                            @if (filled($bestWinsBadge['logo_url'] ?? null))
                                <img src="{{ $bestWinsBadge['logo_url'] }}" alt="{{ $bestWinsBadge['label'] }}" loading="lazy" decoding="async" width="72" height="72">
                            @else
                                <div class="lap-ranking-badge" aria-hidden="true">{{ $bestWinsBadge['initials'] ?? 'KL' }}</div>
                            @endif
                            <p>Tim Paling Stabil</p>
                            <span>Kemenangan terbanyak</span>
                            <h3>{{ data_get($bestWinsRow, 'won', 0) }}</h3>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="ranking-box-style wow fadeInUp" data-wow-delay=".45s">
                            @if (filled($mostPlayedBadge['logo_url'] ?? null))
                                <img src="{{ $mostPlayedBadge['logo_url'] }}" alt="{{ $mostPlayedBadge['label'] }}" loading="lazy" decoding="async" width="72" height="72">
                            @else
                                <div class="lap-ranking-badge" aria-hidden="true">{{ $mostPlayedBadge['initials'] ?? 'KL' }}</div>
                            @endif
                            <p>Posisi Baru</p>
                            <span>Laga dimainkan</span>
                            <h3>{{ data_get($mostPlayedRow, 'played', 0) }}</h3>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="ranking-box-style wow fadeInUp" data-wow-delay=".55s">
                            @if (filled($bestGoalDiffBadge['logo_url'] ?? null))
                                <img src="{{ $bestGoalDiffBadge['logo_url'] }}" alt="{{ $bestGoalDiffBadge['label'] }}" loading="lazy" decoding="async" width="72" height="72">
                            @else
                                <div class="lap-ranking-badge" aria-hidden="true">{{ $bestGoalDiffBadge['initials'] ?? 'KL' }}</div>
                            @endif
                            <p>Kenaikan Tercepat</p>
                            <span>Selisih gol tertinggi</span>
                            <h3 class="color-theme2">+{{ $formatNumber(data_get($bestGoalDiffRow, 'goal_difference', 0), 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="ranking-box-style wow fadeInUp" data-wow-delay=".65s">
                            @if (filled($bestDefenseBadge['logo_url'] ?? null))
                                <img src="{{ $bestDefenseBadge['logo_url'] }}" alt="{{ $bestDefenseBadge['label'] }}" loading="lazy" decoding="async" width="72" height="72">
                            @else
                                <div class="lap-ranking-badge" aria-hidden="true">{{ $bestDefenseBadge['initials'] ?? 'KL' }}</div>
                            @endif
                            <p>Poin Hilang</p>
                            <span>Kebobolan terendah</span>
                            <h3>{{ data_get($bestDefenseRow, 'goals_against', 0) }}</h3>
                        </div>
                    </div>
                </div>
                @elseif ($standingsGroups->isNotEmpty())
                    <div class="standings-summary-head wow fadeInUp" data-wow-delay=".18s">
                        <div>
                            <h4>Daftar Klasemen per Usia</h4>
                            <p>Setiap tabel di bawah mewakili satu kelompok usia agar lebih mudah dibaca.</p>
                        </div>
                        <span class="standings-summary-chip">{{ $standingsGroups->count() }} kelompok usia</span>
                    </div>
                    <div class="standings-group-nav wow fadeInUp" data-wow-delay=".2s">
                        @foreach ($standingsGroups as $group)
                            @php
                                $groupId = $group['age_group']?->id ?? $loop->index;
                                $groupRows = collect($group['rows'] ?? []);
                            @endphp
                            <a href="#standings-age-{{ $groupId }}">
                                <span>{{ $group['age_group']?->name ?: 'Klasemen' }}</span>
                                <span>{{ $groupRows->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
                <div data-standings-table-container class="wow fadeInUp" data-wow-delay=".25s">
                    @include('public.partials.standings-table')
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const containerSelector = '[data-standings-table-container]';
            const formSelector = 'form[data-standings-filter-form]';

            const buildRequestUrl = (form) => {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                const fullParams = new URLSearchParams(params);

                params.set('partial', '1');
                url.search = params.toString();

                return { url, params, fullParams };
            };

            window.loadStandingsFilter = function (select) {
                const form = select?.closest?.(formSelector);

                if (form) {
                    loadStandings(form);
                }
            };

            async function loadStandings(form) {
                const container = document.querySelector(containerSelector);

                if (!container) {
                    form.submit();
                    return;
                }

                const { url, fullParams } = buildRequestUrl(form);
                const queryString = fullParams.toString();

                container.classList.add('is-loading');

                try {
                    const response = await fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load standings');
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nextContainer = doc.querySelector(containerSelector);

                    if (!nextContainer) {
                        throw new Error('Standings container missing');
                    }

                    container.innerHTML = nextContainer.innerHTML;
                    window.history.replaceState({}, '', `${form.action}?${queryString}`);
                } catch (error) {
                    window.location.href = `${form.action}?${queryString}`;
                } finally {
                    container.classList.remove('is-loading');
                }
            }

            document.addEventListener('submit', function (event) {
                const form = event.target.closest(formSelector);

                if (!form) {
                    return;
                }

                event.preventDefault();
                loadStandings(form);
            });
        })();
    </script>
@endpush

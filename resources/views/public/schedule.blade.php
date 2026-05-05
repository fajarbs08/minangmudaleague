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
            padding-bottom: 40px;
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
        .lap-public .latest-world-ranking-wrapper .filter-btn,
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
            line-height: 1.35;
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
            .lap-public .latest-world-ranking-section {
                padding-bottom: 24px;
            }

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

        .lap-public .latest-world-ranking-table tbody tr:hover td:last-child .lap-table-detail-link {
            transform: translateY(-1px) scale(1.08);
            color: #e41b23;
            box-shadow: 0 10px 18px rgba(228, 27, 35, .14);
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td:last-child .lap-table-detail-link i {
            transform: scale(1.08) rotate(-10deg);
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

        .lap-public .latest-world-ranking-table table .team img {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 50%;
            flex: 0 0 auto;
            border: 2px solid #fff;
            box-shadow: 0 3px 8px rgba(15, 23, 42, .08);
        }

        .lap-public .latest-world-ranking-table table .team .lap-schedule-club-mark {
            align-items: center;
            background: #ffffff;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 3px 8px rgba(15, 23, 42, .08);
            color: #030523;
            display: inline-flex;
            flex: 0 0 auto;
            font-family: 'Big Shoulders', sans-serif;
            font-size: 11px;
            font-weight: 800;
            height: 30px;
            justify-content: center;
            letter-spacing: .08em;
            line-height: 1;
            text-transform: uppercase;
            width: 30px;
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

        .lap-public .ranking-box-style img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            padding: 6px;
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
    </style>
@endpush

@php
    $pageMode = ($activePublicPage ?? 'schedule') === 'results' ? 'results' : 'schedule';
    $selectedPublicSeason = $selectedPublicSeason ?? null;
    $publicSeasonOptions = $publicSeasonOptions ?? collect();
    $publicSeasonQuery = $publicSeasonQuery ?? [];
    $isHistoricalPublicSeason = $isHistoricalPublicSeason ?? false;
    $pageTitle = $pageMode === 'results' ? 'Hasil Pertandingan' : 'Jadwal Pertandingan';
    $pageSummaryLabel = $pageMode === 'results' ? 'hasil pertandingan tersedia' : 'pertandingan terjadwal';
    $pageStatusLabel = $pageMode === 'results' ? 'Menunggu hasil' : 'Terjadwal';
    $pageEmptyState = $pageMode === 'results' ? 'Belum ada hasil pertandingan.' : 'Belum ada jadwal pertandingan.';
    $publicAsset = fn (string $path) => asset('public-assets/'.$path);
    $scheduleMatches = collect($pageMode === 'results' ? ($recentResults ?? []) : ($upcomingMatches ?? []));
    $featuredMatch = $scheduleMatches->first();
    $scheduleFilterOptions = ($pageMode === 'results' ? ($resultFilterOptions ?? null) : ($scheduleFilterOptions ?? null)) ?? [
        'age_groups' => collect(),
        'years' => collect(),
        'dates' => collect(),
        'clubs' => collect(),
    ];
    $scheduleFilters = ($pageMode === 'results' ? ($resultFilters ?? null) : ($scheduleFilters ?? null)) ?? [
        'age_group_id' => null,
        'year' => null,
        'date' => null,
        'club_id' => null,
    ];
    $scheduleFilterActionUrl = $scheduleFilterActionUrl ?? route('public.schedule');
    $scheduleCtaLabel = $scheduleCtaLabel ?? 'LIHAT HASIL PERTANDINGAN';
    $scheduleCtaUrl = $scheduleCtaUrl ?? route('public.results');
    $pageTitleAccentWord = str_contains(strtolower($pageTitle), 'hasil') ? 'Hasil' : 'Jadwal';
    $pageTitleMarkup = preg_replace(
        '/('.preg_quote($pageTitleAccentWord, '/').')/i',
        '<span class="lap-outline-word">$1</span>',
        e($pageTitle),
        1,
    ) ?? e($pageTitle);
    $clubLogoUrl = function ($club): ?string {
        if (! $club) {
            return null;
        }

        if (filled($club->logo_file_url)) {
            return $club->logo_file_url;
        }

        if (filled($club->logo_url)) {
            return str_starts_with($club->logo_url, 'http')
                ? $club->logo_url
                : url('/storage/'.ltrim($club->logo_url, '/'));
        }

        return null;
    };
@endphp

@section('content')
    <section class="latest-world-ranking-section section-padding wow fadeInUp" data-wow-delay=".15s">
        <div class="container">
            <div class="latest-world-ranking-wrapper">
                <div class="content">
                    <h3 class="lap-brand-heading">{!! $pageTitleMarkup !!}</h3>
                    @if ($pageMode === 'results')
                        <span class="visually-hidden">Daftar Hasil Pertandingan</span>
                        <span class="visually-hidden">Sorotan Utama</span>
                    @endif
                    <div class="text-item">
                        <p>{{ number_format($scheduleMatches->count()) }} {{ $pageSummaryLabel }}</p>
                        @if ($selectedPublicSeason)
                            <p>{{ $selectedPublicSeason->name }}{{ $isHistoricalPublicSeason ? ' · histori' : ' · aktif' }}</p>
                        @endif
                    </div>
                </div>

                <div class="latest-world-ranking-table wow fadeInUp" data-wow-delay=".25s">
                    <div class="ranking-category-items">
                        <form method="GET" action="{{ $scheduleFilterActionUrl }}">
                            @if ($pageMode === 'results')
                                <span class="visually-hidden">Kontrol pencarian hasil pertandingan</span>
                                <input type="hidden" name="competition_format" value="{{ $scheduleFilters['competition_format'] ?? '' }}">
                            @endif
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="row g-4">
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <div class="form-clt">
                                                <div class="form">
                                                    <select class="single-select w-100" name="season" onchange="this.form.submit()">
                                                        @foreach ($publicSeasonOptions as $season)
                                                            <option value="{{ $season->slug }}" @selected(($selectedPublicSeason?->id ?? 0) === $season->id)>{{ $season->name }}{{ $season->is_active ? ' • aktif' : '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <div class="form-clt">
                                                <div class="form">
                                                    <select class="single-select w-100" name="age_group_id" onchange="this.form.submit()">
                                                        <option value="">Semua kelompok usia</option>
                                                        @foreach ($scheduleFilterOptions['age_groups'] as $option)
                                                            <option value="{{ $option['value'] }}" @selected((string) data_get($scheduleFilters, 'age_group_id', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <div class="form-clt">
                                                <div class="form">
                                                    <select class="single-select w-100" name="year" onchange="this.form.submit()">
                                                        <option value="">Semua tahun</option>
                                                        @foreach ($scheduleFilterOptions['years'] as $option)
                                                            <option value="{{ $option['value'] }}" @selected((string) data_get($scheduleFilters, 'year', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <div class="form-clt">
                                                <div class="form">
                                                    <select class="single-select w-100" name="date" onchange="this.form.submit()">
                                                        <option value="">Semua tanggal</option>
                                                        @foreach ($scheduleFilterOptions['dates'] as $option)
                                                            <option value="{{ $option['value'] }}" @selected((string) data_get($scheduleFilters, 'date', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <div class="form-clt">
                                                <div class="form">
                                                    <select class="single-select w-100" name="club_id" onchange="this.form.submit()">
                                                        <option value="">Semua klub</option>
                                                        @foreach ($scheduleFilterOptions['clubs'] as $option)
                                                            <option value="{{ $option['value'] }}" @selected((string) data_get($scheduleFilters, 'club_id', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pertandingan</th>
                                    <th>Kelompok Usia</th>
                                    <th>Venue</th>
                                    <th>Status</th>
                                    <th>{{ $pageMode === 'results' ? 'Skor' : 'Kickoff' }}</th>
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($scheduleMatches as $match)
                                    <tr>
                                        <td>{{ optional($match->match_date)->translatedFormat('d M Y') ?: 'Tanggal menyusul' }}</td>
                                        <td>
                                            <div class="team">
                                                @include('public.partials.identity-mark', ['imageUrl' => $match->club_a_logo_file_url ?: $clubLogoUrl($match->clubA), 'label' => $match->club_a_display_name ?: 'Klub A', 'badgeClass' => 'lap-schedule-club-mark'])
                                                <span>{{ $match->club_a_short_name ?: $match->club_a_display_name ?: 'Klub A' }} vs {{ $match->club_b_short_name ?: $match->club_b_display_name ?: 'Klub B' }}</span>
                                                @include('public.partials.identity-mark', ['imageUrl' => $match->club_b_logo_file_url ?: $clubLogoUrl($match->clubB), 'label' => $match->club_b_display_name ?: 'Klub B', 'badgeClass' => 'lap-schedule-club-mark'])
                                                <span class="visually-hidden">{{ $match->club_a_display_name ?: 'Klub A' }} {{ $match->club_b_display_name ?: 'Klub B' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $match->ageGroup?->name ?: '-' }}</td>
                                        <td>{{ $match->venue ?: 'Venue menyusul' }}</td>
                                        <td class="{{ $match->is_finished ? 'positive' : 'negative' }}">{{ $match->is_finished ? 'Selesai' : ($pageMode === 'results' ? 'Belum selesai' : $pageStatusLabel) }}</td>
                                        <td>
                                            @if ($pageMode === 'results')
                                                @include('public.partials.match-score', ['homeScore' => $match->score_club_a, 'awayScore' => $match->score_club_b, 'separator' => ' - '])
                                            @else
                                                {{ optional($match->kickoff_time)->format('H:i') ? optional($match->kickoff_time)->format('H:i').' WIB' : 'TBD' }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @include('public.partials.table-detail-link', [
                                                'href' => $pageMode === 'results' ? route('public.results.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery) : route('public.schedule.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery),
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="competition-table-empty">{{ $pageEmptyState }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

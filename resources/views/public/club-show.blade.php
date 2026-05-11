@extends('public.public-layout')

@php
    use Illuminate\Support\Str;

    $selectedPublicSeason = $selectedPublicSeason ?? null;
    $publicSeasonQuery = $publicSeasonQuery ?? [];
    $isHistoricalPublicSeason = $isHistoricalPublicSeason ?? false;
    $clubMark = Str::upper(Str::substr($club->short_name ?: $club->name, 0, 2));
    $clubPlayerCount = $clubPlayers->count();
    $clubOfficialCount = $clubOfficials->count();
    $clubMatchCount = $clubRecentMatches->count();
    $clubAgeGroups = $clubPlayers->pluck('primaryAgeGroup.name')->filter()->unique()->values();
    $clubAgeGroupCount = $clubAgeGroups->count();
    $heroSummary = $club->notes ?: 'Profil resmi klub beserta pemain terverifikasi, ofisial aktif, dan pertandingan terbaru.';
    $managerSummary = collect([$club->manager_name, $club->manager_title])->filter()->implode(' · ');
    $matchDetailUrl = static function ($match) use ($publicSeasonQuery) {
        return $match->is_finished
            ? route('public.results.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery)
            : route('public.schedule.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery);
    };
    $matchStatusLabel = static fn ($match) => $match->is_finished ? 'Selesai' : 'Terjadwal';
    $clubMetaItems = collect([
        $selectedPublicSeason?->name,
        $managerSummary ?: null,
        $club->zone ? 'Zona '.$club->zone : null,
        $club->founded_year ? 'Berdiri '.$club->founded_year : null,
    ])->filter()->values();
    $clubStatCards = [
        ['label' => 'Pemain', 'value' => $clubPlayerCount, 'suffix' => 'terverifikasi'],
        ['label' => 'Ofisial', 'value' => $clubOfficialCount, 'suffix' => 'aktif'],
        ['label' => 'Laga', 'value' => $clubMatchCount, 'suffix' => 'tercatat'],
        ['label' => 'Kategori', 'value' => $clubAgeGroupCount ?: '-', 'suffix' => 'aktif'],
    ];
    $clubFacts = [
        ['label' => 'Manajer', 'value' => $club->manager_name ?: 'Belum diisi'],
        ['label' => 'Jabatan', 'value' => $club->manager_title ?: 'Belum diisi'],
        ['label' => 'Zona', 'value' => $club->zone ?: 'Belum diisi'],
        ['label' => 'Training base', 'value' => $club->training_address ?: 'Belum diisi'],
        ['label' => 'Alamat', 'value' => $club->address ?: 'Belum diisi'],
        ['label' => 'Tahun berdiri', 'value' => $club->founded_year ?: 'Belum diisi'],
        ['label' => 'Status publik', 'value' => 'Terverifikasi'],
        ['label' => 'Season', 'value' => $selectedPublicSeason?->name ?: 'Musim aktif'],
        ['label' => 'Kategori aktif', 'value' => $clubAgeGroupCount > 0 ? $clubAgeGroupCount.' kelompok usia' : 'Belum tersedia'],
    ];
    $clubLinks = [
        ['label' => 'Kembali ke daftar klub', 'url' => route('public.clubs', $publicSeasonQuery)],
        ['label' => 'Lihat jadwal pertandingan', 'url' => route('public.schedule')],
        ['label' => 'Buka hasil pertandingan', 'url' => route('public.results')],
        ['label' => 'Lihat klasemen liga', 'url' => route('public.standings')],
    ];
@endphp

@if (filled($club->logo_file_url))
    @push('headLinks')
        <link rel="preload" as="image" href="{{ $club->logo_file_url }}">
    @endpush
@endif

@push('styles')
    <style>
        .lap-club-detail-section {
            background: #ffffff;
            padding: 80px 0 96px;
            color: #030523;
        }

        .lap-club-detail-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-club-detail-section .container {
                max-width: 1680px;
            }
        }

        .lap-club-shell,
        .lap-club-main-column,
        .lap-club-aside {
            display: grid;
            gap: 24px;
        }

        .lap-club-title,
        .lap-club-subtitle,
        .lap-club-panel h3,
        .lap-club-aside-card h4,
        .lap-club-media-name,
        .lap-club-stat-card strong,
        .lap-club-person-name,
        .lap-club-match-score,
        .lap-club-badge-mark,
        .lap-club-empty h4 {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-club-lead,
        .lap-club-meta,
        .lap-club-panel p,
        .lap-club-fact-value,
        .lap-club-person-sub,
        .lap-club-aside-copy,
        .lap-club-empty-copy,
        .lap-club-media-note,
        .lap-club-match-meta,
        .lap-club-link-item span {
            color: #667085;
        }

        .lap-club-hero-card {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 32px;
            padding: 32px;
            border: 1px solid #e7e9f0;
            border-radius: 28px;
            background: linear-gradient(145deg, #ffffff 0%, #f7f9fc 100%);
            box-shadow: 0 28px 72px rgba(3, 5, 35, 0.08);
            align-items: center;
        }

        .lap-club-media-panel {
            position: relative;
            overflow: hidden;
            min-height: 430px;
            padding: 32px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
        }

        .lap-club-media-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top, rgba(255, 255, 255, 0.12), transparent 55%);
        }

        .lap-club-media-core {
            position: relative;
            z-index: 1;
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 24px;
            min-height: 100%;
            padding-bottom: 72px;
            text-align: center;
        }

        .lap-club-logo-stage {
            width: min(250px, 72%);
            aspect-ratio: 1 / 1;
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
            display: grid;
            place-items: center;
            box-shadow: 0 24px 52px rgba(3, 5, 35, 0.22);
        }

        .lap-club-logo-stage img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 24px;
        }

        .lap-club-logo-mark,
        .lap-club-avatar-mark,
        .lap-club-team-logo-mark {
            display: grid;
            place-items: center;
            width: 100%;
            height: 100%;
            background: #eef4ff;
            color: #0d2f67;
            font-family: 'Big Shoulders', sans-serif;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-club-logo-mark {
            font-size: clamp(3rem, 8vw, 4.5rem);
        }

        .lap-club-media-name {
            margin: 0;
            color: #ffffff;
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1;
        }

        .lap-club-media-note {
            margin: 8px 0 0;
            max-width: 30ch;
            font-family: 'Chakra Petch', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            line-height: 1.6;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.82);
        }

        .lap-club-media-tags,
        .lap-club-chip-list,
        .lap-club-meta,
        .lap-club-meta-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .lap-club-actions {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .lap-club-media-tags {
            justify-content: center;
        }

        .lap-club-chip,
        .lap-club-meta-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: .35rem .8rem;
            border: 1px solid #e3e9f4;
            border-radius: 999px;
            background: #fff;
            color: #0d2f67;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .lap-club-meta-tag.is-light {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        .lap-club-chip.is-accent,
        .lap-club-btn.is-primary {
            background: #e41b23;
            border-color: #e41b23;
            color: #ffffff;
        }

        .lap-club-media-badge {
            position: absolute;
            left: 18px;
            bottom: 18px;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.94);
            color: #030523;
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .lap-club-summary {
            display: grid;
            gap: 18px;
        }

        .lap-club-headline {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: start;
        }

        .lap-club-title {
            margin: 0;
            font-size: 3rem;
            font-weight: 700;
            line-height: .94;
        }

        .lap-club-subtitle {
            margin-top: 8px;
            font-size: 1.35rem;
            font-weight: 700;
            color: #e41b23;
        }

        .lap-club-badge-mark {
            flex-shrink: 0;
            font-size: clamp(3.2rem, 9vw, 6rem);
            font-weight: 700;
            line-height: .85;
            color: rgba(3, 5, 35, 0.1);
        }

        .lap-club-meta {
            gap: 10px 16px;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .lap-club-lead {
            margin: 0;
            max-width: 70ch;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.7;
        }

        .lap-club-stat-grid,
        .lap-club-facts,
        .lap-club-content-grid {
            display: grid;
            gap: 14px;
        }

        .lap-club-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .lap-club-stat-card,
        .lap-club-panel,
        .lap-club-aside-card,
        .lap-club-list,
        .lap-club-link-list {
            border: 1px solid #e7e9f0;
            border-radius: 22px;
            background: #ffffff;
        }

        .lap-club-stat-card {
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
        }

        .lap-club-stat-card span,
        .lap-club-fact-label,
        .lap-club-match-meta span,
        .lap-club-aside-kicker {
            display: block;
            color: #667085;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-club-stat-card strong {
            display: block;
            margin-top: 8px;
            font-size: 2rem;
            font-weight: 700;
            line-height: .9;
        }

        .lap-club-content-grid {
            grid-template-columns: minmax(0, 1.55fr) minmax(300px, .82fr);
            align-items: start;
            gap: 28px;
        }

        .lap-club-panel,
        .lap-club-aside-card {
            padding: 28px;
            box-shadow: 0 20px 56px rgba(3, 5, 35, 0.05);
        }

        .lap-club-panel h3,
        .lap-club-aside-card h4 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 700;
        }

        .lap-club-panel p,
        .lap-club-aside-copy {
            margin: 10px 0 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: .98rem;
            font-weight: 600;
            line-height: 1.7;
        }

        .lap-club-facts {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 20px;
        }

        .lap-club-fact {
            padding: 16px 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #edf1f7;
        }

        .lap-club-fact-value {
            margin-top: 8px;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .lap-club-list,
        .lap-club-link-list {
            overflow: hidden;
        }

        .lap-club-link-item,
        .lap-club-person-row,
        .lap-club-match-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f7;
            transition: background-color .18s ease, transform .18s ease;
        }

        .lap-club-link-list > :last-child,
        .lap-club-list > :last-child {
            border-bottom: 0;
        }

        .lap-club-link-item {
            justify-content: space-between;
            text-decoration: none;
        }

        .lap-club-link-item:hover,
        .lap-club-link-item:focus-visible,
        .lap-club-person-row:hover,
        .lap-club-match-row:hover {
            background: #fcfdff;
            outline: none;
        }

        .lap-club-avatar {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid #e3e9f4;
            overflow: hidden;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .lap-club-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .lap-club-avatar-mark,
        .lap-club-team-logo-mark {
            font-size: 18px;
        }

        .lap-club-person-main,
        .lap-club-match-main {
            min-width: 0;
            flex: 1 1 auto;
            display: grid;
            gap: 8px;
        }

        .lap-club-person-head {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 12px;
            align-items: center;
        }

        .lap-club-person-name {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .lap-club-person-sub {
            margin: 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.65;
        }

        .lap-club-row-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 999px;
            background: linear-gradient(180deg, #ffffff 0%, #f2f6ff 100%);
            border: 1px solid rgba(3, 5, 35, 0.08);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
            color: #030523;
            transition: transform .18s ease, box-shadow .18s ease, color .18s ease, background-color .18s ease;
            flex: 0 0 auto;
        }

        .lap-club-row-link:hover,
        .lap-club-row-link:focus-visible {
            color: #e41b23;
            transform: translateY(-1px) scale(1.06);
            background: linear-gradient(180deg, #ffffff 0%, #fff1f1 100%);
            box-shadow: 0 12px 20px rgba(228, 27, 35, .12);
            outline: none;
        }

        .lap-club-match-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
        }

        .lap-club-match-teams {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            gap: 14px;
            align-items: center;
        }

        .lap-club-team {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .lap-club-team.is-away {
            justify-content: flex-end;
            text-align: right;
        }

        .lap-club-team-logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e3e9f4;
            overflow: hidden;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .lap-club-team-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }

        .lap-club-team-name {
            min-width: 0;
            color: #030523;
            font-size: .98rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .lap-club-match-score {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: .48rem .85rem;
            border-radius: 999px;
            background: #0d2f67;
            color: #fff;
            font-size: .92rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-club-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 48px;
            padding: .8rem 1rem;
            border-radius: 999px;
            border: 1px solid #d8deea;
            background: #fff;
            color: #030523;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            transition: .2s ease;
        }

        .lap-club-btn:hover {
            transform: translateY(-1px);
            color: #030523;
        }

        .lap-club-btn.is-primary:hover {
            color: #fff;
        }

        .lap-club-empty {
            padding: 20px 22px;
            border: 1px dashed #d8deea;
            border-radius: 22px;
            background: #f8fafc;
        }

        .lap-club-empty h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .lap-club-empty-copy {
            margin: 10px 0 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.7;
        }

        @media (max-width: 1199px) {
            .lap-club-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .lap-club-hero-card,
            .lap-club-content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .lap-club-detail-section {
                padding: 64px 0 72px;
            }

            .lap-club-hero-card,
            .lap-club-panel,
            .lap-club-aside-card {
                padding: 22px;
            }

            .lap-club-headline {
                flex-direction: column;
            }

            .lap-club-badge-mark {
                font-size: 4.2rem;
            }

            .lap-club-title {
                font-size: 2.3rem;
            }

            .lap-club-media-panel {
                min-height: 340px;
                padding: 24px;
            }

            .lap-club-media-core {
                padding-bottom: 84px;
            }

            .lap-club-logo-stage {
                width: min(220px, 74%);
            }

            .lap-club-stat-grid,
            .lap-club-facts,
            .lap-club-match-teams {
                grid-template-columns: 1fr;
            }

            .lap-club-link-item,
            .lap-club-person-row,
            .lap-club-match-row {
                align-items: flex-start;
            }

            .lap-club-team,
            .lap-club-team.is-away {
                justify-content: flex-start;
                text-align: left;
            }

            .lap-club-row-link {
                width: 42px;
                height: 42px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="lap-club-detail-section">
        <div class="container">
            <div class="lap-club-shell">
                <article class="lap-club-hero-card">
                    <div class="lap-club-media-panel">
                        <div class="lap-club-media-core">
                            <div class="lap-club-logo-stage">
                                @if ($club->logo_file_url)
                                    <img src="{{ $club->logo_file_url }}" alt="{{ $club->name }}" decoding="async" fetchpriority="high" width="250" height="250">
                                @else
                                    <span class="lap-club-logo-mark">{{ $clubMark }}</span>
                                @endif
                            </div>

                            <div>
                                <h3 class="lap-club-media-name">{{ $club->short_name ?: $club->name }}</h3>
                                <p class="lap-club-media-note">{{ $managerSummary ?: 'Manajer dan jabatan belum diisi' }}</p>
                            </div>

                            <div class="lap-club-media-tags">
                                @foreach ($clubAgeGroups as $ageGroupName)
                                    <span class="lap-club-meta-tag is-light">{{ $ageGroupName }}</span>
                                @endforeach

                                @if ($clubAgeGroups->isEmpty())
                                    <span class="lap-club-meta-tag is-light">Kelompok usia belum tersedia</span>
                                @endif
                            </div>
                        </div>

                        <span class="lap-club-media-badge">
                            <i class="fa-solid fa-circle-check"></i>
                            Profil publik
                        </span>
                    </div>

                    <div class="lap-club-summary">
                        <span class="lap-section-kicker">Profil Klub</span>

                        <div class="lap-club-headline">
                            <div>
                                <h2 class="lap-club-title">{{ $club->name }}</h2>
                                <div class="lap-club-subtitle">{{ $club->short_name ?: 'Klub Peserta' }}</div>
                            </div>
                            <div class="lap-club-badge-mark">{{ $clubMark }}</div>
                        </div>

                        <div class="lap-club-meta">
                            @forelse ($clubMetaItems as $metaItem)
                                <span>{{ $metaItem }}</span>
                            @empty
                                <span>Profil klub publik</span>
                            @endforelse
                        </div>

                        <p class="lap-club-lead">{{ $heroSummary }}</p>

                        <div class="lap-club-chip-list">
                            <span class="lap-club-chip is-accent">Terverifikasi</span>
                            @if ($selectedPublicSeason)
                                <span class="lap-club-chip">{{ $selectedPublicSeason->name }}{{ $isHistoricalPublicSeason ? ' · histori' : '' }}</span>
                            @endif
                            <span class="lap-club-chip">{{ strtoupper($club->zone ?: 'Zona TBD') }}</span>
                            <span class="lap-club-chip">{{ $clubAgeGroupCount > 0 ? $clubAgeGroupCount.' kategori aktif' : 'Kategori TBD' }}</span>
                            <span class="lap-club-chip">{{ $clubMatchCount }} laga tercatat</span>
                        </div>

                        <div class="lap-club-stat-grid">
                            @foreach ($clubStatCards as $stat)
                                <div class="lap-club-stat-card">
                                    <span>{{ $stat['label'] }}</span>
                                    <strong>{{ $stat['value'] }}</strong>
                                    <span>{{ $stat['suffix'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <div class="lap-club-content-grid">
                    <div class="lap-club-main-column">
                        <section class="lap-club-panel">
                            <span class="lap-section-kicker">Informasi Klub</span>
                            <h3>Identitas dan arah kompetisi</h3>
                            <p>Informasi inti klub ditampilkan dalam format panel yang lebih rapi agar ritme visualnya sejalan dengan halaman detail publik lain di portal ini.</p>

                            <div class="lap-club-facts">
                                @foreach ($clubFacts as $fact)
                                    <div class="lap-club-fact">
                                        <span class="lap-club-fact-label">{{ $fact['label'] }}</span>
                                        <div class="lap-club-fact-value">{{ $fact['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="lap-club-panel">
                            <span class="lap-section-kicker">Skuad</span>
                            <h3>Pemain terverifikasi</h3>
                            <p>Daftar pemain ditampilkan sebagai roster ringkas agar tetap mudah dipindai, tetapi sekarang mengikuti panel dan hierarki yang sama dengan detail pemain publik.</p>

                            @if ($clubPlayers->isNotEmpty())
                                <div class="lap-club-list">
                                    @foreach ($clubPlayers as $player)
                                        @php
                                            $playerMark = Str::upper(Str::substr($player->name, 0, 2));
                                        @endphp
                                        <article class="lap-club-person-row">
                                            <div class="lap-club-avatar">
                                                @if ($player->photo_file_url)
                                                    <img src="{{ $player->photo_file_url }}" alt="{{ $player->name }}" loading="lazy" decoding="async" width="56" height="56">
                                                @else
                                                    <span class="lap-club-avatar-mark">{{ $playerMark }}</span>
                                                @endif
                                            </div>

                                            <div class="lap-club-person-main">
                                                <div class="lap-club-person-head">
                                                    <h4 class="lap-club-person-name">{{ $player->name }}</h4>
                                                    @if ($player->is_captain)
                                                        <span class="lap-club-meta-tag">Kapten</span>
                                                    @endif
                                                </div>

                                                <div class="lap-club-meta-tags">
                                                    <span class="lap-club-meta-tag">{{ $player->displayPosition($player->primary_age_group_id) ?: 'Pemain' }}</span>
                                                    <span class="lap-club-meta-tag">#{{ $player->displayJerseyNumber($player->primary_age_group_id) ?: '-' }}</span>
                                                    <span class="lap-club-meta-tag">{{ $player->primaryAgeGroup?->name ?: 'Kelompok usia TBD' }}</span>
                                                </div>

                                                <p class="lap-club-person-sub">
                                                    {{ $player->birth_date ? $player->birth_date->translatedFormat('d F Y') : 'Tanggal lahir belum tersedia' }}
                                                </p>
                                            </div>

                                            <a href="{{ route('public.players.show', ['playerSlug' => $player->public_slug] + $publicSeasonQuery) }}" class="lap-club-row-link" aria-label="Buka profil {{ $player->name }}">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="lap-club-empty">
                                    <h4>Pemain belum tersedia</h4>
                                    <p class="lap-club-empty-copy">Belum ada pemain terverifikasi yang bisa ditampilkan untuk klub ini.</p>
                                </div>
                            @endif
                        </section>

                        <section class="lap-club-panel">
                            <span class="lap-section-kicker">Ofisial</span>
                            <h3>Ofisial aktif klub</h3>
                            <p>Ofisial ditampilkan dengan struktur yang sama agar ritme visual tetap konsisten dan mudah diikuti saat dibuka dari desktop maupun mobile.</p>

                            @if ($clubOfficials->isNotEmpty())
                                <div class="lap-club-list">
                                    @foreach ($clubOfficials as $official)
                                        @php
                                            $officialMark = Str::upper(Str::substr($official->name, 0, 2));
                                        @endphp
                                        <article class="lap-club-person-row">
                                            <div class="lap-club-avatar">
                                                @if ($official->photo_file_url)
                                                    <img src="{{ $official->photo_file_url }}" alt="{{ $official->name }}" loading="lazy" decoding="async" width="56" height="56">
                                                @else
                                                    <span class="lap-club-avatar-mark">{{ $officialMark }}</span>
                                                @endif
                                            </div>

                                            <div class="lap-club-person-main">
                                                <div class="lap-club-person-head">
                                                    <h4 class="lap-club-person-name">{{ $official->name }}</h4>
                                                </div>

                                                <div class="lap-club-meta-tags">
                                                    <span class="lap-club-meta-tag">{{ $official->role ?: 'Ofisial' }}</span>
                                                    <span class="lap-club-meta-tag">{{ $official->ageGroup?->name ?: 'Kategori TBD' }}</span>
                                                </div>

                                                <p class="lap-club-person-sub">{{ $official->citizenship ?: 'Kewarganegaraan belum diisi' }}</p>
                                            </div>

                                            <a href="{{ route('public.officials.show', ['officialSlug' => $official->public_slug] + $publicSeasonQuery) }}" class="lap-club-row-link" aria-label="Buka profil {{ $official->name }}">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="lap-club-empty">
                                    <h4>Ofisial belum tersedia</h4>
                                    <p class="lap-club-empty-copy">Belum ada ofisial aktif yang ditampilkan untuk klub ini.</p>
                                </div>
                            @endif
                        </section>
                    </div>

                    <aside class="lap-club-aside">
                        <section class="lap-club-aside-card">
                            <span class="lap-club-aside-kicker">Navigasi</span>
                            <h4>Jelajahi Klub</h4>
                            <p class="lap-club-aside-copy">Gunakan pintasan ini untuk lanjut ke daftar klub, jadwal, hasil, atau klasemen tanpa kembali ke beranda.</p>

                            <div class="lap-club-link-list">
                                @foreach ($clubLinks as $link)
                                    <a href="{{ $link['url'] }}" class="lap-club-link-item">
                                        <span>{{ $link['label'] }}</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                @endforeach
                            </div>
                        </section>

                        <section class="lap-club-aside-card">
                            <span class="lap-club-aside-kicker">Pertandingan</span>
                            <h4>Laga terakhir dan terdekat</h4>
                            <p class="lap-club-aside-copy">Riwayat pertandingan klub tetap ditampilkan di halaman yang sama, tetapi kini memakai kartu samping yang lebih seragam dengan detail publik lainnya.</p>

                            @if ($clubRecentMatches->isNotEmpty())
                                <div class="lap-club-list">
                                    @foreach ($clubRecentMatches as $match)
                                        <article class="lap-club-match-row">
                                            <div class="lap-club-match-main">
                                                <div class="lap-club-match-meta">
                                                    <span>{{ strtoupper($match->ageGroup?->name ?: 'Umum') }}</span>
                                                    <span>{{ $matchStatusLabel($match) }}</span>
                                                    <span>{{ optional($match->match_date)->translatedFormat('d F Y') ?: 'Tanggal TBD' }}</span>
                                                    <span>{{ $match->kickoff_time ? $match->kickoff_time->format('H:i').' WIB' : 'Kickoff TBD' }}</span>
                                                </div>

                                                <div class="lap-club-match-teams">
                                                    <div class="lap-club-team">
                                                        <span class="lap-club-team-logo">
                                                            @if ($match->club_a_logo_file_url)
                                                                <img src="{{ $match->club_a_logo_file_url }}" alt="{{ $match->club_a_display_name ?: 'Klub A' }}" loading="lazy" decoding="async" width="48" height="48">
                                                            @else
                                                                <span class="lap-club-team-logo-mark">{{ Str::upper(Str::substr($match->club_a_display_name ?: 'A', 0, 2)) }}</span>
                                                            @endif
                                                        </span>
                                                        <strong class="lap-club-team-name">{{ $match->club_a_display_name ?: 'Klub A' }}</strong>
                                                    </div>

                                                    <div class="lap-club-match-score">
                                                        @if ($match->is_finished)
                                                            {{ $match->score_club_a ?? 0 }} - {{ $match->score_club_b ?? 0 }}
                                                        @else
                                                            VS
                                                        @endif
                                                    </div>

                                                    <div class="lap-club-team is-away">
                                                        <strong class="lap-club-team-name">{{ $match->club_b_display_name ?: 'Klub B' }}</strong>
                                                        <span class="lap-club-team-logo">
                                                            @if ($match->club_b_logo_file_url)
                                                                <img src="{{ $match->club_b_logo_file_url }}" alt="{{ $match->club_b_display_name ?: 'Klub B' }}" loading="lazy" decoding="async" width="48" height="48">
                                                            @else
                                                                <span class="lap-club-team-logo-mark">{{ Str::upper(Str::substr($match->club_b_display_name ?: 'B', 0, 2)) }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="{{ $matchDetailUrl($match) }}" class="lap-club-row-link" aria-label="Buka detail pertandingan {{ $match->club_a_display_name }} melawan {{ $match->club_b_display_name }}">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="lap-club-empty">
                                    <h4>Belum ada pertandingan tercatat</h4>
                                    <p class="lap-club-empty-copy">Riwayat pertandingan klub akan tampil setelah jadwal atau hasil resmi tersedia.</p>
                                </div>
                            @endif

                            <div class="lap-club-actions">
                                <a href="{{ route('public.results') }}" class="lap-club-btn is-primary">
                                    <i class="fa-solid fa-trophy"></i>
                                    Semua Hasil
                                </a>
                                <a href="{{ route('public.schedule') }}" class="lap-club-btn">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    Semua Jadwal
                                </a>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection

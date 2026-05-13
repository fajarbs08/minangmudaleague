@extends('public.public-layout')

@php
    use Illuminate\Support\Str;

    $publicAsset = fn (string $path) => asset('public-assets/'.$path);
    $heroMatch = $headlineMatch ?: $upcomingMatches->first() ?: $recentResults->first();
    $lastMatch = $recentResults->first();
    $nextMatch = $upcomingMatches->first();
    $upcomingSpotlight = $upcomingMatches->skip(1)->first() ?: $upcomingMatches->first();
    $tickerMatches = $recentResults->take(6);
    $clubStories = $featuredClubs->take(2);
    $playerHighlights = $featuredPlayers->take(3);
    $teamPlayers = $featuredPlayers->take(6)->values();
    $standingsBlocks = $publicStandings->take(2);
    $homeSponsors = collect($featuredSponsors)->take(13)->values();
    $heroBgUrl = $publicAsset('img/home-1/hero-bg.jpg');
    $clubBlogBgUrl = $publicAsset('img/home-1/club-blog/club-blog-bg.jpg');
    $rankingBgUrl = $publicAsset('img/home-1/ranking/ranking-bg.jpg');
    $sponsorBgUrl = $publicAsset('img/home-1/sponsor-bg.jpg');
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
    $recentMatchHighlights = $recentResults->take(3)->map(function ($match) use ($clubLogoUrl) {
            return [
                'type' => 'result',
                'badge' => 'Hasil laga',
                'title' => $match->score_label,
                'summary' => trim(($match->clubA?->short_name ?: $match->clubA?->name ?: 'Klub A').' vs '.($match->clubB?->short_name ?: $match->clubB?->name ?: 'Klub B')),
                'meta' => trim((optional($match->match_date)->translatedFormat('d F Y') ?: 'Tanggal menyusul').' / '.($match->ageGroup?->name ?: 'Kompetisi')),
                'sort_at' => $match->updated_at ?: ($match->match_date ?? now()),
                'url' => route('public.results.show', ['matchSlug' => $match->public_slug]),
                'image' => $clubLogoUrl($match->clubA),
                'alt' => $match->clubA?->name ?: 'Hasil laga',
            ];
        })->sortByDesc(fn (array $item) => $item['sort_at']?->timestamp ?? 0)->values();

    $latestWinMatch = $recentResults->first();
    $topScorer = $recentResults
        ->flatMap(fn ($match) => $match->goalEvents->filter(fn ($goal) => filled($goal->scorer?->name))->map(fn ($goal) => [
            'name' => $goal->scorer->name,
            'club' => $goal->club?->short_name ?: $goal->club?->name ?: 'Klub',
            'age_group' => $goal->scorer->primaryAgeGroup?->name ?: ($match->ageGroup?->name ?: 'Kompetisi'),
            'count' => 1,
        ]))
        ->groupBy('name')
        ->map(function ($rows) {
            $first = $rows->first();

            return [
                'name' => $first['name'],
                'club' => $first['club'],
                'age_group' => $first['age_group'],
                'count' => $rows->count(),
            ];
        })
        ->sortByDesc('count')
        ->first();

    $topAssist = $recentResults
        ->flatMap(fn ($match) => $match->goalEvents->filter(fn ($goal) => filled($goal->assistPlayer?->name))->map(fn ($goal) => [
            'name' => $goal->assistPlayer->name,
            'club' => $goal->club?->short_name ?: $goal->club?->name ?: 'Klub',
            'age_group' => $goal->assistPlayer->primaryAgeGroup?->name ?: ($match->ageGroup?->name ?: 'Kompetisi'),
            'count' => 1,
        ]))
        ->groupBy('name')
        ->map(function ($rows) {
            $first = $rows->first();

            return [
                'name' => $first['name'],
                'club' => $first['club'],
                'age_group' => $first['age_group'],
                'count' => $rows->count(),
            ];
        })
        ->sortByDesc('count')
        ->first();

    $topStanding = $standingsBlocks->first();
@endphp

@push('headLinks')
    <link rel="preload" as="image" href="{{ $heroBgUrl }}">
    <link rel="preload" as="image" href="{{ $publicAsset('img/home-1/hero1.png') }}">
@endpush

@push('styles')
    <style>
        .lap-home-template .flag-item img,
        .lap-home-template .match-left img,
        .lap-home-template .club-logo-badge img,
        .lap-home-template .match-thumb-list img,
        .lap-home-template .sponsor-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .lap-home-template .flag-item img,
        .lap-home-template .match-left img,
        .lap-home-template .match-thumb-list img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            padding: 6px;
        }

        .lap-home-template .flag-item .lap-home-club-mark,
        .lap-home-template .match-left .lap-home-club-mark,
        .lap-home-template .match-result .thumb .lap-home-club-mark,
        .lap-home-template .club-blog-items .thumb .lap-home-story-badge {
            align-items: center;
            background: #fff;
            color: #10131f;
            display: inline-flex;
            flex: 0 0 auto;
            font-family: 'Big Shoulders', sans-serif;
            font-weight: 800;
            justify-content: center;
            letter-spacing: .08em;
            line-height: 1;
            text-transform: uppercase;
        }

        .lap-home-template .flag-item .lap-home-club-mark,
        .lap-home-template .match-left .lap-home-club-mark {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            box-shadow: 0 3px 8px rgba(15, 23, 42, .08);
            font-size: 12px;
        }

        .lap-home-template .club-logo-badge,
        .lap-home-template .sponsor-img {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lap-home-template .club-logo-badge {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #fff;
            padding: 10px;
            flex: 0 0 54px;
        }

        .lap-home-template .sponsor-img {
            min-height: 120px;
            padding: 24px;
        }

        .lap-home-template .sponsor-img.is-empty {
            opacity: .18;
        }

        .lap-home-template .sponsor-img img {
            max-width: 140px;
            max-height: 54px;
        }

        .lap-home-template .match-result-section {
            background: #fff;
            padding-top: 0;
            padding-bottom: 0;
        }

        .lap-home-template .match-result-wrapper {
            background: #fff;
        }

        .lap-home-template .match-result .thumb img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            padding: 4px;
        }

        .lap-home-template .match-result .thumb .lap-home-club-mark {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .12);
            font-size: 16px;
        }

        .lap-home-template .match-section {
            background: #fff;
        }

        .lap-home-template .match-section .match-box-items {
            min-height: 100%;
            padding: clamp(34px, 4vw, 44px) clamp(24px, 3vw, 30px) 32px;
            background: #f7f7f7;
            box-shadow: none;
        }

        .lap-home-template .match-section .match-box-items h3 {
            font-size: clamp(2rem, 2.8vw, 2.9rem);
            line-height: .95;
            letter-spacing: .02em;
        }

        .lap-home-template .match-section .match-box-items h4 {
            color: #10131f;
        }

        .lap-home-template .match-section .match-box-items p {
            color: #4f5868;
        }

        .lap-home-template .match-section .match-schedule {
            margin-top: 28px;
        }

        .lap-home-template .match-section .match-btn {
            margin-top: 34px;
            display: flex;
            gap: 14px;
        }

        .lap-home-template .match-section .match-btn .theme-btn {
            min-width: 0;
            flex: 1 1 0;
            justify-content: center;
        }

        .lap-home-template .match-section .match-btn .theme-btn.style-2 {
            background: transparent;
            color: #10131f;
        }

        .lap-home-template .match-section .match-btn .theme-btn.style-2:hover {
            color: #fff;
        }

        .lap-home-template .match-section .lap-empty-state.is-light,
        .lap-home-template .match-section .lap-empty-state {
            min-height: 220px;
            display: grid;
            place-items: center;
        }

        .lap-home-template .ranking-section {
            padding-bottom: clamp(32px, 3vw, 52px);
        }

        .lap-home-template .news-section,
        .lap-home-template .about-section,
        .lap-home-template .team-section,
        .lap-home-template .sponsor-section {
            content-visibility: auto;
            contain-intrinsic-size: 1px 960px;
        }

        .lap-home-template .about-section {
            background: #05072a;
        }

        .lap-home-template .about-section .about-wrapper {
            position: relative;
            z-index: 1;
        }

        @media (max-width: 1399px) {
            .lap-home-template .sponsor-section .table-responsive {
                overflow: hidden;
            }

            .lap-home-template .sponsor-section .sponsor-wrap {
                width: 100% !important;
            }

            .lap-home-template .sponsor-section .sponsor-box-items {
                flex-wrap: wrap;
            }

            .lap-home-template .sponsor-section .sponsor-box-items .sponsor-img {
                width: 50%;
                max-width: 50%;
            }
        }

        .lap-home-template .our-club-payer-item .player-image > img {
            width: 100%;
            height: 560px;
            object-fit: cover;
        }

        .lap-home-template .our-club-payer-item .player-image .player-avatar-fallback {
            width: 100%;
            height: 560px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            color: rgba(255, 255, 255, 0.88);
        }

        .lap-home-template .our-club-payer-item .player-image .player-avatar-fallback i {
            font-size: clamp(3.5rem, 7vw, 5.5rem);
        }

        .lap-home-template .our-club-payer-item .player-content h4,
        .lap-home-template .our-club-payer-item .player-content p,
        .lap-home-template .our-club-payer-item .content-item .content h6,
        .lap-home-template .our-club-payer-item .content-item .content h5,
        .lap-home-template .our-club-payer-item .content-item .content span {
            text-transform: none;
        }

        .lap-home-template .lap-home-team-empty {
            margin: 0 auto;
            max-width: 1320px;
            padding: 0 15px;
        }

        .lap-home-template .news-box-items .content h3,
        .lap-home-template .club-blog-items .content h3,
        .lap-home-template .news-thumb1 .news-content h3 {
            text-transform: none;
        }

        .lap-home-template .news-thumb1 .news-content p,
        .lap-home-template .news-box-items .content p {
            color: rgba(16, 19, 31, .66);
            margin-top: 8px;
            margin-bottom: 0;
        }

        .lap-home-template .news-thumb1 .player-avatar-fallback,
        .lap-home-template .news-box-items .player-avatar-fallback {
            min-height: 260px;
            height: 100%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #10131f 0%, #1a2242 100%);
            color: rgba(255, 255, 255, .88);
        }

        .lap-home-template .news-box-items .player-avatar-fallback {
            min-height: 210px;
        }

        .lap-home-template .club-blog-items .thumb .lap-home-story-logo {
            width: 100%;
            min-height: 260px;
            display: block;
            object-fit: contain;
            padding: 32px;
            background: linear-gradient(135deg, #10131f 0%, #1a2242 100%);
        }

        .lap-home-template .club-blog-items .thumb .lap-home-story-badge {
            width: 100%;
            min-height: 260px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #10131f 0%, #1a2242 100%);
            color: rgba(255, 255, 255, .92);
            font-size: clamp(2.5rem, 5vw, 4rem);
        }

        .lap-home-template .news-section {
            background: #fff;
        }

        .lap-home-template .sponsor-section {
            margin-top: clamp(40px, 4vw, 72px);
        }

        .lap-home-template .match-result-item p,
        .lap-home-template .match-box-items p,
        .lap-home-template .playing-result-box2 .content p,
        .lap-home-template .section-title h6 {
            text-transform: uppercase;
        }

        .lap-home-template .lap-empty-state {
            padding: 32px;
            border: 1px dashed rgba(255,255,255,.18);
            color: rgba(255,255,255,.72);
            text-align: center;
        }

        .lap-home-template .lap-empty-state.is-light {
            border-color: rgba(16, 19, 31, .12);
            color: #667085;
            background: #fff;
        }

        .lap-home-template .sponsor-section .section-title h2 {
            color: #10131f;
            -webkit-text-fill-color: #10131f;
            -webkit-text-stroke-width: 0;
            text-shadow: none;
        }

        .lap-home-template .sponsor-section .section-title h2 span {
            color: transparent;
            -webkit-text-fill-color: transparent;
            -webkit-text-stroke-width: 2px;
            -webkit-text-stroke-color: var(--theme);
            text-shadow: 0 8px 18px rgba(16, 19, 31, .08);
        }

        .lap-home-template .sponsor-section .section-title h6 {
            color: rgba(16, 19, 31, .68);
            letter-spacing: .08em;
        }

        .lap-home-template .about-section .section-title h6 {
            color: rgba(255, 255, 255, .58);
            letter-spacing: .08em;
        }

        .lap-home-template .about-section .section-title h2 {
            color: #fff;
        }

        .lap-home-template .about-section .section-title h2 span {
            color: var(--theme);
        }

        .lap-home-template .about-section .section-title h2,
        .lap-home-template .about-section .section-title h2 span {
            text-shadow: none;
            -webkit-text-stroke-width: 0;
            -webkit-text-fill-color: inherit;
        }

        .lap-home-template .about-section .about-text {
            color: rgba(255, 255, 255, .74);
        }

        .lap-home-template .about-section .about-list-items ul li {
            color: rgba(255, 255, 255, .74);
            font-weight: 600;
        }

        .lap-home-template .about-section .about-list-items .lap-stat-value {
            color: #fff;
            font-weight: 800;
        }

        .lap-home-template .lap-match-meta {
            display: inline-flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .lap-home-template .lap-match-meta span {
            color: rgba(255,255,255,.7);
            font-size: 14px;
        }

        @media (max-width: 991.98px) {
            .lap-home-template .hero-image {
                max-width: 480px;
                margin: 0 auto;
            }

            .lap-home-template .section-title-area {
                gap: 16px;
            }

        }

        @media (max-width: 767.98px) {
        }
    </style>
@endpush

@section('content')
    <div class="lap-home-template">
        <section class="hero-section1 parallaxie hero-1 bg-cover" style="background-image: url('{{ $heroBgUrl }}');">
            <div class="right-shape">
                <img src="{{ $publicAsset('img/home-1/right-shape.png') }}" alt="" loading="lazy" decoding="async">
            </div>
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h6 class="wow fadeInUp">Portal Resmi Liga Anak Piaman Laweh</h6>
                            <h1 class="hero_title tv_hero_title hero_title_1">
                                Liga Anak <br>
                                <span>Pariaman</span>
                            </h1>
                            <p class="wow fadeInUp" data-wow-delay=".3s">
                                Liga Anak Piaman Laweh adalah liga sepak bola anak di Pariaman. Pantau jadwal pertandingan, hasil terbaru, klasemen, data klub peserta, pemain terverifikasi, dan dukungan sponsor kompetisi dalam satu portal resmi.
                            </p>
                            <div class="hero-btn wow fadeInUp" data-wow-delay=".5s">
                                <a href="{{ route('public.schedule') }}" class="theme-btn">
                                    Lihat Jadwal Laga <i class="fa-solid fa-arrow-up-right"></i>
                                </a>
                                <a href="{{ route('public.results') }}" class="theme-btn bg-white">
                                    Lihat Hasil Resmi <i class="fa-solid fa-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="{{ $publicAsset('img/home-1/hero1.png') }}" alt="Ilustrasi Liga Anak Pariaman" class="tilt_scale" fetchpriority="high" decoding="async">
                            <div class="hero-image-bg">
                                <img src="{{ $publicAsset('img/home-1/hero-image.png') }}" alt="" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="match-result-section section-bg wow fadeInUp" data-wow-delay=".3s">
            <div class="container">
                <div class="match-result-wrapper">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-1">
                            <p class="left-headling">Hasil terbaru</p>
                        </div>
                        <div class="col-lg-10">
                            @if ($tickerMatches->isNotEmpty())
                                <div class="swiper match-result-slider">
                                    <div class="swiper-wrapper">
                                        @foreach ($tickerMatches as $match)
                                            <div class="swiper-slide">
                                                <div class="match-result-item">
                                                    <p>{{ $match->is_finished ? 'Selesai' : 'Jadwal' }}</p>
                                                    <ul>
                                                        <li>
                                                            <span class="match-left">
                                                                @include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($match->clubA), 'label' => $match->clubA?->name ?: 'Klub A', 'badgeClass' => 'lap-home-club-mark', 'width' => 38, 'height' => 38])
                                                                {{ Str::upper(Str::limit($match->clubA?->short_name ?: $match->clubA?->name ?: 'Klub A', 3, '')) }}
                                                            </span>
                                                            <span class="match-right">
                                                                <span>{{ (int) $match->score_club_a }}</span>
                                                                <span>{{ (int) $match->score_club_b }}</span>
                                                            </span>
                                                        </li>
                                                        <li>
                                                            <span class="match-left">
                                                                @include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($match->clubB), 'label' => $match->clubB?->name ?: 'Klub B', 'badgeClass' => 'lap-home-club-mark', 'width' => 38, 'height' => 38])
                                                                {{ Str::upper(Str::limit($match->clubB?->short_name ?: $match->clubB?->name ?: 'Klub B', 3, '')) }}
                                                            </span>
                                                            <span class="match-right">
                                                                <span>{{ optional($match->match_date)->format('d') ?: '--' }}</span>
                                                            </span>
                                                        </li>
                                                    </ul>
                                                    <div class="arrow-shape">
                                                        <img src="{{ $publicAsset('img/home-1/match-result/arrow-shape.png') }}" alt="" loading="lazy" decoding="async">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="lap-empty-state">Belum ada hasil pertandingan yang bisa ditampilkan.</div>
                            @endif
                        </div>
                        <div class="col-lg-1">
                            <div class="array-button-2 d-grid mt-0 align-items-center">
                                <button class="array-prev2" aria-label="Hasil sebelumnya">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" viewBox="0 0 20 24" fill="none"><g opacity="0.72"><path d="M15.814 19.4316C16.2646 19.8822 16.9952 19.8822 17.4458 19.4316C17.8964 18.981 17.8964 18.2504 17.4458 17.7998L16.6299 18.6157L15.814 19.4316ZM3.37029 4.20228C2.73304 4.20228 2.21644 4.71887 2.21644 5.35613L2.21644 15.7407C2.21644 16.378 2.73303 16.8946 3.37029 16.8946C4.00754 16.8946 4.52413 16.378 4.52413 15.7407V6.50997H13.7549C14.3922 6.50997 14.9087 5.99338 14.9087 5.35613C14.9087 4.71887 14.3922 4.20228 13.7549 4.20228L3.37029 4.20228ZM16.6299 18.6157L17.4458 17.7998L4.18618 4.54023L3.37029 5.35613L2.55439 6.17202L15.814 19.4316L16.6299 18.6157Z" fill="#464E5E"/></g></svg>
                                </button>
                                <button class="array-next2" aria-label="Hasil berikutnya">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="20" viewBox="0 0 24 20" fill="none"><g opacity="0.72"><path d="M4.56838 15.814C4.11778 16.2646 4.11778 16.9952 4.56838 17.4458C5.01899 17.8964 5.74956 17.8964 6.20017 17.4458L5.38428 16.6299L4.56838 15.814ZM19.7977 3.37029C19.7977 2.73304 19.2811 2.21644 18.6439 2.21644L8.25926 2.21644C7.62201 2.21644 7.10541 2.73303 7.10541 3.37029C7.10541 4.00754 7.62201 4.52413 8.25926 4.52413H17.49V13.7549C17.49 14.3922 18.0066 14.9087 18.6439 14.9087C19.2811 14.9087 19.7977 14.3922 19.7977 13.7549L19.7977 3.37029ZM5.38428 16.6299L6.20017 17.4458L19.4598 4.18618L18.6439 3.37029L17.828 2.55439L4.56838 15.814L5.38428 16.6299Z" fill="#464E5E"/></g></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="match-section fix section-padding">
            <div class="container">
                <div class="row g-4 tv-desti-content">
                    @foreach ([
                        ['title' => 'Laga terakhir', 'match' => $lastMatch, 'primary' => ['label' => 'Laporan laga', 'url' => $lastMatch ? route('public.results.show', ['matchSlug' => $lastMatch->public_slug]) : route('public.results')], 'secondary' => ['label' => 'Semua hasil', 'url' => route('public.results')]],
                        ['title' => 'Laga berikutnya', 'match' => $nextMatch, 'primary' => ['label' => 'Jadwal laga', 'url' => route('public.schedule')], 'secondary' => ['label' => 'Daftar klub', 'url' => route('public.clubs')]],
                        ['title' => 'Laga pilihan', 'match' => $upcomingSpotlight, 'primary' => ['label' => 'Jadwal lengkap', 'url' => route('public.schedule')], 'secondary' => ['label' => 'Klasemen', 'url' => route('public.standings')]],
                    ] as $card)
                        <div class="col-xl-4 col-lg-6 col-md-6">
                            <div class="match-box-items tv-desti-item">
                                <h3>{{ strtoupper($card['title']) }}</h3>
                                @if ($card['match'])
                                    <h4>{{ optional($card['match']->match_date)->translatedFormat('D d M Y') ?: 'Tanggal menyusul' }}</h4>
                                    <p>{{ $card['match']->competition_format_label }} , {{ $card['match']->venue ?: 'Lokasi menyusul' }}</p>
                                    <div class="match-schedule">
                                        <div class="flag-item">
                                            @include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($card['match']->clubA), 'label' => $card['match']->clubA?->name ?: 'Klub A', 'badgeClass' => 'lap-home-club-mark', 'width' => 38, 'height' => 38])
                                            <span>{{ $card['match']->clubA?->short_name ?: $card['match']->clubA?->name ?: 'Klub A' }}</span>
                                        </div>
                                        <div class="match-date">
                                            <span>{{ $card['match']->is_finished ? $card['match']->score_label : ($card['match']->kickoff_time ? $card['match']->kickoff_time->format('H.i') : 'VS') }}</span>
                                        </div>
                                        <div class="flag-item">
                                            @include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($card['match']->clubB), 'label' => $card['match']->clubB?->name ?: 'Klub B', 'badgeClass' => 'lap-home-club-mark', 'width' => 38, 'height' => 38])
                                            <span>{{ $card['match']->clubB?->short_name ?: $card['match']->clubB?->name ?: 'Klub B' }}</span>
                                        </div>
                                    </div>
                                    <div class="match-btn">
                                        <a href="{{ $card['primary']['url'] }}" class="theme-btn">{{ strtoupper($card['primary']['label']) }} <i class="fa-solid fa-arrow-up-right"></i></a>
                                        <a href="{{ $card['secondary']['url'] }}" class="theme-btn style-2">{{ strtoupper($card['secondary']['label']) }} <i class="fa-solid fa-arrow-up-right"></i></a>
                                    </div>
                                @else
                                    <div class="lap-empty-state is-light">Belum ada pertandingan untuk slot ini.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="club-blog-section fix section-padding bg-cover" style="background-image: url('{{ $clubBlogBgUrl }}');">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="section-title-area align-items-end">
                            <div class="section-title hero_title tv_hero_title hero_title_1">
                                <h2>Profil <span>klub</span> peserta</h2>
                            </div>
                            <a href="{{ route('public.clubs') }}" class="link-btn wow fadeInUp" data-wow-delay=".3s">
                                Lihat semua klub <i class="fa-solid fa-arrow-up-right"></i>
                            </a>
                        </div>
                        <div class="row">
                            @forelse ($clubStories as $index => $club)
                                <div class="col-xl-6 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".{{ 3 + ($index * 2) }}s">
                                    <div class="club-blog-items">
                                        <div class="thumb">
                                            @include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($club), 'label' => $club->name, 'imgClass' => 'lap-home-story-logo', 'badgeClass' => 'lap-home-story-badge', 'width' => 54, 'height' => 54])
                                        </div>
                                        <div class="content">
                                            <ul>
                                                <li>
                                                    <span>
                                                        <i class="fa-regular fa-circle-user"></i>
                                                        Klub <b>{{ $club->short_name ?: Str::limit($club->name, 10, '') }}</b>
                                                    </span>
                                                </li>
                                                <li>
                                                    <span>
                                                        <i class="fa-solid fa-calendar-days"></i>
                                                        {{ optional($club->updated_at)->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}
                                                    </span>
                                                </li>
                                            </ul>
                                            <h3>
                                                <a href="{{ route('public.clubs.show', ['clubSlug' => $club->public_slug]) }}">{{ $club->name }}</a>
                                            </h3>
                                            <a href="{{ route('public.clubs.show', ['clubSlug' => $club->public_slug]) }}" class="link-btn">
                                                Lihat profil klub <i class="fa-solid fa-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="lap-empty-state">Klub peserta belum tersedia.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-lg-4 wow fadeInUp" data-wow-delay=".7s">
                        <div class="playing-result-box2 style-2">
                            <h3>HASIL RESMI</h3>
                            @forelse ($recentResults->take(3) as $match)
                                <div class="match-result-box {{ $loop->last ? 'mb-0' : '' }}">
                                    <div class="match-result">
                                        <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($match->clubA), 'label' => $match->clubA?->name ?: 'Klub A', 'badgeClass' => 'lap-home-club-mark', 'width' => 56, 'height' => 56])</div>
                                        <p>{{ Str::limit($match->clubA?->short_name ?: $match->clubA?->name ?: 'Klub A', 10, '') }}</p>
                                    </div>
                                    <div class="content">
                                        <p>{{ optional($match->match_date)->translatedFormat('d F Y') ?: 'Tanggal menyusul' }}</p>
                                        <div class="result-box"><h4>@include('public.partials.match-score', ['homeScore' => $match->score_club_a, 'awayScore' => $match->score_club_b, 'separator' => ' - '])</h4></div>
                                    </div>
                                    <div class="match-result">
                                        <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $clubLogoUrl($match->clubB), 'label' => $match->clubB?->name ?: 'Klub B', 'badgeClass' => 'lap-home-club-mark', 'width' => 56, 'height' => 56])</div>
                                        <p>{{ Str::limit($match->clubB?->short_name ?: $match->clubB?->name ?: 'Klub B', 10, '') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="lap-empty-state">Belum ada hasil pertandingan.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ranking-section parallaxie fix section-padding bg-cover" style="background-image: url('{{ $rankingBgUrl }}');">
            <div class="container">
                    <div class="section-title text-center mb-0 tv_hero_title">
                    <h6 class="wow fadeInUp">Klasemen resmi kompetisi</h6>
                    <h2 class="text-white hero_title hero_title_1">
                        Peringkat <span>klub</span> terbaru
                    </h2>
                </div>
                <div class="ranking-wrapper">
                    <div class="row g-5">
                        @forelse ($standingsBlocks as $standing)
                            <div class="col-lg-6 wow fadeInUp" data-wow-delay=".{{ 3 + ($loop->index * 2) }}s">
                                <div class="ranking-table-1">
                                    <div class="top-content">
                                        <div class="content">
                                            <h3 class="text-white">{{ $standing['age_group']?->name ?: 'Klasemen Klub' }}</h3>
                                            <p>Pembaruan terakhir: {{ now()->translatedFormat('d F Y') }}</p>
                                        </div>
                                        <a href="{{ route('public.standings') }}" class="rank-text">LIHAT KLASEMEN</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>PERINGKAT</th>
                                                    <th>NAMA KLUB</th>
                                                    <th>TOTAL POIN</th>
                                                    <th>+/- POIN</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($standing['rows'] as $row)
                                                    <tr>
                                                        <td class="rank">{{ str_pad((string) $row['position'], 2, '0', STR_PAD_LEFT) }}</td>
                                                        <td class="club">{{ $row['club_short_name'] }}</td>
                                                        <td class="points">{{ number_format($row['points'], 2) }}</td>
                                                        <td class="pos {{ $row['goal_difference'] >= 0 ? 'positive' : 'negative' }}">{{ $row['goal_difference'] >= 0 ? '+' : '' }}{{ $row['goal_difference'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="lap-empty-state">Klasemen belum tersedia.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="news-section fix section-padding pb-0">
            <div class="container">
                <div class="section-title-area">
                    <div class="section-title hero_title tv_hero_title hero_title_1">
                        <h2>Sorotan <span>kompetisi</span> pekan ini</h2>
                    </div>
                    <a href="{{ route('public.clubs') }}" class="theme-btn wow fadeInUp" data-wow-delay=".3s">
                        Jelajahi klub <i class="fa-solid fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="row">
                    @if ($latestWinMatch || $topScorer || $topAssist || $topStanding)
                        @if ($latestWinMatch)
                            <div class="col-lg-12 wow fadeInUp" data-wow-delay=".3s">
                                <div class="news-thumb1">
                                    <img src="{{ $publicAsset('img/home-1/news/news-01.jpg') }}" alt="Kemenangan terbaru" loading="lazy" decoding="async" fetchpriority="low">
                                    <div class="video-box">
                                        <a href="{{ route('public.results.show', ['matchSlug' => $latestWinMatch->public_slug]) }}" class="video-btn">
                                            <i class="fa-solid fa-circle-play"></i>
                                        </a>
                                        Kemenangan terbaru
                                    </div>
                                    <div class="news-content">
                                        <span>Kemenangan terbaru</span>
                                        <ul>
                                            <li>{{ optional($latestWinMatch->match_date)->translatedFormat('d F Y') ?: 'Tanggal menyusul' }}</li>
                                            <li>/</li>
                                            <li><i class="fa-solid fa-clock"></i> {{ $latestWinMatch->ageGroup?->name ?: 'Kompetisi' }}</li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('public.results.show', ['matchSlug' => $latestWinMatch->public_slug]) }}">{{ $latestWinMatch->clubA?->short_name ?: $latestWinMatch->clubA?->name ?: 'Klub A' }} {{ $latestWinMatch->score_label }} {{ $latestWinMatch->clubB?->short_name ?: $latestWinMatch->clubB?->name ?: 'Klub B' }}</a>
                                        </h3>
                                        <p>{{ $latestWinMatch->competition_format_label }} di {{ $latestWinMatch->venue ?: 'lokasi pertandingan' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($topScorer)
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".3s">
                                <div class="news-box-items">
                                    <div class="thumb">
                                        <div class="player-avatar-fallback" aria-hidden="true">
                                            <i class="fa-solid fa-futbol"></i>
                                        </div>
                                        <div class="video-box">
                                            <a href="{{ route('public.results') }}" class="video-btn">
                                                <i class="fa-solid fa-circle-play"></i>
                                            </a>
                                            Top skor
                                        </div>
                                    </div>
                                    <div class="content">
                                        <span>Top skor</span>
                                        <ul>
                                            <li>{{ $topScorer['age_group'] }}</li>
                                            <li>/</li>
                                            <li><i class="fa-solid fa-bullseye"></i> {{ $topScorer['count'] }} gol</li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('public.results') }}">{{ $topScorer['name'] }}</a>
                                        </h3>
                                        <p>{{ $topScorer['club'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($topAssist)
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".5s">
                                <div class="news-box-items">
                                    <div class="thumb">
                                        <div class="player-avatar-fallback" aria-hidden="true">
                                            <i class="fa-solid fa-hands-helping"></i>
                                        </div>
                                        <div class="video-box">
                                            <a href="{{ route('public.results') }}" class="video-btn">
                                                <i class="fa-solid fa-circle-play"></i>
                                            </a>
                                            Top assist
                                        </div>
                                    </div>
                                    <div class="content">
                                        <span>Top assist</span>
                                        <ul>
                                            <li>{{ $topAssist['age_group'] }}</li>
                                            <li>/</li>
                                            <li><i class="fa-solid fa-bullseye"></i> {{ $topAssist['count'] }} assist</li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('public.results') }}">{{ $topAssist['name'] }}</a>
                                        </h3>
                                        <p>{{ $topAssist['club'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($topStanding)
                            @php $standingLeader = $topStanding['rows']->first(); @endphp
                            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".7s">
                                <div class="news-box-items">
                                    <div class="thumb">
                                        <div class="player-avatar-fallback" aria-hidden="true">
                                            <i class="fa-solid fa-trophy"></i>
                                        </div>
                                        <div class="video-box">
                                            <a href="{{ route('public.standings') }}" class="video-btn">
                                                <i class="fa-solid fa-circle-play"></i>
                                            </a>
                                            Puncak klasemen
                                        </div>
                                    </div>
                                    <div class="content">
                                        <span>{{ $topStanding['age_group']?->name ?: 'Klasemen' }}</span>
                                        <ul>
                                            <li>Pemimpin klasemen</li>
                                            <li>/</li>
                                            <li><i class="fa-solid fa-bullseye"></i> {{ number_format($standingLeader['points'] ?? 0, 2) }} poin</li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('public.standings') }}">{{ $standingLeader['club_short_name'] ?? 'Belum ada data' }}</a>
                                        </h3>
                                        <p>{{ $standingLeader ? 'Selisih gol '.($standingLeader['goal_difference'] >= 0 ? '+' : '').($standingLeader['goal_difference'] ?? 0) : 'Klasemen belum tersedia' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="col-12">
                            <div class="lap-empty-state is-light">Belum ada sorotan aktivitas liga.</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="about-section fix section-padding">
            <div class="left-shape"><img src="{{ $publicAsset('img/home-1/about/left-shape.png') }}" alt="" loading="lazy" decoding="async" fetchpriority="low"></div>
            <div class="right-shape"><img src="{{ $publicAsset('img/home-1/about/right-shape.png') }}" alt="" loading="lazy" decoding="async" fetchpriority="low"></div>
            <div class="container">
                <div class="about-wrapper">
                    <div class="row g-4">
                        <div class="col-lg-6 wow fadeInUp" data-wow-delay=".3s">
                            <div class="about-image">
                                <img src="{{ $publicAsset('img/home-1/about/about.png') }}" alt="Ilustrasi portal kompetisi" loading="lazy" decoding="async" fetchpriority="low">
                                <div class="thumb1"><img src="{{ $publicAsset('img/home-1/about/about-2.png') }}" alt="" loading="lazy" decoding="async" fetchpriority="low"></div>
                                <div class="thumb2"><img src="{{ $publicAsset('img/home-1/about/about-3.png') }}" alt="" loading="lazy" decoding="async" fetchpriority="low"></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="about-content">
                                <div class="section-title mb-0 tv_hero_title">
                                    <h6 class="wow fadeInUp">Tentang Liga Anak Piaman Laweh</h6>
                                    <h2 class="text-white hero_title hero_title_1">
                                        Liga sepak bola <span>anak</span> Pariaman
                                    </h2>
                                </div>
                                <p class="about-text wow fadeInUp" data-wow-delay=".3s">
                                    Portal publik Liga Anak Pariaman ini menjadi pusat informasi kompetisi: data klub peserta, pemain terverifikasi, ofisial aktif, jadwal pertandingan, dan arsip hasil resmi yang terus diperbarui.
                                </p>
                                <div class="about-list-items wow fadeInUp" data-wow-delay=".5s">
                                    <ul>
                                        <li><i class="fa-solid fa-circle-check"></i> <span class="lap-stat-value">{{ number_format($publicStats['clubs'] ?? 0) }}</span> klub peserta terdaftar</li>
                                        <li><i class="fa-solid fa-circle-check"></i> <span class="lap-stat-value">{{ number_format($publicStats['players'] ?? 0) }}</span> pemain tercatat di sistem</li>
                                    </ul>
                                    <ul>
                                        <li><i class="fa-solid fa-circle-check"></i> <span class="lap-stat-value">{{ number_format($publicStats['officials'] ?? 0) }}</span> ofisial aktif mendampingi tim</li>
                                        <li><i class="fa-solid fa-circle-check"></i> <span class="lap-stat-value">{{ number_format($upcomingMatches->count()) }}</span> laga siap dipantau publik</li>
                                    </ul>
                                </div>
                                <a href="{{ route('public.clubs') }}" class="theme-btn wow fadeInUp" data-wow-delay=".7s">
                                    Lihat data klub <i class="fa-solid fa-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="team-section fix section-padding">
            <div class="container">
                <div class="section-title-area">
                    <div class="section-title hero_title tv_hero_title hero_title_1">
                        <h2 class="text-white">Pemain <span>terverifikasi</span></h2>
                    </div>
                    <div class="array-button-2 d-flex align-items-center wow fadeInUp" data-wow-delay=".3s">
                        <button class="lap-home-team-prev" aria-label="Pemain sebelumnya">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" viewBox="0 0 20 24" fill="none"><g opacity="0.72"><path d="M15.814 19.4316C16.2646 19.8822 16.9952 19.8822 17.4458 19.4316C17.8964 18.981 17.8964 18.2504 17.4458 17.7998L16.6299 18.6157L15.814 19.4316ZM3.37029 4.20228C2.73304 4.20228 2.21644 4.71887 2.21644 5.35613L2.21644 15.7407C2.21644 16.378 2.73303 16.8946 3.37029 16.8946C4.00754 16.8946 4.52413 16.378 4.52413 15.7407V6.50997H13.7549C14.3922 6.50997 14.9087 5.99338 14.9087 5.35613C14.9087 4.71887 14.3922 4.20228 13.7549 4.20228L3.37029 4.20228ZM16.6299 18.6157L17.4458 17.7998L4.18618 4.54023L3.37029 5.35613L2.55439 6.17202L15.814 19.4316L16.6299 18.6157Z" fill="#464E5E"/></g></svg>
                        </button>
                        <button class="lap-home-team-next" aria-label="Pemain berikutnya">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="20" viewBox="0 0 24 20" fill="none"><g opacity="0.72"><path d="M4.56838 15.814C4.11778 16.2646 4.11778 16.9952 4.56838 17.4458C5.01899 17.8964 5.74956 17.8964 6.20017 17.4458L5.38428 16.6299L4.56838 15.814ZM19.7977 3.37029C19.7977 2.73304 19.2811 2.21644 18.6439 2.21644L8.25926 2.21644C7.62201 2.21644 7.10541 2.73303 7.10541 3.37029C7.10541 4.00754 7.62201 4.52413 8.25926 4.52413H17.49V13.7549C17.49 14.3922 18.0066 14.9087 18.6439 14.9087C19.2811 14.9087 19.7977 14.3922 19.7977 13.7549L19.7977 3.37029ZM5.38428 16.6299L6.20017 17.4458L19.4598 4.18618L18.6439 3.37029L17.828 2.55439L4.56838 15.814L5.38428 16.6299Z" fill="#464E5E"/></g></svg>
                        </button>
                    </div>
                </div>
            </div>
            @if ($teamPlayers->isNotEmpty())
                <div class="swiper lap-home-team-slider">
                    <div class="swiper-wrapper">
                        @foreach ($teamPlayers as $player)
                            @php
                                $jerseyNumber = $player->displayJerseyNumber($player->primary_age_group_id) ?: $player->jersey_number;
                                $playerClub = $player->club?->short_name ?: $player->club?->name ?: 'Klub belum diisi';
                                $playerLink = $player->club ? route('public.clubs.show', ['clubSlug' => $player->club->public_slug]) : route('public.clubs');
                                $playerPhoto = $player->photo_file_url;
                            @endphp
                            <div class="swiper-slide">
                                <div class="our-club-payer-item">
                                    <div class="player-image">
                                        @if ($playerPhoto)
                                            <img src="{{ $playerPhoto }}" alt="{{ $player->name }}" loading="lazy" decoding="async" fetchpriority="low">
                                        @else
                                            <div class="player-avatar-fallback" aria-hidden="true">
                                                <i class="fa-regular fa-user"></i>
                                            </div>
                                        @endif
                                        <h2 class="number">{{ filled($jerseyNumber) ? str_pad((string) $jerseyNumber, 2, '0', STR_PAD_LEFT) : '--' }}</h2>
                                        <div class="player-content">
                                            <h4>
                                                <a href="{{ $playerLink }}">{{ $player->name }}</a>
                                            </h4>
                                            <p>{{ $player->displayPosition($player->primary_age_group_id) ?: ($player->position ?: 'Pemain') }}</p>
                                        </div>
                                        <div class="content-item">
                                            <div class="content">
                                                <h6>Klub</h6>
                                                <h5>{{ Str::limit($playerClub, 16) }}</h5>
                                                <span>Klub terdaftar</span>
                                                <h6 class="title">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</h6>
                                            </div>
                                            <div class="content">
                                                <h6>Kategori</h6>
                                                <h5>{{ Str::limit($player->primaryAgeGroup?->name ?: 'Belum diisi', 16) }}</h5>
                                                <span>Kelompok usia</span>
                                                <h6 class="title">{{ filled($jerseyNumber) ? str_pad((string) $jerseyNumber, 2, '0', STR_PAD_LEFT) : '00' }}</h6>
                                            </div>
                                            <div class="content">
                                                <h6>Status</h6>
                                                <h5>Terverifikasi</h5>
                                                <span>{{ optional($player->updated_at)->translatedFormat('d M Y') ?: 'Validasi admin' }}</span>
                                                <h6 class="title">OK</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="lap-home-team-empty">
                    <div class="lap-empty-state is-light">Belum ada pemain terverifikasi untuk ditampilkan.</div>
                </div>
            @endif
        </section>

        <section class="sponsor-section section-padding bg-cover" style="background-image: url('{{ $sponsorBgUrl }}');">
            <div class="container">
                    <div class="section-title text-center tv_hero_title">
                    <h6 class="wow fadeInUp">Mitra kompetisi</h6>
                    <h2 class="text-white hero_title hero_title_1">Mitra resmi & <span>pendukung</span> kompetisi</h2>
                </div>
                <div class="sponsor-wrapper-21">
                    <div class="table-responsive">
                        <div class="sponsor-wrap">
                            @foreach ($homeSponsors->chunk(5) as $row)
                                <div class="sponsor-box-items">
                                    @foreach ($row as $sponsor)
                                        <div class="sponsor-img {{ $loop->even ? 'bb-none' : '' }}">
                                            @if (!empty($sponsor['logo_url']))
                                                <img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsor['name'] }}" loading="lazy" decoding="async" fetchpriority="low" width="140" height="54">
                                            @else
                                                <span>{{ Str::upper(Str::substr($sponsor['short_name'] ?: $sponsor['name'], 0, 2)) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @for ($i = $row->count(); $i < 5; $i++)
                                        <div class="sponsor-img is-empty {{ $i % 2 === 0 ? 'bb-none' : '' }}"></div>
                                    @endfor
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Swiper === 'undefined') {
                return;
            }

            const teamSliderElement = document.querySelector('.lap-home-team-slider');
            if (!teamSliderElement) {
                return;
            }

            new window.Swiper('.lap-home-team-slider', {
                spaceBetween: 30,
                speed: 1300,
                loop: true,
                centeredSlides: true,
                autoplay: {
                    delay: 2200,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: '.lap-home-team-prev',
                    prevEl: '.lap-home-team-next',
                },
                breakpoints: {
                    1399: { slidesPerView: 4.3 },
                    1199: { slidesPerView: 3.2 },
                    991: { slidesPerView: 3 },
                    767: { slidesPerView: 2 },
                    575: { slidesPerView: 1.4 },
                    0: { slidesPerView: 1.2 },
                },
            });
        });
    </script>
@endpush

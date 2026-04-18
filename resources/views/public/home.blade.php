@extends('public.layout')

@push('styles')
    <style>
        .lap-public .rts-latest-match .club-area .club-logo {
            max-width: 84px;
        }

        .lap-public .rts-latest-match .club-area .club-logo img {
            max-height: 84px;
            width: auto;
        }

        .lap-public .rts-next-match-section .team-logo-area img {
            max-height: 62px;
            width: auto;
        }

        .lap-public .lap-home-sponsors .sponsor-single {
            display: block;
            padding: 10px 0;
            text-align: center;
            width: 100%;
        }

        .lap-public .lap-home-sponsors .sponsors-logo {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 138px;
            opacity: 1;
            transition: transform .25s ease, opacity .25s ease;
        }

        .lap-public .lap-home-sponsors .lap-home-sponsor-shell,
        .lap-public .lap-home-sponsors .sponsor-mark {
            align-items: center;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 24px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
            display: inline-flex;
            height: 122px;
            justify-content: center;
            padding: 18px;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
            width: min(100%, 178px);
        }

        .lap-public .lap-home-sponsors .sponsors-logo img {
            display: block;
            filter: none;
            height: auto;
            max-height: 84px;
            max-width: 100%;
            object-fit: contain;
            opacity: 1;
            width: auto;
        }

        .lap-public .lap-home-sponsors .sponsor-mark {
            color: rgba(15, 23, 42, .58);
            font-size: 34px;
            font-weight: 800;
            letter-spacing: .08em;
        }

        .lap-public .lap-home-sponsors .sponsor-single:hover .sponsors-logo,
        .lap-public .lap-home-sponsors .sponsor-single:focus .sponsors-logo {
            opacity: 1;
            transform: translateY(-2px);
        }

        .lap-public .lap-home-sponsors .sponsor-single:hover .lap-home-sponsor-shell,
        .lap-public .lap-home-sponsors .sponsor-single:hover .sponsor-mark,
        .lap-public .lap-home-sponsors .sponsor-single:focus .lap-home-sponsor-shell,
        .lap-public .lap-home-sponsors .sponsor-single:focus .sponsor-mark {
            border-color: rgba(228, 27, 35, .18);
            box-shadow: 0 20px 42px rgba(15, 23, 42, .12);
            transform: translateY(-4px);
        }

        .lap-public .lap-home-sponsors .lap-home-sponsor-note {
            background: #fff;
            border: 1px solid #ebedf2;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .06);
            margin: 0 auto;
            max-width: 720px;
            padding: 28px;
        }

        .lap-public .lap-home-results .lap-home-result-card {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
            height: 100%;
            padding: 28px;
        }

        .lap-public .lap-home-results .lap-home-result-meta {
            color: rgba(15, 23, 42, .62);
            display: flex;
            flex-wrap: wrap;
            font-size: 12px;
            font-weight: 700;
            gap: 10px;
            letter-spacing: .08em;
            margin-bottom: 22px;
            text-transform: uppercase;
        }

        .lap-public .lap-home-results .lap-home-result-meta span {
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 999px;
            padding: 8px 12px;
        }

        .lap-public .lap-home-results .lap-home-result-matchup {
            align-items: center;
            display: grid;
            gap: 18px;
            grid-template-columns: 1fr auto 1fr;
        }

        .lap-public .lap-home-results .lap-home-result-team {
            align-items: center;
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }

        .lap-public .lap-home-results .lap-home-result-team img {
            height: 72px;
            object-fit: contain;
            width: 72px;
        }

        .lap-public .lap-home-results .lap-home-result-team-name {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
        }

        .lap-public .lap-home-results .lap-home-result-score {
            color: var(--theme-color);
            font-size: 34px;
            font-weight: 800;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .lap-public .lap-home-results .lap-home-result-summary {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
            margin: 24px 0 10px;
            text-align: center;
        }

        .lap-public .lap-home-results .lap-home-result-detail {
            color: rgba(15, 23, 42, .68);
            margin-bottom: 22px;
            text-align: center;
        }

        .lap-public .lap-home-results .lap-home-result-card .btn-1 {
            width: 100%;
        }

        .lap-public .lap-home-cta-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .lap-public .lap-home-cta-actions .btn {
            min-width: 190px;
        }

        .lap-public .lap-home-cta-note {
            color: rgba(255, 255, 255, .78);
            font-size: 15px;
            line-height: 1.7;
            margin-top: 18px;
            max-width: 560px;
        }

        @media (max-width: 768px) {
            .lap-public .rts-latest-match .club-area .club-logo {
                max-width: 60px;
            }

            .lap-public .rts-latest-match .club-area .club-logo img {
                max-height: 60px;
            }

            .lap-public .rts-next-match-section .team-logo-area img {
                max-height: 48px;
            }

            .lap-public .lap-home-sponsors .sponsors-logo {
                min-height: 118px;
            }

            .lap-public .lap-home-sponsors .lap-home-sponsor-shell,
            .lap-public .lap-home-sponsors .sponsor-mark {
                border-radius: 20px;
                height: 104px;
                padding: 14px;
                width: min(100%, 150px);
            }

            .lap-public .lap-home-sponsors .sponsors-logo img {
                max-height: 72px;
            }

            .lap-public .lap-home-sponsors .sponsor-mark {
                font-size: 28px;
            }

            .lap-public .lap-home-sponsors .lap-home-sponsor-note {
                padding: 22px 18px;
            }

            .lap-public .lap-home-results .lap-home-result-card {
                padding: 22px 18px;
            }

            .lap-public .lap-home-results .lap-home-result-matchup {
                gap: 12px;
                grid-template-columns: 1fr;
            }

            .lap-public .lap-home-results .lap-home-result-score {
                font-size: 30px;
            }

            .lap-public .lap-home-cta-actions .btn {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
@endpush

@section('hero')
    @php
        $heroSlides = $upcomingMatches->take(3);
    @endphp
    <div class="banner banner1">
        <div class="swiper bannerSlide">
            <div class="swiper-wrapper">
                @forelse ($heroSlides as $match)
                    <div class="swiper-slide">
                        <div class="banner-single {{ ['banner-single-1', 'banner-single-1_2', 'banner-single-3'][$loop->index % 3] }} banner-bg">
                            <div class="container">
                                <div class="banner-content">
                                    <span class="pretitle">PORTAL PUBLIK KOMPETISI SEPAK BOLA ANAK</span>
                                    <h1 class="banner-heading">{{ strtoupper($match->clubA?->name ?: $match->clubA?->short_name ?: 'MENYUSUL') }} &amp; {{ strtoupper($match->clubB?->name ?: $match->clubB?->short_name ?: 'MENYUSUL') }}</h1>
                                    <div class="banner-btn-area">
                                        <a href="{{ route('public.schedule') }}" class="team-btn banner-btn">JADWAL <i class="far fa-long-arrow-right ml--5"></i></a>
                                        <a href="{{ route('public.standings') }}" class="nxt-match-btn banner-btn">KLASEMEN <i class="far fa-long-arrow-right ml--5"></i></a>
                                    </div>
                                    <p class="lap-muted mt--20">{{ $match->ageGroup?->name ?: '-' }} · {{ $match->match_day ?: 'Pekan pertandingan' }} · {{ $match->venue ?: 'Venue menyusul' }} · {{ optional($match->match_date)->translatedFormat('d F Y') }} {{ optional($match->kickoff_time)->format('H:i') }} WIB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="swiper-slide">
                        <div class="banner-single banner-single-1 banner-bg">
                            <div class="container">
                                <div class="banner-content">
                                    <span class="pretitle">PORTAL PUBLIK KOMPETISI SEPAK BOLA ANAK</span>
                                    <h1 class="banner-heading">LIGA ANAK PIAMAN LAWEH</h1>
                                    <div class="banner-btn-area">
                                        <a href="#footer-kontak" class="team-btn banner-btn">KONTAK <i class="far fa-long-arrow-right ml--5"></i></a>
                                        <a href="{{ auth()->check() ? route('dashboard.home') : route('login') }}" class="nxt-match-btn banner-btn">{{ auth()->check() ? 'BUKA DASHBOARD' : 'MASUK DASHBOARD' }} <i class="far fa-long-arrow-right ml--5"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            <div class="slider-pagination-area">
                <div class="swiper-paginations">
                    <span class="swiper-pagination-bullet one"></span>
                    <span class="swiper-pagination-bullet two"></span>
                    <span class="swiper-pagination-bullet three"></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @php
        $headlineMatchAtMs = null;

        if ($headlineMatch?->match_date) {
            $headlineTime = $headlineMatch?->kickoff_time?->format('H:i') ?? '00:00';
            $headlineMatchAtMs = \Illuminate\Support\Carbon::parse(
                $headlineMatch->match_date->format('Y-m-d').' '.$headlineTime,
                'Asia/Jakarta'
            )->timestamp * 1000;
        }

        $homeFeaturedClubs = $featuredClubs->take(3)->values();
        $homeRecentResults = $recentResults->take(3)->values();
        $homeSponsorSlides = collect($featuredSponsors)->values();
        $minimumHomeSponsorSlides = 6;

        if ($homeSponsorSlides->isNotEmpty() && $homeSponsorSlides->count() < $minimumHomeSponsorSlides) {
            $homeSponsorSlides = collect(range(1, (int) ceil($minimumHomeSponsorSlides / $homeSponsorSlides->count())))
                ->flatMap(fn () => collect($featuredSponsors)->values())
                ->take($minimumHomeSponsorSlides)
                ->values();
        }

        $homeUpcomingMatches = $upcomingMatches->take(3)->values();
        $fallbackGalleryImages = [
            ['full' => asset('kester-assets/images/gallery/full1.png'), 'small' => asset('kester-assets/images/gallery/1.png')],
            ['full' => asset('kester-assets/images/gallery/full2.png'), 'small' => asset('kester-assets/images/gallery/2.png')],
            ['full' => asset('kester-assets/images/gallery/full3.png'), 'small' => asset('kester-assets/images/gallery/3.png')],
        ];
    @endphp

    <div class="rts-latest-match">
        <div class="container">
            <div class="latest-match-inner">
                <div class="club-area">
                    <div class="club-logo">
                        <img src="{{ $headlineMatch?->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-1.svg') }}" alt="{{ $headlineMatch?->clubA?->name ?: 'Klub A' }}">
                    </div>
                    <div class="content">
                        <h3 class="club-text">
                            {{ strtoupper($headlineMatch?->clubA?->name ?: $headlineMatch?->clubA?->short_name ?: 'KLUB A') }}
                        </h3>
                        <span class="match-type">{{ strtoupper($headlineMatch?->round_display_label ?: 'PERTANDINGAN BERIKUTNYA') }}</span>
                    </div>
                </div>
                <div class="match-countdown-area">
                    <div class="countdown" @if ($headlineMatchAtMs) data-countdown-ts="{{ $headlineMatchAtMs }}" @endif>
                        <div class="countdown-el days-c">
                            <span class="value" id="days">0</span>
                        </div>
                        <span class="letter">D</span>
                        <div class="countdown-el hours-c">
                            <span class="value" id="hours">00</span>
                        </div>
                        <span class="letter">H</span>
                        <div class="countdown-el mins-c">
                            <span class="value" id="mins">00</span>
                        </div>
                        <span class="letter">M</span>
                        <div class="countdown-el seconds-c">
                            <span class="value" id="seconds">00</span>
                        </div>
                        <span class="letter">S</span>
                    </div>
                </div>
                <div class="club-area">
                    <div class="content text-end ml--40 mr--0">
                        <h3 class="club-text">
                            {{ strtoupper($headlineMatch?->clubB?->name ?: $headlineMatch?->clubB?->short_name ?: 'KLUB B') }}
                        </h3>
                        <span class="match-type">
                            {{ $headlineMatch ? (optional($headlineMatch->match_date)->translatedFormat('d F Y').' · '.optional($headlineMatch->kickoff_time)->format('H:i').' WIB') : 'DATA AKAN MUNCUL SAAT JADWAL TERSEDIA' }}
                        </span>
                    </div>
                    <div class="club-logo ml--40 mr--0">
                        <img src="{{ $headlineMatch?->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-2.svg') }}" alt="{{ $headlineMatch?->clubB?->name ?: 'Klub B' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rts-gallery-section">
        <div class="container">
            <div class="gallery-area">
                @forelse ($homeFeaturedClubs as $club)
                    @php
                        $clubUrl = route('public.clubs.show', ['clubSlug' => $club->public_slug]);
                        $galleryImage = $club->logo_file_url ?: ($fallbackGalleryImages[$loop->index]['full'] ?? asset('kester-assets/images/gallery/full1.png'));
                        $galleryImageSmall = $club->logo_file_url ?: ($fallbackGalleryImages[$loop->index]['small'] ?? asset('kester-assets/images/gallery/1.png'));
                    @endphp
                    <div class="gallery-item text-center {{ $loop->first ? 'active mid' : '' }}">
                        <a href="{{ $clubUrl }}" class="gallery-picture"><img src="{{ $galleryImage }}" alt="{{ $club->name }}"></a>
                        <a href="{{ $clubUrl }}" class="gallery-picture1"><img src="{{ $galleryImageSmall }}" alt="{{ $club->name }}"></a>
                        <div class="contents-wrapper">
                            <div class="contents text-start">
                                <div class="d-block">
                                    <p class="tag">{{ strtoupper($club->zone ?: 'KLUB PESERTA') }}</p>
                                    <a href="{{ $clubUrl }}" class="gallery-title">{{ strtoupper($club->name) }}</a>
                                </div>
                                <div class="author-info">
                                    <div class="content">
                                        <a href="{{ $clubUrl }}" class="read-more">LIHAT PROFIL</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    @for ($i = 0; $i < 3; $i++)
                        <div class="gallery-item text-center {{ $i === 1 ? 'active mid' : '' }}">
                            <a href="{{ route('public.clubs') }}" class="gallery-picture"><img src="{{ $fallbackGalleryImages[$i]['full'] }}" alt="Klub Peserta"></a>
                            <a href="{{ route('public.clubs') }}" class="gallery-picture1"><img src="{{ $fallbackGalleryImages[$i]['small'] }}" alt="Klub Peserta"></a>
                            <div class="contents-wrapper">
                                <div class="contents text-start">
                                    <div class="d-block">
                                        <p class="tag">KLUB PESERTA</p>
                                        <a href="{{ route('public.clubs') }}" class="gallery-title">SOROTAN KLUB KOMPETISI</a>
                                    </div>
                                    <div class="author-info">
                                        <div class="content">
                                            <a href="{{ route('public.clubs') }}" class="read-more">LIHAT KLUB</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                @endforelse
            </div>
        </div>
    </div>

    <div class="rts-next-match-section section-gap">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">LAGA</span>
                <h1 class="section-title">JADWAL TERDEKAT</h1>
                <p>Berikut beberapa pertandingan terdekat yang sudah terjadwal pada portal publik kompetisi.</p>
            </div>
            <ul class="table-area table-full">
                @forelse ($homeUpcomingMatches as $match)
                    <li class="table-inner">
                        <div class="team-name">
                            <p class="mode">{{ strtoupper($match->competition_format_label) }} · {{ strtoupper($match->ageGroup?->name ?: '-') }}</p>
                            <h3 class="name">{{ $match->venue ?: 'Venue menyusul' }}</h3>
                            <p class="time">{{ optional($match->match_date)->translatedFormat('d F Y') }} {{ optional($match->kickoff_time)->format('H:i') }} WIB</p>
                        </div>
                        <div class="team-logo-area">
                            <a href="{{ $match->clubA ? route('public.clubs.show', ['clubSlug' => $match->clubA->public_slug]) : route('public.schedule') }}" class="text-center">
                                <img src="{{ $match->clubA?->logo_file_url ?: asset('kester-assets/images/team/logo-01.png') }}" alt="{{ $match->clubA?->name ?: 'Klub A' }}">
                                <p class="team">{{ strtoupper($match->clubA?->name ?: $match->clubA?->short_name ?: 'MENYUSUL') }}</p>
                            </a>
                            <span class="versus">VS</span>
                            <a href="{{ $match->clubB ? route('public.clubs.show', ['clubSlug' => $match->clubB->public_slug]) : route('public.schedule') }}" class="text-center">
                                <img src="{{ $match->clubB?->logo_file_url ?: asset('kester-assets/images/team/logo-02.png') }}" alt="{{ $match->clubB?->name ?: 'Klub B' }}">
                                <p class="team">{{ strtoupper($match->clubB?->name ?: $match->clubB?->short_name ?: 'MENYUSUL') }}</p>
                            </a>
                        </div>
                        <div class="button-area">
                            <a href="{{ route('public.schedule') }}" class="btn-1">Lihat Jadwal</a>
                            <a href="{{ route('public.standings') }}" class="btn-2">Lihat Klasemen</a>
                        </div>
                    </li>
                @empty
                    <li class="table-inner">
                        <div class="team-name">
                            <p class="mode">JADWAL</p>
                            <h3 class="name">Pertandingan belum tersedia</h3>
                            <p class="time">Silakan cek kembali setelah admin menambahkan jadwal.</p>
                        </div>
                        <div class="team-logo-area">
                            <a href="{{ route('public.schedule') }}" class="text-center">
                                <img src="{{ asset('kester-assets/images/team/logo-01.png') }}" alt="">
                                <p class="team">MENYUSUL</p>
                            </a>
                            <span class="versus">VS</span>
                            <a href="{{ route('public.schedule') }}" class="text-center">
                                <img src="{{ asset('kester-assets/images/team/logo-02.png') }}" alt="">
                                <p class="team">MENYUSUL</p>
                            </a>
                        </div>
                        <div class="button-area">
                            <a href="{{ route('public.schedule') }}" class="btn-1">Lihat Jadwal</a>
                            <a href="#footer-kontak" class="btn-2">Lihat Kontak</a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="rts-about-section section-gap">
        <div class="shape1"><img src="{{ asset('kester-assets/images/about/shape2.png') }}" alt=""></div>
        <div class="shape2"><img src="{{ asset('kester-assets/images/about/shape1.png') }}" alt=""></div>
        <div class="container-1">
            <div class="about-inner">
                <div class="row align-items-center">
                    <div class="col-lg-5 col-12">
                        <div class="about-thumb">
                            <div class="img1"><img src="{{ asset('kester-assets/images/gallery/img4.jpg') }}" alt="about-thumb"></div>
                            <div class="img2"><img src="{{ asset('kester-assets/images/gallery/img5.jpg') }}" alt="about-thumb"></div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-12">
                        <div class="contents">
                            <div class="section-title-area section-title-area1 text-start">
                                <span class="pretitle">TENTANG PORTAL</span>
                                <h1 class="section-title"><span>TENTANG</span> <br> LIGA ANAK PIAMAN LAWEH</h1>
                                <p>Portal resmi Liga Anak Piaman Laweh untuk jadwal pertandingan, hasil laga, klasemen, daftar klub, dan sponsor kompetisi.</p>
                            </div>
                            <ul>
                                <li class="player">
                                    <p class="sub">KLUB</p>
                                    <h3 class="title">{{ $publicStats['clubs'] }}</h3>
                                </li>
                                <li class="player">
                                    <p class="sub">PEMAIN</p>
                                    <h3 class="title">{{ $publicStats['players'] }}</h3>
                                </li>
                                <li class="player">
                                    <p class="sub">OFISIAL</p>
                                    <h3 class="title">{{ $publicStats['officials'] }}</h3>
                                </li>
                            </ul>
                            <a href="#footer-kontak" class="more-btn">KONTAK <i class="fal fa-long-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rts-sponsors-section section-gap lap-home-sponsors">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">SPONSOR</span>
                <h1 class="section-title">SPONSOR RESMI</h1>
                <p>Terima kasih untuk sponsor yang mendukung kompetisi. Klik logo untuk melihat profil sponsor.</p>
            </div>
            @if ($homeSponsorSlides->isNotEmpty())
                <div class="sponsors-section-inner">
                    <div class="swiper rts-brandSlider">
                        <div class="swiper-wrapper">
                            @foreach ($homeSponsorSlides as $sponsor)
                                @php
                                    $hasLogo = filled($sponsor['logo_url'] ?? null);
                                    $sponsorHref = filled($sponsor['website_url'] ?? null) ? $sponsor['website_url'] : route('public.sponsors');
                                    $sponsorMark = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sponsor['short_name'] ?: $sponsor['name'], 0, 2));
                                @endphp
                                <div class="swiper-slide">
                                    <a href="{{ $sponsorHref }}" class="sponsor-single" aria-label="{{ $sponsor['name'] }}" @if (filled($sponsor['website_url'] ?? null)) target="_blank" rel="noopener" @endif>
                                        <div class="sponsors-logo">
                                            @if ($hasLogo)
                                                <span class="lap-home-sponsor-shell">
                                                    <img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsor['name'] }}">
                                                </span>
                                            @else
                                                <span class="sponsor-mark" aria-label="{{ $sponsor['name'] }}">{{ $sponsorMark }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="lap-home-sponsor-note text-center">
                    <p class="mb-0">Daftar sponsor akan diumumkan segera.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="rts-team-section section-gap">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">PEMAIN</span>
                <h1 class="section-title">PEMAIN TERVERIFIKASI</h1>
                <p>Beberapa pemain yang sudah terverifikasi pada portal kompetisi.</p>
            </div>
            <div class="team-section-inner">
                <div class="swiper rts-teamSlider">
                    <div class="swiper-wrapper">
                        @forelse ($featuredPlayers as $player)
                            @php
                                $playerImage = $player->photo_file_url ?: asset('kester-assets/images/team/team'.str_pad((string) (($loop->index % 9) + 1), 2, '0', STR_PAD_LEFT).'.png');
                                $playerClubUrl = $player->club ? route('public.clubs.show', ['clubSlug' => $player->club->public_slug]) : route('public.clubs');
                            @endphp
                            <div class="swiper-slide">
                                <div class="team-wraper">
                                    <div class="player-card">
                                        <a class="image" href="{{ $playerClubUrl }}"><img src="{{ $playerImage }}" alt="{{ $player->name }}"></a>
                                        <div class="number">{{ $player->jersey_number ?: '-' }}</div>
                                        <ul class="social-area">
                                            <li><a href="#0" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                            <li><a href="#0" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                                            <li><a href="#0" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                                        </ul>
                                    </div>
                                    <div class="profile">
                                        <p class="position">{{ strtoupper($player->position ?: 'PEMAIN') }}</p>
                                        <a href="{{ $playerClubUrl }}" class="name">{{ strtoupper($player->name) }}</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            @for ($i = 1; $i <= 6; $i++)
                                <div class="swiper-slide">
                                    <div class="team-wraper">
                                        <div class="player-card">
                                            <a class="image" href="{{ route('public.clubs') }}"><img src="{{ asset('kester-assets/images/team/team'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'.png') }}" alt="Pemain"></a>
                                            <div class="number">{{ $i }}</div>
                                            <ul class="social-area">
                                                <li><a href="#0"><i class="fab fa-facebook-f"></i></a></li>
                                                <li><a href="#0"><i class="fab fa-twitter"></i></a></li>
                                                <li><a href="#0"><i class="fab fa-linkedin-in"></i></a></li>
                                            </ul>
                                        </div>
                                        <div class="profile">
                                            <p class="position">PEMAIN</p>
                                            <a href="{{ route('public.clubs') }}" class="name">PEMAIN {{ $i }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        @endforelse
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="rts-newsletter-section">
        <div class="container">
            <div class="newsletter-inner">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="newsletter-box">
                            <div class="shape">
                                <img src="{{ asset('kester-assets/images/team/shape.png') }}" alt="">
                            </div>
                            <div class="section-title-area section-title-area1 text-start mb--50">
                                <h1 class="section-title">IKUTI KOMPETISI DARI SATU PORTAL</h1>
                                <p>Pantau jadwal, hasil pertandingan, klasemen, dan hubungi panitia dari halaman publik yang sama.</p>
                            </div>
                            <div class="lap-home-cta-actions">
                                <a href="{{ route('public.schedule') }}" class="btn">LIHAT JADWAL <i class="fal fa-long-arrow-right"></i></a>
                                <a href="{{ route('public.results') }}" class="btn btn-light">LIHAT HASIL</a>
                                <a href="#footer-kontak" class="btn btn-light">HUBUNGI PANITIA</a>
                                <a href="{{ auth()->check() ? route('dashboard.home') : route('login') }}" class="btn btn-light">{{ auth()->check() ? 'BUKA DASHBOARD' : 'MASUK DASHBOARD' }}</a>
                            </div>
                            <p class="lap-home-cta-note">Gunakan tombol di atas untuk memantau jalannya kompetisi, melihat hasil terbaru, atau langsung menghubungi panitia.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="{{ asset('kester-assets/images/team/player3.png') }}" alt="Ilustrasi pemain Liga Anak Piaman Laweh">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rts-blog-section section-gap lap-home-results">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">HASIL</span>
                <h1 class="section-title">HASIL PERTANDINGAN TERBARU</h1>
                <p>Skor akhir dan ringkasan laga terbaru yang sudah dicatat pada portal kompetisi.</p>
            </div>
            <div class="blog-area">
                <div class="row">
                    @forelse ($homeRecentResults as $match)
                        <div class="col-xl-4 col-md-6">
                            <div class="lap-home-result-card">
                                <div class="lap-home-result-meta">
                                    <span>{{ strtoupper($match->ageGroup?->name ?: 'UMUM') }}</span>
                                    <span>{{ strtoupper($match->competition_format_label) }}</span>
                                </div>
                                <div class="lap-home-result-matchup">
                                    <div class="lap-home-result-team">
                                        <img src="{{ $match->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-3.svg') }}" alt="{{ $match->clubA?->name ?: 'Klub A' }}">
                                        <div class="lap-home-result-team-name">{{ strtoupper($match->clubA?->name ?: $match->clubA?->short_name ?: 'MENYUSUL') }}</div>
                                    </div>
                                    <div class="lap-home-result-score">{{ $match->score_label }}</div>
                                    <div class="lap-home-result-team">
                                        <img src="{{ $match->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-4.svg') }}" alt="{{ $match->clubB?->name ?: 'Klub B' }}">
                                        <div class="lap-home-result-team-name">{{ strtoupper($match->clubB?->name ?: $match->clubB?->short_name ?: 'MENYUSUL') }}</div>
                                    </div>
                                </div>
                                <p class="lap-home-result-summary">{{ strtoupper($match->result_summary) }}</p>
                                <p class="lap-home-result-detail">{{ optional($match->match_date)->translatedFormat('d F Y') }} · {{ optional($match->kickoff_time)->format('H:i') }} WIB · {{ $match->venue ?: 'Venue belum diisi' }}</p>
                                <a href="{{ route('public.results') }}" class="btn-1">Lihat Semua Hasil</a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="lap-home-result-card text-center">
                                <p class="lap-home-result-summary mb--10">Belum ada hasil pertandingan terbaru</p>
                                <p class="lap-home-result-detail">Hasil akan tampil di sini setelah pertandingan selesai dan skor akhir dicatat admin.</p>
                                <a href="{{ route('public.schedule') }}" class="btn-1">Lihat Jadwal Pertandingan</a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

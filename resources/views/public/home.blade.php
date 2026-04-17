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
                                    <h1 class="banner-heading">{{ strtoupper($match->clubA?->short_name ?: $match->clubA?->name ?: 'TBD') }} &amp; {{ strtoupper($match->clubB?->short_name ?: $match->clubB?->name ?: 'TBD') }}</h1>
                                    <div class="banner-btn-area">
                                        <a href="{{ route('public.schedule') }}" class="team-btn banner-btn">JADWAL <i class="far fa-long-arrow-right ml--5"></i></a>
                                        <a href="{{ route('public.standings') }}" class="nxt-match-btn banner-btn">KLASEMEN <i class="far fa-long-arrow-right ml--5"></i></a>
                                    </div>
                                    <p class="lap-muted mt--20">{{ $match->ageGroup?->name ?: '-' }} · {{ $match->match_day ?: 'Matchday' }} · {{ $match->venue ?: 'Venue menyusul' }} · {{ optional($match->match_date)->translatedFormat('d F Y') }} {{ optional($match->kickoff_time)->format('H:i') }} WIB</p>
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
                                        <a href="{{ route('public.information') }}" class="team-btn banner-btn">INFORMASI <i class="far fa-long-arrow-right ml--5"></i></a>
                                        <a href="{{ auth()->check() ? route('dashboard.home') : route('login') }}" class="nxt-match-btn banner-btn">{{ auth()->check() ? 'DASHBOARD' : 'MASUK' }} <i class="far fa-long-arrow-right ml--5"></i></a>
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

        $homeGalleryResources = $publishedResources->take(3)->values();
        $homeBlogResources = $publishedResources->slice(3, 3)->values();
        $homeSponsorSlides = collect($featuredSponsors)->values();
        $minimumHomeSponsorSlides = 6;

        if ($homeBlogResources->isEmpty()) {
            $homeBlogResources = $homeGalleryResources;
        }

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
        $fallbackBlogImages = [
            asset('kester-assets/images/blog/blog1.jpg'),
            asset('kester-assets/images/blog/blog2.jpg'),
            asset('kester-assets/images/blog/blog3.jpg'),
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
                            {{ strtoupper($headlineMatch?->clubA?->short_name ?: $headlineMatch?->clubA?->name ?: 'KLUB A') }}
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
                            {{ strtoupper($headlineMatch?->clubB?->short_name ?: $headlineMatch?->clubB?->name ?: 'KLUB B') }}
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
                @forelse ($homeGalleryResources as $resource)
                    @php
                        $resourceUrl = route('public.information.show', ['resourceSlug' => $resource->public_slug]);
                        $galleryImage = $resource->is_image ? $resource->file_url : ($fallbackGalleryImages[$loop->index]['full'] ?? asset('kester-assets/images/gallery/full1.png'));
                        $galleryImageSmall = $resource->is_image ? $resource->file_url : ($fallbackGalleryImages[$loop->index]['small'] ?? asset('kester-assets/images/gallery/1.png'));
                    @endphp
                    <div class="gallery-item text-center {{ $loop->first ? 'active mid' : '' }}">
                        <a href="{{ $resourceUrl }}" class="gallery-picture"><img src="{{ $galleryImage }}" alt="{{ $resource->title }}"></a>
                        <a href="{{ $resourceUrl }}" class="gallery-picture1"><img src="{{ $galleryImageSmall }}" alt="{{ $resource->title }}"></a>
                        <div class="contents-wrapper">
                            <div class="contents text-start">
                                <div class="d-block">
                                    <p class="tag">{{ strtoupper($resource->badge_label) }}</p>
                                    <a href="{{ $resourceUrl }}" class="gallery-title">{{ strtoupper($resource->title) }}</a>
                                </div>
                                <div class="author-info">
                                    <div class="content">
                                        <a href="{{ $resourceUrl }}" class="read-more">READ MORE</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    @for ($i = 0; $i < 3; $i++)
                        <div class="gallery-item text-center {{ $i === 1 ? 'active mid' : '' }}">
                            <a href="{{ route('public.information') }}" class="gallery-picture"><img src="{{ $fallbackGalleryImages[$i]['full'] }}" alt="Informasi Kompetisi"></a>
                            <a href="{{ route('public.information') }}" class="gallery-picture1"><img src="{{ $fallbackGalleryImages[$i]['small'] }}" alt="Informasi Kompetisi"></a>
                            <div class="contents-wrapper">
                                <div class="contents text-start">
                                    <div class="d-block">
                                        <p class="tag">INFO</p>
                                        <a href="{{ route('public.information') }}" class="gallery-title">INFORMASI RESMI KOMPETISI</a>
                                    </div>
                                    <div class="author-info">
                                        <div class="content">
                                            <a href="{{ route('public.information') }}" class="read-more">READ MORE</a>
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
                <span class="pretitle">GAMES</span>
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
                                <p class="team">{{ strtoupper($match->clubA?->short_name ?: $match->clubA?->name ?: 'TBD') }}</p>
                            </a>
                            <span class="versus">VS</span>
                            <a href="{{ $match->clubB ? route('public.clubs.show', ['clubSlug' => $match->clubB->public_slug]) : route('public.schedule') }}" class="text-center">
                                <img src="{{ $match->clubB?->logo_file_url ?: asset('kester-assets/images/team/logo-02.png') }}" alt="{{ $match->clubB?->name ?: 'Klub B' }}">
                                <p class="team">{{ strtoupper($match->clubB?->short_name ?: $match->clubB?->name ?: 'TBD') }}</p>
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
                                <p class="team">TBD</p>
                            </a>
                            <span class="versus">VS</span>
                            <a href="{{ route('public.schedule') }}" class="text-center">
                                <img src="{{ asset('kester-assets/images/team/logo-02.png') }}" alt="">
                                <p class="team">TBD</p>
                            </a>
                        </div>
                        <div class="button-area">
                            <a href="{{ route('public.schedule') }}" class="btn-1">Lihat Jadwal</a>
                            <a href="{{ route('public.information') }}" class="btn-2">Lihat Informasi</a>
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
                                <span class="pretitle">PORTAL</span>
                                <h1 class="section-title"><span>TENTANG</span> <br> LIGA ANAK PIAMAN LAWEH</h1>
                                <p>Portal resmi Liga Anak Piaman Laweh untuk jadwal pertandingan, hasil laga, klasemen, daftar klub, dan informasi kompetisi.</p>
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
                                    <p class="sub">OFFICIAL</p>
                                    <h3 class="title">{{ $publicStats['officials'] }}</h3>
                                </li>
                            </ul>
                            <a href="{{ route('public.information') }}" class="more-btn">INFORMASI <i class="fal fa-long-arrow-right"></i></a>
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
                <p>Terima kasih untuk sponsor yang mendukung kompetisi. Klik logo untuk melihat info sponsor.</p>
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
                <span class="pretitle">PLAYERS</span>
                <h1 class="section-title">CLUB MEMBERS</h1>
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
                                        <p class="position">{{ strtoupper($player->position ?: 'PLAYER') }}</p>
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
                                            <p class="position">PLAYER</p>
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
                                <h1 class="section-title">DAPATKAN INFO TERBARU</h1>
                                <p>Masukkan email untuk menerima informasi publik terbaru dari kompetisi.</p>
                            </div>
                            <form action="#0" onsubmit="return false;">
                                <div class="form">
                                    <input type="email" class="form-control" placeholder="Email kamu" required />
                                </div>
                                <div class="button">
                                    <button type="submit" class="btn">SUBSCRIBE <i class="fal fa-long-arrow-right"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="{{ asset('kester-assets/images/team/player3.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rts-blog-section section-gap">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">INSIGHTS</span>
                <h1 class="section-title">INFORMASI TERBARU</h1>
                <p>Ringkasan informasi dan publikasi terbaru seputar kompetisi.</p>
            </div>
            <div class="blog-area">
                <div class="row">
                    @forelse ($homeBlogResources as $resource)
                        @php
                            $resourceUrl = route('public.information.show', ['resourceSlug' => $resource->public_slug]);
                            $blogImage = $resource->is_image ? $resource->file_url : ($fallbackBlogImages[$loop->index] ?? $fallbackBlogImages[0]);
                        @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="blog-item">
                                <div class="blog-picture"><img src="{{ $blogImage }}" alt="{{ $resource->title }}"></div>
                                <div class="contents-wrapper">
                                    <div class="contents">
                                        <div class="d-block">
                                            <a href="{{ $resourceUrl }}" class="blog-catagory">{{ strtoupper($resource->badge_label) }}</a>
                                            <a href="{{ $resourceUrl }}" class="blog-title">{{ strtoupper($resource->title) }}</a>
                                        </div>
                                        <div class="author-info">
                                            <div class="author-dp lap-author-badge">
                                                <span class="lap-author-avatar">
                                                    @if ($resource->author_avatar_url)
                                                        <img src="{{ $resource->author_avatar_url }}" alt="{{ $resource->author_name }}">
                                                    @else
                                                        <span class="lap-author-avatar-fallback">{{ $resource->author_initials }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="content">
                                                <a href="{{ $resourceUrl }}" class="author-name">{{ strtoupper($resource->author_name) }}</a>
                                                <div class="blog-date">
                                                    <div class="date">{{ optional($resource->created_at)->translatedFormat('d F Y') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        @for ($i = 0; $i < 3; $i++)
                            <div class="col-xl-4 col-md-6">
                                <div class="blog-item">
                                    <div class="blog-picture"><img src="{{ $fallbackBlogImages[$i] }}" alt=""></div>
                                    <div class="contents-wrapper">
                                        <div class="contents">
                                            <div class="d-block">
                                                <a href="{{ route('public.information') }}" class="blog-catagory">INFO</a>
                                                <a href="{{ route('public.information') }}" class="blog-title">INFORMASI KOMPETISI</a>
                                            </div>
                                            <div class="author-info">
                                                <div class="author-dp lap-author-badge">
                                                    <span class="lap-author-avatar">
                                                        <span class="lap-author-avatar-fallback">AD</span>
                                                    </span>
                                                </div>
                                                <div class="content">
                                                    <a href="{{ route('public.information') }}" class="author-name">ADMIN</a>
                                                    <div class="blog-date">
                                                        <div class="date">{{ now()->translatedFormat('d F Y') }}</div>
                                                    </div>
                                                </div>
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
    </div>
@endsection

@extends('public.layout')

@push('styles')
    <style>
        .lap-sponsor-clubs {
            background: #ffffff;
        }

        .lap-sponsor-clubs .section-title-area {
            position: relative;
            z-index: 1;
            padding-top: 28px;
        }

        .lap-sponsor-clubs .section-title-area::before {
            content: "PESERTA";
            position: absolute;
            left: 50%;
            top: -56px;
            transform: translateX(-50%);
            font-size: clamp(120px, 18vw, 260px);
            font-weight: 800;
            letter-spacing: .04em;
            color: rgba(15, 23, 42, 0.035);
            line-height: 1;
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
        }

        .lap-sponsor-clubs .section-title-area .pretitle {
            color: #e41b23;
            position: relative !important;
            left: auto !important;
            top: auto !important;
            transform: none !important;
            display: inline-block;
            font-size: 14px !important;
            font-weight: 700;
            letter-spacing: .12em;
            line-height: 1;
            margin-bottom: 12px;
            text-transform: uppercase;
            z-index: 1;
            -webkit-text-fill-color: currentColor !important;
            -webkit-text-stroke-width: 0 !important;
        }

        .lap-sponsor-clubs .section-title-area p {
            max-width: 760px;
            margin: 18px auto 0;
        }

        .lap-sponsor-clubs .sponsor-single {
            background: transparent;
            border: 0;
            box-shadow: none;
            color: inherit;
            display: block;
            padding: 12px 0;
        }

        .lap-sponsor-clubs .swiper-slide {
            height: auto;
        }

        .lap-sponsor-clubs .sponsors-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 150px;
            opacity: .86;
            transition: transform .25s ease, opacity .25s ease, filter .25s ease;
        }

        .lap-sponsor-clubs .club-logo-shell {
            align-items: center;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            display: inline-flex;
            height: 132px;
            justify-content: center;
            padding: 14px;
            width: 132px;
        }

        .lap-sponsor-clubs .sponsors-logo img {
            max-width: 104px;
            max-height: 104px;
            width: auto;
            height: auto;
            filter: none;
            opacity: 1;
            transition: transform .25s ease, filter .25s ease;
        }

        .lap-sponsor-clubs .sponsor-mark {
            align-items: center;
            border: 5px solid rgba(15, 23, 42, 0.38);
            border-radius: 28px;
            color: rgba(15, 23, 42, 0.58);
            display: inline-flex;
            font-size: 34px;
            font-weight: 800;
            height: 112px;
            justify-content: center;
            letter-spacing: .06em;
            width: 112px;
            transition: transform .25s ease, color .25s ease, border-color .25s ease;
        }

        .lap-sponsor-clubs .sponsor-single:hover,
        .lap-sponsor-clubs .sponsor-single:focus,
        .lap-sponsor-clubs .sponsor-single:active {
            color: inherit;
        }

        .lap-sponsor-clubs .lap-club-line {
            color: #111111;
            display: block;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
            margin-top: 14px;
            text-align: center;
            text-transform: uppercase;
        }

        .lap-sponsor-clubs .lap-club-tier {
            color: #777777;
            display: block;
            font-size: 12px;
            letter-spacing: .08em;
            margin-top: 6px;
            text-align: center;
            text-transform: uppercase;
        }

        .lap-sponsor-clubs .swiper-slide:hover .sponsors-logo img,
        .lap-sponsor-clubs .sponsor-single:focus .sponsors-logo img,
        .lap-sponsor-clubs .swiper-slide:hover .sponsor-mark,
        .lap-sponsor-clubs .sponsor-single:focus .sponsor-mark {
            transform: translateY(-4px);
        }

        .lap-sponsor-clubs .swiper-slide:hover .sponsors-logo img,
        .lap-sponsor-clubs .sponsor-single:focus .sponsors-logo img {
            filter: none;
        }

        .lap-sponsor-clubs .swiper-slide:hover .sponsor-mark,
        .lap-sponsor-clubs .sponsor-single:focus .sponsor-mark {
            border-color: rgba(15, 23, 42, 0.68);
            color: rgba(15, 23, 42, 0.8);
        }

        @media (max-width: 991.98px) {
            .lap-sponsor-clubs .section-title-area::before {
                top: -18px;
            }

            .lap-sponsor-clubs .sponsors-logo {
                min-height: 120px;
            }

            .lap-sponsor-clubs .club-logo-shell {
                height: 110px;
                width: 110px;
                padding: 12px;
            }

            .lap-sponsor-clubs .sponsors-logo img {
                max-width: 86px;
                max-height: 86px;
            }

            .lap-sponsor-clubs .sponsor-mark {
                font-size: 28px;
                height: 96px;
                width: 96px;
            }
        }

        @media (max-width: 768px) {
            .lap-sponsor-clubs.section-gap {
                padding: 60px 0;
            }

            .lap-sponsor-clubs .section-title-area {
                margin-bottom: 28px;
                padding-top: 32px;
            }

            .lap-sponsor-clubs .section-title-area::before {
                font-size: clamp(46px, 15vw, 74px);
                left: 50%;
                top: -4px;
                transform: translateX(-50%);
                width: min(100%, 360px);
                letter-spacing: .03em;
                line-height: .88;
                max-width: 100%;
                overflow-wrap: anywhere;
                text-align: center;
                white-space: normal;
            }

            .lap-sponsor-clubs .section-title-area .pretitle {
                font-size: 12px;
                letter-spacing: .16em;
            }

            .lap-sponsor-clubs .section-title-area .section-title {
                font-size: clamp(28px, 8vw, 38px) !important;
                line-height: 1.1;
                margin-bottom: 14px !important;
                margin-left: auto;
                margin-right: auto;
                max-width: 10ch;
                overflow-wrap: anywhere;
                text-wrap: balance;
            }

            .lap-sponsor-clubs .section-title-area p {
                font-size: 14px;
                line-height: 1.7;
                margin-top: 14px;
                max-width: 100%;
                padding: 0;
            }
        }

        @media (max-width: 575.98px) {
            .lap-sponsor-clubs.section-gap {
                padding: 44px 0 56px;
            }

            .lap-sponsor-clubs .section-title-area {
                margin-bottom: 22px;
                padding-top: 28px;
            }

            .lap-sponsor-clubs .section-title-area::before {
                font-size: clamp(40px, 12vw, 56px);
                left: 50%;
                letter-spacing: .02em;
                top: 0;
                transform: translateX(-50%);
                width: min(100%, 300px);
            }

            .lap-sponsor-clubs .section-title-area .section-title {
                font-size: 26px !important;
                line-height: 1.12;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $clubSlides = $featuredClubs->values();
        $minimumSlides = 8;

        if ($clubSlides->isNotEmpty() && $clubSlides->count() < $minimumSlides) {
            $clubSlides = collect(range(1, (int) ceil($minimumSlides / $clubSlides->count())))
                ->flatMap(fn () => $featuredClubs->values())
                ->take($minimumSlides)
                ->values();
        }
    @endphp

    <div class="rts-sponsors-section section-gap lap-sponsor-clubs">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">KLUB</span>
                <h1 class="section-title">KLUB PESERTA</h1>
                <p>Daftar klub peserta yang telah terdaftar dalam kompetisi ditampilkan pada halaman ini untuk memudahkan publik melihat tim yang berpartisipasi.</p>
            </div>
            @if ($clubSlides->isNotEmpty())
                <div class="sponsors-section-inner">
                    <div class="swiper lap-club-slider">
                        <div class="swiper-wrapper">
                            @foreach ($clubSlides as $club)
                                @php
                                    $logoPath = (string) $club->logo_url;
                                    $usePlaceholderMark = blank($club->logo_file_url) || str_contains($logoPath, 'demo-images/');
                                    $clubMark = strtoupper(substr($club->short_name ?: $club->name, 0, 2));
                                @endphp
                                <div class="swiper-slide">
                                    <a href="{{ route('public.clubs.show', ['clubSlug' => $club->public_slug]) }}" class="sponsor-single">
                                        <div class="sponsors-logo">
                                            @if ($usePlaceholderMark)
                                                <span class="sponsor-mark" aria-label="{{ $club->name }}">{{ $clubMark }}</span>
                                            @else
                                                <span class="club-logo-shell">
                                                    <img src="{{ $club->logo_file_url }}" alt="{{ $club->name }}">
                                                </span>
                                            @endif
                                        </div>
                                        <div class="lap-club-line">{{ $club->name }}</div>
                                        <span class="lap-club-tier">{{ $club->zone ?: ($club->short_name ?: 'KLUB PESERTA') }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="lap-summary-card">
                    <h3 class="section-title mb--20">Klub belum tersedia</h3>
                    <p class="lap-copy mb-0">Belum ada klub peserta yang dapat ditampilkan pada halaman ini.</p>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clubSlider = document.querySelector('.lap-club-slider');

            if (!clubSlider || typeof Swiper === 'undefined') {
                return;
            }

            new Swiper(clubSlider, {
                slidesPerView: 4,
                spaceBetween: 30,
                slidesPerGroup: 1,
                speed: 1000,
                loop: true,
                centeredSlides: false,
                watchOverflow: false,
                allowTouchMove: true,
                breakpoints: {
                    1200: {
                        slidesPerView: 6,
                    },
                    900: {
                        slidesPerView: 4,
                    },
                    768: {
                        slidesPerView: 3,
                    },
                    580: {
                        slidesPerView: 2,
                    },
                    0: {
                        slidesPerView: 1,
                    }
                },
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                on: {
                    init(swiper) {
                        swiper.autoplay.start();
                    }
                }
            });
        });
    </script>
@endpush

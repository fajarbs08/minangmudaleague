@extends('public.public-layout')

@php
    $homeUrl = route('public.home');
    $errorImage = asset('public-assets/img/404.png');
    $title = '404 Not Found';
    $seoTitle = '404 Not Found | Liga Anak Piaman Laweh';
    $seoDescription = 'Halaman tidak ditemukan di portal Liga Anak Piaman Laweh.';
    $seoRobots = 'noindex,nofollow';
    $showBreadcrumb = true;
    $activePublicPage = '404';
    $bannerTitle = '404 Not Found';
    $bannerCurrent = '404 Not Found';
@endphp

@push('styles')
    <style>
        .lap-error-404 {
            padding: 72px 0 24px;
            background: #ffffff;
        }

        .lap-error-404 .lap-error-card {
            max-width: 1160px;
            margin: 0 auto;
            padding: 20px 24px 48px;
            text-align: center;
        }

        .lap-error-404 .lap-error-image {
            display: block;
            width: min(100%, 760px);
            margin: 0 auto 6px;
        }

        .lap-error-404 .lap-error-title {
            margin: 0;
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            font-size: clamp(30px, 4vw, 58px);
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .lap-error-404 .lap-error-copy {
            max-width: 720px;
            margin: 18px auto 0;
            color: #5a6274;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.8;
        }

        .lap-error-404 .lap-error-actions {
            margin-top: 28px;
        }

        .lap-error-404 .theme-btn {
            min-width: 210px;
        }

        @media (max-width: 767px) {
            .lap-error-404 {
                padding-top: 52px;
            }

            .lap-error-404 .lap-error-card {
                padding: 20px 12px 36px;
            }

            .lap-error-404 .lap-error-copy {
                font-size: 16px;
            }

            .lap-error-404 .lap-error-image {
                width: min(100%, 520px);
            }
        }
    </style>
@endpush

@section('content')
    <section class="lap-error-404">
        <div class="container">
            <div class="lap-error-card">
                <img src="{{ $errorImage }}" alt="404 illustration" class="lap-error-image">
                <h2 class="lap-error-title">Oops! Halaman Tidak Ditemukan</h2>
                <p class="lap-error-copy">
                    Halaman yang Anda cari tidak tersedia atau sudah dipindahkan. Kembali ke beranda untuk melihat jadwal, hasil, klasemen, dan profil klub.
                </p>
                <div class="lap-error-actions">
                    <a href="{{ $homeUrl }}" class="theme-btn">Kembali ke Beranda <i class="fa-solid fa-arrow-up-right"></i></a>
                </div>
            </div>
        </div>
    </section>
@endsection

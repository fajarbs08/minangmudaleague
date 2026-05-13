@extends('public.public-layout', [
    'title' => 'Offline',
    'seoTitle' => 'Offline | Liga Anak Piaman Laweh',
    'seoDescription' => 'Halaman offline Liga Anak Piaman Laweh.',
    'seoRobots' => 'noindex,nofollow',
    'activePublicPage' => 'offline',
    'showBreadcrumb' => false,
])

@section('content')
    <section class="gt-error-section fix section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="gt-error-content text-center">
                        <h1>Offline</h1>
                        <h3>Koneksi internet tidak tersedia</h3>
                        <p>
                            Beberapa halaman yang pernah dibuka mungkin masih bisa diakses. Sambungkan kembali internet untuk melihat jadwal, hasil, dan klasemen terbaru.
                        </p>
                        <a href="{{ route('public.home') }}" class="gt-theme-btn">
                            Coba buka beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

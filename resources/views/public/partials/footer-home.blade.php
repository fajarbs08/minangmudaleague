@php
    $footerReferenceLogo = asset('images/logo-sm.png');
    $footerPolicyUrl = route('public.home').'#footer-kontak';
    $footerWhatsappUrl = 'https://wa.me/6282181761383';
    $footerInstagramUrl = 'https://www.instagram.com/liga.anakpariaman';
    $footerClubLinks = [
        ['label' => 'Beranda', 'url' => route('public.home')],
        ['label' => 'Jadwal Pertandingan', 'url' => route('public.schedule')],
        ['label' => 'Hasil Pertandingan', 'url' => route('public.results')],
        ['label' => 'Daftar Klub', 'url' => route('public.clubs')],
    ];
    $footerBottomLinks = [
        ['label' => 'Jadwal', 'url' => route('public.schedule')],
        ['label' => 'Hasil', 'url' => route('public.results')],
        ['label' => 'Klasemen', 'url' => route('public.standings')],
    ];
    $footerSocialLinks = [
        ['icon' => 'fab fa-whatsapp', 'url' => $footerWhatsappUrl, 'label' => 'WhatsApp'],
        ['icon' => 'fab fa-instagram', 'url' => $footerInstagramUrl, 'label' => 'Instagram'],
    ];
@endphp

<footer id="footer-kontak" class="footer-section section-padding pb-0 bg-cover lap-home-footer" style="background-image: url('{{ $homeFooterBg }}');">
    <div class="container">
        <div class="footer-subscribe-wrapper wow fadeInUp" data-wow-delay=".3s">
            <h3>
                Dapatkan Update Jadwal, <br> Hasil & Berita Resmi!
            </h3>
            <form action="#">
                <input type="text" placeholder="Masukkan email Anda" aria-label="Masukkan email Anda">
                <button class="theme-btn" type="submit">
                    daftar sekarang <i class="fa-solid fa-arrow-up-right"></i>
                </button>
            </form>
            <div class="footer-right">
                <h3>
                    Ikuti <br> kanal resmi kami
                </h3>
                <div class="social-right">
                    <div class="social-icon d-flex align-items-center">
                        @foreach ($footerSocialLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] }}"><i class="{{ $link['icon'] }}"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-widget-wrapper">
            <div class="row g-4">
                <div class="col-xl-2 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                    <div class="footer-widget-items">
                        <div class="widget-head">
                            <a href="{{ route('public.home') }}">
                                <img class="lap-footer-brand-logo" src="{{ $footerReferenceLogo }}" alt="Liga Anak Piaman Laweh">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".3s">
                    <div class="footer-widget-items lap-footer-copy-column">
                        <div class="footer-content">
                            <p class="mb-3">Portal resmi Liga Anak Piaman Laweh.</p>
                            <p>
                                Pantau jadwal pertandingan, hasil laga, klasemen, data klub, dan informasi resmi kompetisi dalam satu portal publik.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                    <div class="footer-widget-items">
                        <div class="widget-head">
                            <h3>
                                Portal
                            </h3>
                        </div>
                        <ul class="list-area">
                            @foreach ($footerClubLinks as $link)
                                <li>
                                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                    <div class="footer-widget-items">
                        <div class="widget-head">
                            <h3>
                               Kontak
                            </h3>
                        </div>
                        <ul class="footer-contact-list">
                            <li>
                                Liga Anak Piaman Laweh
                            </li>
                            <li>
                               <a href="{{ $footerWhatsappUrl }}" target="_blank" rel="noopener">082181761383</a>
                            </li>
                            <li>
                                <a href="{{ $footerInstagramUrl }}" target="_blank" rel="noopener">
                                   @liga.anakpariaman
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="wow fadeInLeft" data-wow-delay=".3s">
                © {{ now()->year }} <b><a href="https://fajarlabs.com" target="_blank" rel="noopener">Fajarlabs</a></b>. Seluruh hak cipta dilindungi.
            </p>
            <ul class="footer-menu wow fadeInRight" data-wow-delay=".5s">
                @foreach ($footerBottomLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>

<!DOCTYPE html>
<html lang="id" class="darkmode" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $resolvedSiteName = 'Liga Anak Piaman Laweh';
        $resolvedSeoTitle = $seoTitle ?? $title;
        $resolvedSeoDescription = $seoDescription ?? 'Platform resmi Liga Anak Piaman Laweh.';
        $resolvedSeoRobots = $seoRobots ?? 'index,follow';
        $resolvedSeoUrl = $seoUrl ?? url()->current();
        $resolvedSeoImage = $seoImage ?? asset('og-share-card.jpg');
        $resolvedSeoImageType = str_ends_with(strtolower(parse_url($resolvedSeoImage, PHP_URL_PATH) ?: ''), '.png') ? 'image/png' : 'image/jpeg';
        $resolvedStructuredData = collect([
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $resolvedSiteName,
                'url' => route('public.home'),
                'inLanguage' => 'id-ID',
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'SportsOrganization',
                'name' => $resolvedSiteName,
                'url' => route('public.home'),
                'logo' => asset('images/logo-full-transparent.png'),
                'image' => $resolvedSeoImage,
                'sport' => 'Soccer',
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => $seoSchemaType ?? 'WebPage',
                'name' => $resolvedSeoTitle,
                'description' => $resolvedSeoDescription,
                'url' => $resolvedSeoUrl,
                'inLanguage' => 'id-ID',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => $resolvedSiteName,
                    'url' => route('public.home'),
                ],
                'primaryImageOfPage' => $resolvedSeoImage,
            ],
        ])->merge(collect($seoStructuredData ?? []))->filter()->values();
    @endphp
    <title>{{ $resolvedSeoTitle }}</title>
    <meta name="description" content="{{ $resolvedSeoDescription }}">
    <meta name="robots" content="{{ $resolvedSeoRobots }}">
    <meta name="theme-color" content="#e41b23">
    <meta name="application-name" content="{{ $resolvedSiteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $resolvedSiteName }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="canonical" href="{{ $resolvedSeoUrl }}">
    <link rel="alternate" hreflang="id-ID" href="{{ $resolvedSeoUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $resolvedSeoUrl }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="{{ $seoType ?? 'website' }}">
    <meta property="og:site_name" content="{{ $resolvedSiteName }}">
    <meta property="og:title" content="{{ $resolvedSeoTitle }}">
    <meta property="og:description" content="{{ $resolvedSeoDescription }}">
    <meta property="og:url" content="{{ $resolvedSeoUrl }}">
    <meta property="og:image" content="{{ $resolvedSeoImage }}">
    <meta property="og:image:secure_url" content="{{ $resolvedSeoImage }}">
    <meta property="og:image:type" content="{{ $resolvedSeoImageType }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $resolvedSeoTitle }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedSeoTitle }}">
    <meta name="twitter:description" content="{{ $resolvedSeoDescription }}">
    <meta name="twitter:url" content="{{ $resolvedSeoUrl }}">
    <meta name="twitter:image" content="{{ $resolvedSeoImage }}">
    <meta name="twitter:image:alt" content="{{ $resolvedSeoTitle }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @foreach ($resolvedStructuredData as $structuredDataItem)
        <script type="application/ld+json">{!! json_encode($structuredDataItem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/rt-icons.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/animate.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/magnific.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/metisMenu.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/rtsmenu.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/variables/variable1.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('kester-assets/css/main.css') }}">
    @vite(['resources/css/public-tailwind.css'])
    <style>
        @include('public.partials.preloader-css')

        .lap-public .logo img,
        .lap-public .logo-sticky img,
        .lap-public .footer-logo img {
            max-height: 76px;
            width: auto;
        }

        .lap-public .menu-item.active1,
        .lap-public .mm-link.active {
            color: #e41b23;
        }

        .lap-public .inner-page-banner.banner-bg {
            background-image: linear-gradient(rgba(5, 8, 18, .7), rgba(5, 8, 18, .7)), url('{{ asset('kester-assets/images/banner/bannerbg-inner.jpg') }}');
        }

        .lap-public .lap-copy {
            color: #5c6271;
            line-height: 1.7;
        }

        .lap-public .lap-muted {
            color: #bfc3d0;
        }

        .lap-public .lap-stat-chip,
        .lap-public .lap-info-box {
            background: #10131f;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
        }

        .lap-public .lap-stat-chip {
            padding: 18px 20px;
            text-align: center;
        }

        .lap-public .lap-stat-chip .value {
            color: #fff;
            font-size: 30px;
            font-weight: 700;
            line-height: 1;
        }

        .lap-public .lap-stat-chip .label {
            color: #bfc3d0;
            display: block;
            font-size: 12px;
            letter-spacing: .08em;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .lap-public .lap-home-panel,
        .lap-public .lap-summary-card,
        .lap-public .lap-result-report,
        .lap-public .lap-club-note {
            background: #fff;
            border: 1px solid #ebedf2;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .06);
        }

        .lap-public .lap-home-panel,
        .lap-public .lap-summary-card,
        .lap-public .lap-result-report,
        .lap-public .lap-club-note {
            padding: 28px;
        }

        .lap-public .lap-club-card .lap-logo-frame,
        .lap-public .lap-team-stack .lap-logo-frame,
        .lap-public .lap-match-card .lap-logo-frame,
        .lap-public .lap-scoreboard .lap-logo-frame {
            border-radius: 50%;
            overflow: hidden;
        }

        .lap-public .lap-club-card .lap-logo-frame img,
        .lap-public .lap-team-stack .lap-logo-frame img,
        .lap-public .lap-match-card .lap-logo-frame img,
        .lap-public .lap-scoreboard .lap-logo-frame img {
            object-fit: cover;
        }

        .lap-public .lap-home-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lap-public .btn {
            align-items: center;
            background: #10131f;
            border: 1px solid #10131f;
            border-radius: 14px;
            box-shadow: none;
            color: #ffffff;
            display: inline-flex;
            gap: 8px;
            justify-content: center;
            letter-spacing: .08em;
            line-height: 1;
            min-height: 52px;
            padding: 14px 22px;
            text-transform: uppercase;
            transition: background .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease, transform .2s ease;
        }

        .lap-public .btn::before {
            display: none;
        }

        .lap-public .btn:hover,
        .lap-public .btn:focus {
            background: #0b1220;
            border-color: #0b1220;
            box-shadow: 0 14px 28px rgba(15, 23, 42, .12);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .lap-public .btn:focus-visible {
            outline: 2px solid rgba(228, 27, 35, .22);
            outline-offset: 2px;
        }

        .lap-public .btn.btn-primary {
            background: #e41b23;
            border-color: #e41b23;
            color: #ffffff;
        }

        .lap-public .btn.btn-primary:hover,
        .lap-public .btn.btn-primary:focus {
            background: #c9151d;
            border-color: #c9151d;
            color: #ffffff;
        }

        .lap-public .btn.btn-light {
            background: #ffffff;
            border-color: #d9e0e8;
            color: #10131f;
        }

        .lap-public .btn.btn-light:hover,
        .lap-public .btn.btn-light:focus {
            background: #f8fafc;
            border-color: #cdd5df;
            color: #10131f;
        }

        .lap-public .news-feed-section .news-right-widget .widget.widget-post .post-list li .blog-post .post-content h6 a {
            color: #10131f;
            transition: color .2s ease;
        }

        .lap-public .news-feed-section .news-right-widget .widget.widget-post .post-list li .blog-post:hover .post-content h6 a,
        .lap-public .news-feed-section .news-right-widget .widget.widget-post .post-list li .blog-post .post-content h6 a:hover {
            color: #e41b23;
        }

        .lap-public .lap-resource-nav-card {
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .lap-public .lap-resource-nav-card:hover,
        .lap-public .lap-resource-nav-card:focus {
            border-color: rgba(228, 27, 35, .14);
            box-shadow: 0 24px 54px rgba(15, 23, 42, .09);
            transform: translateY(-2px);
        }

        .lap-public .lap-resource-nav-card:hover .eyebrow,
        .lap-public .lap-resource-nav-card:focus .eyebrow {
            color: #e41b23;
        }

        .lap-public .lap-resource-nav-card:hover h5,
        .lap-public .lap-resource-nav-card:hover p,
        .lap-public .lap-resource-nav-card:focus h5,
        .lap-public .lap-resource-nav-card:focus p {
            color: #10131f;
        }

        .lap-public .rts-blog-section .blog-item .contents .blog-title,
        .lap-public .rts-blog-section .blog-item .contents .blog-title:hover {
            color: #10131f;
        }

        .lap-public .rts-blog-section .blog-item .contents .blog-title:hover {
            color: #e41b23;
        }

        .lap-public .lap-sponsor-clubs .sponsor-single:hover .lap-club-line,
        .lap-public .lap-sponsor-clubs .sponsor-single:focus .lap-club-line {
            color: #111111;
        }

        .lap-public .lap-sponsor-clubs .sponsor-single:hover .lap-sponsor-tier,
        .lap-public .lap-sponsor-clubs .sponsor-single:hover .lap-club-tier,
        .lap-public .lap-sponsor-clubs .sponsor-single:focus .lap-sponsor-tier,
        .lap-public .lap-sponsor-clubs .sponsor-single:focus .lap-club-tier {
            color: #777777;
        }

        .lap-public .lap-table-note {
            color: #6b7280;
            font-size: 14px;
            margin-top: 18px;
        }

        .lap-public .lap-result-report ul,
        .lap-public .lap-club-note ul {
            margin: 0;
            padding-left: 18px;
        }

        .lap-public .lap-result-report li,
        .lap-public .lap-club-note li {
            color: #4b5563;
            margin-bottom: 8px;
        }

        .lap-public .lap-section-space {
            padding-top: 120px;
        }

        .lap-public .lap-player-name {
            display: block;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .lap-public .lap-club-line {
            color: #bfc3d0;
            display: block;
            font-size: 13px;
            margin-top: 8px;
        }

        .lap-public .lap-blog-thumb img,
        .lap-public .player-card .image img {
            object-fit: cover;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part {
            margin-top: 20px;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner {
            align-items: center;
            column-gap: 20px;
            display: grid;
            grid-template-columns: minmax(140px, 1fr) auto minmax(140px, 1fr);
            min-height: 100px;
            padding: 0 28px;
            position: relative;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .logo,
        .lap-public #rtsHeader.rts-header1.sticky-menu .navbar-part .navbar-inner .logo-sticky {
            align-items: center;
            display: flex;
            grid-column: 1;
            justify-content: flex-start;
            margin-right: 0;
            min-width: 76px;
            position: relative;
            justify-self: start;
            z-index: 2;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .logo-sticky {
            display: none;
            margin-right: 0;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .logo img,
        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .logo-sticky img {
            max-height: 84px;
            width: auto;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .rts-menu {
            display: flex;
            grid-column: 2;
            justify-content: center;
            margin-left: 0;
            min-width: 0;
            position: relative;
            justify-self: center;
            transform: none;
            z-index: 1;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .nav__menu {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
            margin: 0;
        }

        .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .rts-menu .menu-item {
            letter-spacing: .08em;
            font-size: 13px;
            padding: 10px 14px;
        }

        .lap-public .lap-header-actions {
            align-items: center;
            display: flex;
            grid-column: 3;
            gap: 12px;
            height: 100%;
            justify-content: flex-end;
            justify-self: end;
            min-width: max-content;
            position: relative;
            z-index: 2;
        }

        .lap-public #rtsHeader.rts-header1 .hamburger-menu {
            align-items: center;
            display: none;
            flex: 0 0 40px;
            height: 40px;
            justify-content: center;
            margin-left: 0;
            padding: 0;
            position: static;
            top: auto;
            vertical-align: middle;
            width: 40px;
        }

        .lap-public #rtsHeader.rts-header1 .lap-header-actions .hamburger-menu {
            display: inline-flex !important;
        }

        .lap-public #rtsHeader.rts-header1 .hamburger-menu .hamburger-menu-inner {
            align-items: center;
            display: inline-flex;
            height: 14px;
            justify-content: center;
            width: 16px;
        }

        .lap-public .lap-page-shell.lap-page-shell-offset {
            padding-top: 124px;
        }

        .lap-public .lap-header-auth {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 999px;
            box-shadow: none;
            color: #ffffff;
            display: inline-flex;
            font-size: 13px;
            flex: 0 0 auto;
            font-weight: 700;
            gap: 8px;
            height: 40px;
            justify-content: flex-end;
            letter-spacing: .06em;
            line-height: 1;
            padding: 0;
            text-transform: uppercase;
            transition: color .2s ease, transform .2s ease, opacity .2s ease;
            vertical-align: middle;
            white-space: nowrap;
        }

        .lap-public .lap-header-auth:hover,
        .lap-public .lap-header-auth:focus {
            color: #ffffff;
            opacity: .82;
            transform: translateY(-1px);
        }

        .lap-public .lap-header-user-dropdown {
            align-items: center;
            display: flex;
            flex: 0 0 auto;
            position: relative;
            z-index: 25;
        }

        .lap-public .lap-header-user-toggle {
            align-items: center;
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 999px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .18);
            color: #ffffff;
            column-gap: 10px;
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            min-height: 44px;
            padding: 4px 12px 4px 4px;
            text-align: left;
            text-transform: uppercase;
            transition: background-color .2s ease, border-color .2s ease, opacity .2s ease;
        }

        .lap-public .lap-header-user-toggle:hover,
        .lap-public .lap-header-user-toggle:focus,
        .lap-public .lap-header-user-toggle.show {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(228, 27, 35, .4);
            box-shadow: 0 14px 32px rgba(0, 0, 0, .24);
            color: #ffffff;
            opacity: 1;
        }

        .lap-public .lap-header-user-avatar {
            align-items: center;
            background: #e41b23;
            border-radius: 999px;
            color: #ffffff;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            height: 36px;
            justify-content: center;
            overflow: hidden;
            width: 36px;
        }

        .lap-public .lap-header-user-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .lap-public .lap-header-user-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
            line-height: 1.1;
        }

        .lap-public .lap-header-user-caret {
            align-items: center;
            color: rgba(255, 255, 255, .95);
            display: inline-flex;
            font-size: 12px;
            justify-content: center;
            transition: transform .2s ease, color .2s ease;
            width: 14px;
        }

        .lap-public .lap-header-user-toggle.show .lap-header-user-caret {
            color: #ffffff;
            transform: rotate(180deg);
        }

        .lap-public .lap-header-user-name {
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: none;
            white-space: nowrap;
        }

        .lap-public .lap-header-user-role {
            color: rgba(255, 255, 255, .92);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .08em;
        }

        .lap-public .lap-header-user-menu {
            background: linear-gradient(180deg, rgba(24, 24, 28, .98) 0%, rgba(10, 10, 12, .98) 100%);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 18px;
            box-shadow: 0 22px 50px rgba(0, 0, 0, .38);
            display: none;
            left: auto;
            margin-top: 0;
            min-width: 260px;
            overflow: visible;
            padding: 10px;
            right: 0;
            top: calc(100% + 14px);
            z-index: 1200;
        }

        .lap-public .lap-header-user-menu::before {
            background: rgba(20, 20, 24, .98);
            border-left: 1px solid rgba(255, 255, 255, .08);
            border-top: 1px solid rgba(255, 255, 255, .08);
            content: '';
            height: 14px;
            position: absolute;
            right: 22px;
            top: -8px;
            transform: rotate(45deg);
            width: 14px;
        }

        .lap-public .lap-header-user-menu.show {
            display: block;
        }

        .lap-public .lap-header-user-menu-head {
            align-items: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, .06) 0%, rgba(255, 255, 255, .03) 100%);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 14px;
            display: flex;
            gap: 12px;
            margin-bottom: 10px;
            padding: 12px;
        }

        .lap-public .lap-header-user-menu-avatar {
            align-items: center;
            background: #e41b23;
            border-radius: 999px;
            color: #ffffff;
            display: inline-flex;
            flex: 0 0 42px;
            font-size: 13px;
            font-weight: 700;
            height: 42px;
            justify-content: center;
            overflow: hidden;
            width: 42px;
        }

        .lap-public .lap-header-user-menu-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .lap-public .lap-header-user-menu-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .lap-public .lap-header-user-menu-label {
            color: rgba(255, 255, 255, .56);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .14em;
            line-height: 1;
            text-transform: uppercase;
        }

        .lap-public .lap-header-user-menu-name {
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lap-public .lap-header-user-menu-role {
            color: rgba(255, 255, 255, .76);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .lap-public .lap-header-user-menu .dropdown-item {
            align-items: center;
            border-radius: 12px;
            color: rgba(255, 255, 255, .92) !important;
            display: flex !important;
            font-size: 13px;
            font-weight: 600;
            gap: 10px;
            letter-spacing: .03em;
            opacity: 1 !important;
            padding: 12px 14px;
            text-decoration: none !important;
            transition: background-color .2s ease, color .2s ease, transform .2s ease;
        }

        .lap-public .lap-header-user-menu .dropdown-item:hover,
        .lap-public .lap-header-user-menu .dropdown-item:focus {
            background: rgba(255, 255, 255, .06) !important;
            color: #ffffff !important;
            transform: translateX(2px);
        }

        .lap-public .lap-header-user-menu .dropdown-item span {
            color: inherit !important;
        }

        .lap-public .lap-header-user-menu .dropdown-item i {
            color: #ff6b63 !important;
            font-size: 13px;
            text-align: center;
            width: 16px;
        }

        .lap-public .lap-header-user-menu .dropdown-item.text-danger {
            color: #ffb3af !important;
        }

        .lap-public .lap-header-user-menu .dropdown-item.text-danger:hover,
        .lap-public .lap-header-user-menu .dropdown-item.text-danger:focus {
            background: rgba(228, 27, 35, .14) !important;
            color: #ffffff !important;
        }

        .lap-public .lap-header-user-menu .dropdown-divider {
            border-color: rgba(255, 255, 255, .08);
            margin: 8px 2px;
        }

        .lap-public .lap-header-user-menu form {
            margin: 0;
        }

        .lap-public .lap-header-user-menu form .dropdown-item {
            background: transparent !important;
            border: 0;
            text-align: left;
            width: 100%;
        }

        .lap-public .side-mobile-menu .lap-mobile-account {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            margin-bottom: 18px;
            padding-bottom: 18px;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-card {
            align-items: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, .08) 0%, rgba(255, 255, 255, .03) 100%);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 16px;
            display: flex;
            gap: 12px;
            padding: 14px;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-avatar {
            align-items: center;
            background: #e41b23;
            border-radius: 999px;
            color: #ffffff;
            display: inline-flex;
            flex: 0 0 44px;
            font-size: 13px;
            font-weight: 700;
            height: 44px;
            justify-content: center;
            overflow: hidden;
            width: 44px;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-label {
            color: rgba(255, 255, 255, .56);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .14em;
            line-height: 1;
            text-transform: uppercase;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-name {
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-role {
            color: rgba(255, 255, 255, .78);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .lap-public .side-mobile-menu ul li.lap-mobile-account-action {
            margin-bottom: 8px;
        }

        .lap-public .side-mobile-menu ul li.lap-mobile-account-action:last-child {
            margin-bottom: 0;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-link {
            align-items: center;
            appearance: none;
            background: rgba(255, 255, 255, .03) !important;
            border: 1px solid rgba(255, 255, 255, .08) !important;
            border-bottom: 0 !important;
            border-radius: 12px;
            color: rgba(255, 255, 255, .94) !important;
            display: flex !important;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            gap: 10px;
            line-height: 1.3;
            padding: 14px 16px !important;
            text-decoration: none !important;
            text-transform: uppercase;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
            -webkit-appearance: none;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-link:hover,
        .lap-public .side-mobile-menu .lap-mobile-account-link:focus {
            background: rgba(255, 255, 255, .07) !important;
            border-color: rgba(228, 27, 35, .28) !important;
            color: #ffffff !important;
            padding-left: 16px !important;
            transform: translateX(2px);
        }

        .lap-public .side-mobile-menu .lap-mobile-account-link i {
            color: #ff6b63;
            font-size: 13px;
            text-align: center;
            width: 16px;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-link span {
            color: inherit !important;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-action form {
            margin: 0;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-action button.lap-mobile-account-link {
            cursor: pointer;
            text-align: left;
            width: 100%;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-link-danger {
            color: #ffb3af !important;
        }

        .lap-public .side-mobile-menu .lap-mobile-account-link-danger:hover,
        .lap-public .side-mobile-menu .lap-mobile-account-link-danger:focus {
            background: rgba(228, 27, 35, .14) !important;
            color: #ffffff !important;
        }

        .lap-public .lap-header-auth-icon {
            align-items: center;
            color: #ffffff;
            display: flex;
            font-size: 15px;
            justify-content: center;
            line-height: 1;
        }

        .lap-public .lap-header-auth-text {
            align-items: center;
            color: inherit;
            display: flex;
            font-size: 13px;
            line-height: 1;
        }

        .lap-public .lap-author-badge {
            align-items: center;
            display: inline-flex;
            gap: 10px;
        }

        .lap-public .lap-author-avatar {
            align-items: center;
            background: #eef2f7;
            border-radius: 999px;
            color: #111827;
            display: inline-flex;
            flex-shrink: 0;
            font-size: 12px;
            font-weight: 700;
            height: 44px;
            justify-content: center;
            overflow: hidden;
            text-transform: uppercase;
            width: 44px;
        }

        .lap-public .lap-author-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .lap-public .lap-author-avatar-fallback {
            letter-spacing: .08em;
            line-height: 1;
        }

        .lap-public .player-card .image img {
            height: 360px;
            width: 100%;
        }

        .lap-public .footer-text,
        .lap-public .widget-list-item,
        .lap-public .copyright,
        .lap-public .footer-bottom-links a {
            color: rgba(255, 255, 255, 0.78);
        }

        .lap-public .copyright a {
            color: #ffffff;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .lap-public .copyright a:hover,
        .lap-public .copyright a:focus {
            color: #e41b23;
        }

        .lap-public .lap-footer-club-gallery .recent-post {
            gap: 12px;
        }

        .lap-public .lap-footer-club-gallery .recent-post .picture {
            align-items: center;
            display: flex;
            justify-content: center;
            margin-right: 0;
            width: 72px;
        }

        .lap-public .lap-footer-club-gallery .recent-post .picture a {
            align-items: center;
            display: inline-flex;
            justify-content: center;
        }

        .lap-public .lap-footer-club-gallery .recent-post .picture img {
            height: 72px;
            max-width: 100%;
            object-fit: contain;
            width: 72px;
        }

        .lap-public .lap-footer-club-gallery .recent-post .picture .lap-footer-club-badge {
            align-items: center;
            background: #ffffff;
            border-radius: 18px;
            color: #030523;
            display: inline-flex;
            font-family: 'Big Shoulders', sans-serif;
            font-size: 22px;
            font-weight: 800;
            height: 72px;
            justify-content: center;
            letter-spacing: .08em;
            line-height: 1;
            text-transform: uppercase;
            width: 72px;
        }

        @media (max-width: 991.98px) {
            .lap-public .lap-home-grid {
                grid-template-columns: 1fr;
            }

            .lap-public #rtsHeader.rts-header1 .navbar-part {
                margin-top: 16px;
            }

            .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner {
                column-gap: 16px;
                grid-template-columns: minmax(0, 1fr) auto;
                min-height: 88px;
                padding: 0 20px;
            }

            .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .rts-menu {
                display: none;
            }

            .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .logo,
            .lap-public #rtsHeader.rts-header1 .navbar-part .navbar-inner .logo-sticky {
                flex-basis: auto;
            }

            .lap-public .lap-header-actions {
                min-width: 0;
            }

            .lap-public .lap-header-user-dropdown {
                display: none;
            }

            .lap-public .lap-header-auth {
                display: none;
            }

            .lap-public #rtsHeader.rts-header1 .lap-header-actions .hamburger-menu {
                display: inline-flex;
            }

            .lap-public #rtsHeader.rts-header1 .slide-bar .hamburger-menu {
                display: inline-flex;
            }

            .lap-public .player-card .image img {
                height: 300px;
            }

            .lap-public .lap-page-shell.lap-page-shell-offset {
                padding-top: 104px;
            }

            .lap-public .lap-footer-club-gallery .recent-post {
                gap: 10px;
            }

            .lap-public .lap-footer-club-gallery .recent-post .picture {
                width: 58px;
            }

            .lap-public .lap-footer-club-gallery .recent-post .picture img {
                height: 58px;
                width: 58px;
            }

            .lap-public .lap-footer-club-gallery .recent-post .picture .lap-footer-club-badge {
                font-size: 18px;
                height: 58px;
                width: 58px;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .wow,
            .animated {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="lap-public">
    @php
        $publicCurrentUser = auth()->user();
        $publicAuthUrl = auth()->check() ? route('dashboard.home') : route('login');
        $publicAuthLabel = auth()->check() ? 'Dashboard' : 'Login';
        $publicContextUrl = request()->routeIs('public.*') ? route('dashboard.home') : route('public.home');
        $publicContextLabel = request()->routeIs('public.*') ? 'Dashboard' : 'Beranda';
    @endphp
    @include('partials.preloader')

    <div class="anywere anywere-home"></div>

    <header id="rtsHeader" class="rts-header1">
        <div class="navbar-sticky">
            <div class="navbar-part navbar-part1">
                <div class="container">
                    <div class="navbar-inner">
                        <a href="{{ route('public.home') }}" class="logo"><img src="{{ asset('images/logo-full-transparent.png') }}" alt="Liga Anak Piaman Laweh"></a>
                        <a href="{{ route('public.home') }}" class="logo-sticky"><img src="{{ asset('images/logo-full-transparent.png') }}" alt="Liga Anak Piaman Laweh"></a>
                        <div class="rts-menu">
                            <nav class="menus menu-toggle">
                                <ul class="nav__menu">
                                    <li><a class="menu-item {{ $activePublicPage === 'home' ? 'active1' : '' }}" href="{{ route('public.home') }}">Beranda</a></li>
                                    <li><a class="menu-item {{ $activePublicPage === 'schedule' ? 'active1' : '' }}" href="{{ route('public.schedule') }}">Jadwal</a></li>
                                    <li><a class="menu-item {{ $activePublicPage === 'results' ? 'active1' : '' }}" href="{{ route('public.results') }}">Hasil</a></li>
                                    <li><a class="menu-item {{ $activePublicPage === 'standings' ? 'active1' : '' }}" href="{{ route('public.standings') }}">Klasemen</a></li>
                                    <li><a class="menu-item {{ $activePublicPage === 'clubs' ? 'active1' : '' }}" href="{{ route('public.clubs') }}">Klub</a></li>
                                </ul>
                            </nav>
                        </div>
                        <div class="lap-header-actions">
                            @auth
                                <div class="dropdown lap-header-user-dropdown">
                                    <button
                                        class="lap-header-user-toggle"
                                        type="button"
                                        id="public-header-user-dropdown"
                                        aria-expanded="false"
                                    >
                                        <span class="lap-header-user-avatar">
                                            @if ($publicCurrentUser->profile_avatar_url)
                                                <img src="{{ $publicCurrentUser->profile_avatar_url }}" alt="{{ $publicCurrentUser->name }}">
                                            @else
                                                {{ $publicCurrentUser->profile_initials }}
                                            @endif
                                        </span>
                                        <span class="lap-header-user-meta">
                                            <span class="lap-header-user-name">{{ $publicCurrentUser->name }}</span>
                                            <span class="lap-header-user-role">{{ strtoupper($publicCurrentUser->role) }}</span>
                                        </span>
                                        <span class="lap-header-user-caret" aria-hidden="true">
                                            <i class="fas fa-chevron-down"></i>
                                        </span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end lap-header-user-menu" aria-labelledby="public-header-user-dropdown">
                                        <div class="lap-header-user-menu-head">
                                            <span class="lap-header-user-menu-avatar">
                                                @if ($publicCurrentUser->profile_avatar_url)
                                                    <img src="{{ $publicCurrentUser->profile_avatar_url }}" alt="{{ $publicCurrentUser->name }}">
                                                @else
                                                    {{ $publicCurrentUser->profile_initials }}
                                                @endif
                                            </span>
                                            <span class="lap-header-user-menu-meta">
                                                <span class="lap-header-user-menu-label">Akun Aktif</span>
                                                <span class="lap-header-user-menu-name">{{ $publicCurrentUser->name }}</span>
                                                <span class="lap-header-user-menu-role">{{ strtoupper($publicCurrentUser->role) }}</span>
                                            </span>
                                        </div>
                                        <a class="dropdown-item" href="{{ $publicContextUrl }}">
                                            <i class="fas {{ request()->routeIs('public.*') ? 'fa-th-large' : 'fa-home' }}"></i>
                                            <span>{{ $publicContextLabel }}</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button class="dropdown-item text-danger" type="submit">
                                                <i class="fas fa-sign-out-alt"></i>
                                                <span>Logout</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <a href="{{ $publicAuthUrl }}" class="lap-header-auth" aria-label="{{ $publicAuthLabel }}">
                                    <span class="lap-header-auth-icon">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </span>
                                    <span class="lap-header-auth-text">{{ $publicAuthLabel }}</span>
                                </a>
                            @endauth
                            <a class="hamburger-menu aitem d-block">
                                <div class="hamburger-menu-inner">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <aside class="slide-bar">
            <div class="offset-sidebar">
                <a class="hamburger-menu aitem">
                    <div class="hamburger-menu-inner">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
            </div>
            <div class="side-mobile-menu">
                <nav class="nav-main mainmenu-nav mt--30">
                    <ul class="mainmenu metismenu" id="mobile-menu-active">
                        @auth
                            <li class="lap-mobile-account">
                                <div class="lap-mobile-account-card">
                                    <span class="lap-mobile-account-avatar">
                                        @if ($publicCurrentUser->profile_avatar_url)
                                            <img src="{{ $publicCurrentUser->profile_avatar_url }}" alt="{{ $publicCurrentUser->name }}">
                                        @else
                                            {{ $publicCurrentUser->profile_initials }}
                                        @endif
                                    </span>
                                    <span class="lap-mobile-account-meta">
                                        <span class="lap-mobile-account-label">Akun Aktif</span>
                                        <span class="lap-mobile-account-name">{{ $publicCurrentUser->name }}</span>
                                        <span class="lap-mobile-account-role">{{ strtoupper($publicCurrentUser->role) }}</span>
                                    </span>
                                </div>
                            </li>
                        @endauth
                        <li><a class="mm-link {{ $activePublicPage === 'home' ? 'active' : '' }}" href="{{ route('public.home') }}">Beranda</a></li>
                        <li><a class="mm-link {{ $activePublicPage === 'schedule' ? 'active' : '' }}" href="{{ route('public.schedule') }}">Jadwal</a></li>
                        <li><a class="mm-link {{ $activePublicPage === 'results' ? 'active' : '' }}" href="{{ route('public.results') }}">Hasil</a></li>
                        <li><a class="mm-link {{ $activePublicPage === 'standings' ? 'active' : '' }}" href="{{ route('public.standings') }}">Klasemen</a></li>
                        <li><a class="mm-link {{ $activePublicPage === 'clubs' ? 'active' : '' }}" href="{{ route('public.clubs') }}">Klub</a></li>
                        @auth
                            <li class="lap-mobile-account-action">
                                <a class="mm-link lap-mobile-account-link" href="{{ $publicContextUrl }}">
                                    <i class="fas {{ request()->routeIs('public.*') ? 'fa-th-large' : 'fa-home' }}"></i>
                                    <span>{{ $publicContextLabel }}</span>
                                </a>
                            </li>
                            <li class="lap-mobile-account-action">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="mm-link lap-mobile-account-link lap-mobile-account-link-danger" type="submit">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </li>
                        @else
                            <li><a class="mm-link" href="{{ $publicAuthUrl }}">{{ $publicAuthLabel }}</a></li>
                        @endauth
                    </ul>
                </nav>
            </div>
        </aside>

        @hasSection('hero')
            @yield('hero')
        @elseif ($bannerTitle)
            <div class="banner banner1">
                <div class="inner-page-banner banner-bg">
                    <div class="container">
                        <div class="banner-content">
                            <div class="page-path">
                                <ul>
                                    <li><a class="home-page-link" href="{{ route('public.home') }}">Beranda</a></li>
                                    <li><a class="current-page" href="#">{{ strtoupper($bannerCurrent ?: $bannerTitle) }}</a></li>
                                </ul>
                            </div>
                            <h1 class="banner-heading">{{ strtoupper($bannerTitle) }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </header>

    @php
        $hasHeroSection = trim($__env->yieldContent('hero')) !== '';
        $needsContentOffset = ! $hasHeroSection && ! $bannerTitle;
    @endphp

    <main class="lap-page-shell {{ $needsContentOffset ? 'lap-page-shell-offset' : '' }}">
        @yield('content')
    </main>

    <div class="footer footer1 {{ $bannerTitle ? 'inner' : '' }}">
        <div class="container">
            <div class="footer-inner">
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="footer-widget">
                            <div class="footer-logo"><img src="{{ asset('images/logo-full-transparent.png') }}" alt="footer-logo"></div>
                            <p class="footer-text">Portal publik kompetisi yang menampilkan jadwal pertandingan, hasil laga, klasemen, daftar klub, sponsor, dan kontak panitia.</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-sm-6">
                        <div class="footer-widget">
                            <h3 class="footer-widget-title"><span class="decorator"></span> MENU PUBLIK</h3>
                            <ul class="widget-items cata-widget">
                                <li class="widget-list-item"><a href="{{ route('public.schedule') }}">JADWAL</a></li>
                                <li class="widget-list-item"><a href="{{ route('public.results') }}">HASIL</a></li>
                                <li class="widget-list-item"><a href="{{ route('public.standings') }}">KLASEMEN</a></li>
                                <li class="widget-list-item"><a href="{{ route('public.clubs') }}">KLUB</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="footer-widget address-widget" id="footer-kontak">
                            <h3 class="footer-widget-title"><span class="decorator"></span> KONTAK</h3>
                            <ul>
                                <li class="widget-list-item">
                                    <i class="fab fa-whatsapp"></i>
                                    <a href="https://wa.me/6282181761383" target="_blank" rel="noopener">082181761383</a>
                                </li>
                                <li class="widget-list-item">
                                    <i class="fab fa-instagram"></i>
                                    <a href="https://www.instagram.com/liga.anakpariaman" target="_blank" rel="noopener">@liga.anakpariaman</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="footer-widget news-widget lap-footer-club-gallery">
                            <h3 class="footer-widget-title"><span class="decorator"></span> GALERI KLUB</h3>
                            @php
                                $footerClubLogoUrl = function ($club): ?string {
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

                                $footerClubRows = [
                                    [
                                        'items' => $featuredClubs->take(3)->pad(3, null),
                                        'class' => '',
                                    ],
                                    [
                                        'items' => $featuredClubs->slice(3, 3)->pad(3, null),
                                        'class' => 'post2',
                                    ],
                                ];
                            @endphp
                            @foreach ($footerClubRows as $row)
                                <div class="recent-post {{ $row['class'] }}">
                                    @foreach ($row['items'] as $index => $club)
                                        <div class="picture">
                                            @if ($club)
                                                <a href="{{ route('public.clubs.show', ['clubSlug' => $club->public_slug]) }}">
                                                    @include('public.partials.identity-mark', ['imageUrl' => $footerClubLogoUrl($club), 'label' => $club->name, 'badgeClass' => 'lap-footer-club-badge'])
                                                </a>
                                            @else
                                                <a href="{{ route('public.clubs') }}">
                                                    @include('public.partials.identity-mark', ['imageUrl' => null, 'label' => 'Klub', 'badgeClass' => 'lap-footer-club-badge'])
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom-area">
            <div class="container">
                <div class="bottom-area-inner">
                    <span class="copyright">COPYRIGHT {{ now()->year }} &copy; <a href="https://fajarlabs.com/id/fajar-budi-setiawan" target="_blank" rel="noopener">Fajarlabs</a></span>
                    <div class="footer-bottom-links">
                        <a href="{{ route('public.home') }}">HOME</a>
                        <a href="https://wa.me/6282181761383" target="_blank" rel="noopener">WHATSAPP</a>
                        <a href="https://www.instagram.com/liga.anakpariaman" target="_blank" rel="noopener">INSTAGRAM</a>
                        <a href="{{ $publicAuthUrl }}">{{ strtoupper($publicAuthLabel) }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="scroll-top-btn scroll-top-btn1"><i class="fas fa-angle-up arrow-up"></i><i class="fas fa-circle-notch"></i></div>

    <script src="{{ asset('kester-assets/js/vendors/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/bootstrap.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/wow.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/zoom.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/timer.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/metisMenu.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/rtsmenu.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/vendors/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('kester-assets/js/main.js') }}"></script>
    <script>
        (function () {
            const dropdown = document.querySelector('.lap-header-user-dropdown');

            if (!dropdown) {
                return;
            }

            const toggle = dropdown.querySelector('.lap-header-user-toggle');
            const menu = dropdown.querySelector('.lap-header-user-menu');

            if (!toggle || !menu) {
                return;
            }

            const closeMenu = function () {
                dropdown.classList.remove('show');
                toggle.classList.remove('show');
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            };

            const openMenu = function () {
                dropdown.classList.add('show');
                toggle.classList.add('show');
                menu.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
            };

            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (menu.classList.contains('show')) {
                    closeMenu();
                    return;
                }

                openMenu();
            });

            menu.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target)) {
                    closeMenu();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>

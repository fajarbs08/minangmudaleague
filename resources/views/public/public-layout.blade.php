<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $resolvedSiteName = 'Liga Anak Piaman Laweh';
        $resolvedSeoTitle = $seoTitle ?? $title;
        $resolvedSeoDescription = $seoDescription ?? 'Platform resmi Liga Anak Piaman Laweh.';
        $resolvedSeoRobots = $seoRobots ?? 'index,follow';
        $resolvedSeoUrl = $seoUrl ?? url()->current();
        $resolvedSeoImage = $seoImage ?? asset('og-share-card.jpg');
        $resolvedSeoImageType = str_ends_with(strtolower(parse_url($resolvedSeoImage, PHP_URL_PATH) ?: ''), '.png') ? 'image/png' : 'image/jpeg';
        $publicAsset = fn (string $path) => asset('public-assets/'.$path);
        $publicMenu = [
            ['key' => 'home', 'label' => 'Beranda', 'route' => 'public.home'],
            ['key' => 'schedule', 'label' => 'Jadwal', 'route' => 'public.schedule'],
            ['key' => 'results', 'label' => 'Hasil', 'route' => 'public.results'],
            ['key' => 'brackets', 'label' => 'Bracket', 'route' => 'public.brackets'],
            ['key' => 'standings', 'label' => 'Klasemen', 'route' => 'public.standings'],
            ['key' => 'clubs', 'label' => 'Klub', 'route' => 'public.clubs'],
        ];
        $pageHeading = $bannerTitle ?? $title ?? 'Liga Anak Piaman Laweh';
        $pageCurrent = $bannerCurrent ?? $pageHeading;
        $showBreadcrumb = $showBreadcrumb ?? (($activePublicPage ?? 'home') !== 'home');
        $isHomePage = ($activePublicPage ?? 'home') === 'home';
        $resolvedBreadcrumbItems = collect($breadcrumbItems ?? [
            ['label' => 'Beranda', 'url' => route('public.home')],
            ['label' => $pageCurrent],
        ])->filter(fn (array $item) => filled($item['label'] ?? null))->values();
        $pageHeadingAccentMap = [
            'schedule' => 'Jadwal',
            'results' => 'Hasil',
            'brackets' => 'Bagan',
            'standings' => 'Klasemen',
            'clubs' => 'Klub',
        ];
        $pageHeadingAccentWord = $pageHeadingAccentWord ?? ($pageHeadingAccentMap[$activePublicPage ?? ''] ?? null);
        $pageHeadingMarkup = e($pageHeading);

        if (filled($pageHeadingAccentWord) && preg_match('/'.preg_quote($pageHeadingAccentWord, '/').'/i', $pageHeading)) {
            $pageHeadingMarkup = preg_replace(
                '/('.preg_quote($pageHeadingAccentWord, '/').')/i',
                '<span class="lap-outline-word">$1</span>',
                e($pageHeading),
                1,
            ) ?? e($pageHeading);
        }

        $currentUser = auth()->user();
        $isAuthenticated = auth()->check();
        $dashboardLabel = $isAuthenticated ? 'Dashboard' : 'Login';
        $dashboardRoute = $isAuthenticated ? route('dashboard.home') : route('login');
        $dashboardIcon = $isAuthenticated ? 'fa-th-large' : 'fa-sign-in-alt';
        $logoutLabel = 'Logout';
        $breadcrumbBg = $publicAsset('img/breadcrumb-bg.jpg');
        $footerBg = $publicAsset('img/home-3/footer-bg.jpg');
        $homeFooterBg = $publicAsset('img/home-1/footer-bg.jpg');
        $sidebarImage = $publicAsset('img/sidebar-image.jpg');
        $notificationItems = [];
        $notificationTotal = 0;
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
            $showBreadcrumb && $resolvedBreadcrumbItems->count() > 1
                ? [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => $resolvedBreadcrumbItems->values()->map(function (array $item, int $index) use ($resolvedSeoUrl) {
                        return array_filter([
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'name' => $item['label'],
                            'item' => $item['url'] ?? ($index === 0 ? route('public.home') : $resolvedSeoUrl),
                        ], fn ($value) => filled($value));
                    })->all(),
                ]
                : null,
        ])->merge(collect($seoStructuredData ?? []))->filter()->values();

        if ($isAuthenticated && $currentUser) {
            $cacheTtl = 45;

            if ($currentUser->isAdmin()) {
                $pendingCounts = \Illuminate\Support\Facades\Cache::remember('admin_notification_counts', $cacheTtl, function () {
                    return [
                        ['label' => 'Klub', 'count' => \App\Models\Club::query()->where('verification_status', \App\Models\Club::STATUS_SUBMITTED)->count(), 'route' => route('clubs.index', ['status' => 'submitted']), 'message' => 'Pengajuan klub menunggu verifikasi.'],
                        ['label' => 'Ofisial', 'count' => \App\Models\Official::query()->where('verification_status', \App\Models\Official::STATUS_SUBMITTED)->count(), 'route' => route('officials.index', ['status' => 'submitted']), 'message' => 'Data ofisial menunggu verifikasi.'],
                        ['label' => 'Pemain', 'count' => \App\Models\Player::query()->where('verification_status', \App\Models\Player::STATUS_SUBMITTED)->count(), 'route' => route('players.index', ['status' => 'submitted']), 'message' => 'Data pemain menunggu verifikasi.'],
                        ['label' => 'DSP', 'count' => \App\Models\LineupList::query()->where('verification_status', \App\Models\LineupList::STATUS_SUBMITTED)->count(), 'route' => route('lineup-lists.index', ['status' => 'submitted']), 'message' => 'DSP menunggu verifikasi.'],
                    ];
                });

                foreach ($pendingCounts as $pending) {
                    if ($pending['count'] > 0) {
                        $notificationTotal += $pending['count'];
                        $notificationItems[] = $pending;
                    }
                }
            } else {
                $club = \Illuminate\Support\Facades\Cache::remember("club_notification_owner_{$currentUser->id}", $cacheTtl, function () use ($currentUser) {
                    return \App\Models\Club::query()->select(['id', 'verification_status'])->where('user_id', $currentUser->id)->first();
                });

                if ($club) {
                    $needsAttentionStatuses = [
                        \App\Models\Club::STATUS_REVISION,
                        \App\Models\Club::STATUS_REJECTED,
                    ];

                    if (in_array($club->verification_status, $needsAttentionStatuses, true)) {
                        $notificationTotal += 1;
                        $notificationItems[] = [
                            'label' => 'Klub',
                            'count' => 1,
                            'route' => route('clubs.show', $club),
                            'message' => 'Profil klub perlu perbaikan sebelum diverifikasi.',
                        ];
                    }

                    $clubId = $club->id;
                    $clubCounts = \Illuminate\Support\Facades\Cache::remember("club_notification_counts_{$clubId}", $cacheTtl, function () use ($clubId, $needsAttentionStatuses) {
                        return [
                            ['label' => 'Ofisial', 'count' => \App\Models\Official::query()->where('club_id', $clubId)->whereIn('verification_status', $needsAttentionStatuses)->count(), 'route' => route('officials.index', ['status' => 'revision']), 'message' => 'Beberapa ofisial perlu revisi.'],
                            ['label' => 'Pemain', 'count' => \App\Models\Player::query()->where('club_id', $clubId)->whereIn('verification_status', $needsAttentionStatuses)->count(), 'route' => route('players.index', ['status' => 'revision']), 'message' => 'Beberapa pemain perlu revisi.'],
                            ['label' => 'DSP', 'count' => \App\Models\LineupList::query()->where('club_id', $clubId)->whereIn('verification_status', $needsAttentionStatuses)->count(), 'route' => route('lineup-lists.index', ['status' => 'revision']), 'message' => 'Beberapa DSP perlu revisi.'],
                        ];
                    });

                    foreach ($clubCounts as $pending) {
                        if ($pending['count'] > 0) {
                            $notificationTotal += $pending['count'];
                            $notificationItems[] = $pending;
                        }
                    }
                }
            }
        }
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
    <link rel="stylesheet" href="{{ $publicAsset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/all.min.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/meanmenu.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/main.css') }}">
    <link rel="stylesheet" href="{{ $publicAsset('css/lap-custom.css') }}">
    <style>
        @include('public.partials.preloader-css')

        .lap-public .lap-brand-heading {
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-public .lap-brand-heading .lap-outline-word {
            color: transparent;
            display: inline-block;
            paint-order: stroke fill;
            -webkit-text-fill-color: transparent;
            -webkit-text-stroke-width: 2px;
            -webkit-text-stroke-color: var(--theme);
            text-shadow: 0 8px 18px rgba(16, 19, 31, .08);
        }

        .lap-public .gt-page-heading .lap-brand-heading .lap-outline-word {
            text-shadow: 0 12px 28px rgba(3, 5, 35, .22);
        }

        .lap-public .gt-breadcrumb-wrapper .gt-page-heading .gt-breadcrumb-items {
            flex-wrap: wrap;
            gap: 10px 12px;
        }

        .lap-public .gt-breadcrumb-wrapper .gt-page-heading .gt-breadcrumb-items li {
            color: rgba(255, 255, 255, .92);
            text-shadow: 0 2px 10px rgba(3, 5, 35, .3);
        }

        .lap-public .gt-breadcrumb-wrapper .gt-page-heading .gt-breadcrumb-items li a {
            color: #ffffff;
            opacity: 1;
        }

        .lap-public .gt-breadcrumb-wrapper .gt-page-heading .gt-breadcrumb-items li a:hover {
            color: var(--theme);
        }

        .lap-public .gt-breadcrumb-wrapper .gt-page-heading .gt-breadcrumb-items li:last-child {
            color: var(--theme);
            font-weight: 700;
        }

        .lap-public .gt-breadcrumb-wrapper .gt-page-heading .gt-breadcrumb-items li i {
            color: rgba(255, 255, 255, .78);
        }

        @media (prefers-reduced-motion: reduce) {
            .wow,
            .animated {
                animation: none !important;
                transition: none !important;
            }

            .lap-public .lap-auth-link,
            .lap-public .lap-header-user-toggle,
            .lap-public .lap-offcanvas-auth-link,
            .lap-public .lap-offcanvas-auth-button {
                transition: none !important;
            }
        }

        .lap-public .lap-auth-link {
            align-items: center;
            background: none;
            border: 0;
            color: inherit;
            display: inline-flex;
            gap: 10px;
            line-height: 1;
            padding: 8px 0;
            position: relative;
            text-decoration: none;
            text-transform: uppercase;
            transition: color .18s ease, opacity .18s ease, transform .18s ease;
        }

        .lap-public .lap-auth-link::after {
            content: "";
            position: absolute;
            left: 32px;
            right: 0;
            bottom: 0;
            height: 1px;
            background: currentColor;
            opacity: 0.28;
            transform: scaleX(0.38);
            transform-origin: left center;
            transition: transform .18s ease, opacity .18s ease;
        }

        .lap-public .lap-auth-link-icon {
            align-items: center;
            border: 1px solid currentColor;
            border-radius: 999px;
            display: inline-flex;
            flex: 0 0 auto;
            height: 22px;
            justify-content: center;
            width: 22px;
        }

        .lap-public .lap-auth-link-icon i {
            font-size: 11px;
            margin: 0;
        }

        .lap-public .lap-auth-link-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.16em;
            white-space: nowrap;
        }

        .lap-public .lap-auth-link:hover {
            transform: translateY(-1px);
        }

        .lap-public .lap-auth-link:hover::after,
        .lap-public .lap-auth-link:focus-visible::after {
            opacity: 0.55;
            transform: scaleX(1);
        }

        .lap-public .lap-auth-link:focus-visible,
        .lap-public .lap-offcanvas-auth-link:focus-visible,
        .lap-public .lap-offcanvas-auth-button:focus-visible {
            outline: 3px solid rgba(254, 89, 0, 0.22);
            outline-offset: 3px;
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
            transition: background-color .2s ease, border-color .2s ease, opacity .2s ease, color .2s ease, box-shadow .2s ease;
        }

        .lap-public .lap-header-user-toggle.has-notifications {
            padding-right: 14px;
        }

        .lap-public .lap-header-user-toggle:hover,
        .lap-public .lap-header-user-toggle:focus,
        .lap-public .lap-header-user-toggle.show {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(254, 89, 0, .36);
            box-shadow: 0 14px 32px rgba(0, 0, 0, .24);
            color: #ffffff;
            opacity: 1;
        }

        .lap-public .lap-header-user-avatar {
            align-items: center;
            background: var(--lap-red);
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
            color: rgba(255, 255, 255, .9);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .08em;
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

        .lap-public .lap-header-user-alert {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .1);
            color: #ffffff;
            flex: 0 0 auto;
        }

        .lap-public .lap-header-user-alert i {
            font-size: 12px;
        }

        .lap-public .lap-header-user-alert-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: var(--theme);
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 18px;
            text-align: center;
            box-shadow: 0 0 0 3px rgba(3, 5, 35, .28);
        }

        .lap-public #header-sticky.sticky .lap-header-user-alert {
            background: rgba(13, 47, 103, .08);
            color: var(--lap-ink);
        }

        .lap-public #header-sticky.sticky .lap-header-user-alert-badge {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .92);
        }

        .lap-public .lap-header-user-menu {
            background: linear-gradient(180deg, rgba(24, 24, 28, .98) 0%, rgba(10, 10, 12, .98) 100%);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 18px;
            box-shadow: 0 22px 50px rgba(0, 0, 0, .38);
            margin-top: 14px;
            min-width: 260px;
            overflow: visible;
            padding: 10px;
        }

        .lap-public .lap-header-user-menu::before {
            background: rgba(20, 20, 24, .98);
            border-left: 1px solid rgba(255, 255, 255, .08);
            border-top: 1px solid rgba(255, 255, 255, .08);
            content: "";
            height: 14px;
            position: absolute;
            right: 22px;
            top: -8px;
            transform: rotate(45deg);
            width: 14px;
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

        .lap-public .lap-header-user-menu-notice {
            margin: 2px 2px 10px;
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .04) 0%, rgba(255, 255, 255, .02) 100%);
        }

        .lap-public .lap-header-user-menu-notice-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .lap-public .lap-header-user-menu-notice-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .lap-public .lap-header-user-menu-notice-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 999px;
            background: rgba(254, 89, 0, .16);
            color: #ffd0b6;
            font-size: 11px;
            font-weight: 900;
            line-height: 1;
        }

        .lap-public .lap-header-user-menu-notice-list {
            display: grid;
            gap: 8px;
        }

        .lap-public .lap-header-user-menu-notice-item {
            display: block;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .04);
            color: rgba(255, 255, 255, .92);
            text-decoration: none;
            transition: background-color .2s ease, transform .2s ease;
        }

        .lap-public .lap-header-user-menu-notice-item:hover,
        .lap-public .lap-header-user-menu-notice-item:focus-visible {
            background: rgba(255, 255, 255, .07);
            transform: translateX(2px);
            outline: none;
        }

        .lap-public .lap-header-user-menu-notice-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
        }

        .lap-public .lap-header-user-menu-notice-label i {
            color: #ff6b63;
            font-size: 12px;
        }

        .lap-public .lap-header-user-menu-notice-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            margin-left: auto;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
        }

        .lap-public .lap-header-user-menu-notice-copy {
            color: rgba(255, 255, 255, .68);
            font-size: 12px;
            line-height: 1.45;
        }

        .lap-public .lap-header-user-menu-notice-empty {
            color: rgba(255, 255, 255, .66);
            font-size: 12px;
            line-height: 1.5;
        }

        .lap-public .lap-header-user-menu-avatar {
            align-items: center;
            background: var(--lap-red);
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
            background: transparent;
            border: 0;
            border-radius: 12px;
            color: rgba(255, 255, 255, .92) !important;
            display: flex !important;
            font-size: 13px;
            font-weight: 600;
            gap: 10px;
            letter-spacing: .03em;
            opacity: 1 !important;
            padding: 12px 14px;
            text-align: left;
            text-decoration: none !important;
            transition: background-color .2s ease, color .2s ease, transform .2s ease;
            width: 100%;
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

        .lap-public #header-sticky.sticky .lap-header-user-toggle {
            background: #ffffff;
            border-color: rgba(3, 5, 35, .1);
            box-shadow: 0 14px 30px rgba(3, 5, 35, .12);
            color: var(--lap-ink);
        }

        .lap-public #header-sticky.sticky .lap-header-user-toggle:hover,
        .lap-public #header-sticky.sticky .lap-header-user-toggle:focus,
        .lap-public #header-sticky.sticky .lap-header-user-toggle.show {
            background: #ffffff;
            border-color: rgba(254, 89, 0, .28);
            color: var(--lap-ink);
        }

        .lap-public #header-sticky.sticky .lap-header-user-name,
        .lap-public #header-sticky.sticky .lap-header-user-caret {
            color: var(--lap-ink);
        }

        .lap-public #header-sticky.sticky .lap-header-user-role {
            color: rgba(3, 5, 35, .64);
        }

        .lap-public .lap-home-header .lap-auth-link {
            color: #fff;
        }

        .lap-public #header-sticky.sticky .lap-auth-link {
            color: var(--lap-ink);
        }

        .lap-public .lap-home-header .lap-auth-link:hover,
        .lap-public .lap-home-header .lap-auth-link:focus-visible {
            color: rgba(255, 255, 255, 0.84);
        }

        .lap-public #header-sticky.sticky .lap-auth-link:hover,
        .lap-public #header-sticky.sticky .lap-auth-link:focus-visible {
            color: var(--lap-red);
        }

        .lap-public .lap-offcanvas-auth {
            border-top: 1px solid rgba(3, 5, 35, 0.1);
            margin-top: 24px;
            padding-top: 18px;
        }

        .lap-public .lap-offcanvas-account {
            align-items: center;
            border-bottom: 1px solid rgba(3, 5, 35, 0.08);
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 16px;
        }

        .lap-public .lap-offcanvas-account-avatar {
            align-items: center;
            background: var(--lap-red);
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

        .lap-public .lap-offcanvas-account-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .lap-public .lap-offcanvas-account-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .lap-public .lap-offcanvas-account-label {
            color: #8b97a8;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .14em;
            line-height: 1;
            text-transform: uppercase;
        }

        .lap-public .lap-offcanvas-account-name {
            color: var(--lap-ink);
            display: -webkit-box;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
        }

        .lap-public .lap-offcanvas-account-role {
            color: #6f7b8d;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .lap-public .lap-offcanvas-account-notice {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            color: #6f7b8d;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .lap-public .lap-offcanvas-account-notice i {
            color: var(--lap-red);
        }

        .lap-public .lap-offcanvas-account-notice-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: rgba(254, 89, 0, .12);
            color: var(--lap-red);
            font-size: 10px;
            font-weight: 900;
            line-height: 1;
        }

        .lap-public .lap-offcanvas-auth-label {
            color: #6c7789;
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .lap-public .lap-offcanvas-auth-links {
            display: grid;
            gap: 10px;
        }

        .lap-public .lap-offcanvas-auth-form {
            margin: 0;
        }

        .lap-public .lap-offcanvas-auth-link,
        .lap-public .lap-offcanvas-auth-button {
            align-items: center;
            background: none;
            border: 0;
            color: var(--lap-ink);
            cursor: pointer;
            display: grid;
            font: inherit;
            gap: 12px;
            grid-template-columns: auto minmax(0, 1fr) auto;
            line-height: 1;
            padding: 8px 0 4px;
            text-align: left;
            text-decoration: none;
            width: 100%;
            transition: color .18s ease, transform .18s ease;
        }

        .lap-public .lap-offcanvas-auth-link:hover,
        .lap-public .lap-offcanvas-auth-button:hover {
            color: var(--lap-red);
            transform: translateX(2px);
        }

        .lap-public .lap-offcanvas-auth-icon {
            align-items: center;
            background: rgba(254, 89, 0, 0.09);
            border-radius: 999px;
            color: var(--lap-red);
            display: inline-flex;
            flex: 0 0 auto;
            height: 34px;
            justify-content: center;
            width: 34px;
        }

        .lap-public .lap-offcanvas-auth-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .lap-public .lap-offcanvas-auth-title {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .lap-public .lap-offcanvas-auth-meta {
            color: #7a8698;
            font-size: 12px;
            line-height: 1.35;
        }

        .lap-public .lap-offcanvas-auth-link:hover .lap-offcanvas-auth-meta,
        .lap-public .lap-offcanvas-auth-button:hover .lap-offcanvas-auth-meta {
            color: rgba(254, 89, 0, 0.72);
        }

        .lap-public .lap-offcanvas-auth-arrow {
            color: rgba(3, 5, 35, 0.36);
            font-size: 13px;
        }

        @media (max-width: 1399px) {
            .lap-public .lap-auth-link-label {
                font-size: 10px;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="lap-public {{ ($activePublicPage ?? 'home') === 'home' ? 'is-home' : 'is-inner' }}">
    @include('partials.preloader')

    <button id="back-top" class="back-to-top">
        <i class="fa-regular fa-arrow-up"></i>
    </button>

    @if ($isHomePage)
        <div class="mouseCursor cursor-outer"></div>
        <div class="mouseCursor cursor-inner"></div>
    @endif

    <div class="fix-area">
        <div class="offcanvas__info">
            <div class="offcanvas__wrapper">
                <div class="offcanvas__content">
                    <div class="offcanvas__top mb-5 d-flex justify-content-between align-items-center">
                        <div class="offcanvas__logo">
                            <a href="{{ route('public.home') }}">
                                <img class="lap-logo-default" src="{{ asset('images/logo-full-transparent.png') }}" alt="Liga Anak Piaman Laweh">
                            </a>
                        </div>
                        <div class="offcanvas__close">
                            <button type="button" aria-label="Tutup menu">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text d-none d-xl-block">
                        Portal publik Liga Anak Piaman Laweh untuk memantau jadwal pertandingan, hasil resmi, klasemen, klub peserta, dan sponsor kompetisi.
                    </p>
                    <div class="mobile-menu fix mb-3"></div>
                    <div class="sidebar-image mt-4 d-none d-xl-block">
                        <img class="w-100" src="{{ $sidebarImage }}" alt="Portal publik Liga Anak Piaman Laweh">
                    </div>
                    <div class="offcanvas__contact">
                        <h4>Jelajahi Portal</h4>
                        <ul class="lap-offcanvas-nav d-none d-xl-block">
                            @foreach ($publicMenu as $item)
                                <li>
                                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="lap-offcanvas-auth">
                            @auth
                                <div class="lap-offcanvas-account">
                                    <span class="lap-offcanvas-account-avatar">
                                        @if ($currentUser?->profile_avatar_url)
                                            <img src="{{ $currentUser->profile_avatar_url }}" alt="{{ $currentUser->name }}">
                                        @else
                                            {{ $currentUser?->profile_initials }}
                                        @endif
                                    </span>
                                    <span class="lap-offcanvas-account-meta">
                                        <span class="lap-offcanvas-account-label">Akun Aktif</span>
                                        <span class="lap-offcanvas-account-name">{{ $currentUser?->name }}</span>
                                        <span class="lap-offcanvas-account-role">{{ strtoupper($currentUser?->role ?? '') }}</span>
                                        <span class="lap-offcanvas-account-notice">
                                            <i class="fas fa-bell"></i>
                                            <span>Notifikasi</span>
                                            @if ($notificationTotal > 0)
                                                <span class="lap-offcanvas-account-notice-badge">{{ min($notificationTotal, 99) }}</span>
                                            @endif
                                        </span>
                                    </span>
                                </div>
                            @endauth
                            <span class="lap-offcanvas-auth-label">Portal Akun</span>
                            <div class="lap-offcanvas-auth-links">
                                <a href="{{ $dashboardRoute }}" class="lap-offcanvas-auth-link" aria-label="{{ $dashboardLabel }}" title="{{ $dashboardLabel }}">
                                    <span class="lap-offcanvas-auth-icon" aria-hidden="true">
                                        <i class="fas {{ $dashboardIcon }}"></i>
                                    </span>
                                    <span class="lap-offcanvas-auth-copy">
                                        <span class="lap-offcanvas-auth-title">{{ $dashboardLabel }}</span>
                                        <span class="lap-offcanvas-auth-meta">{{ $isAuthenticated ? 'Buka panel akun dan pengelolaan data' : 'Masuk ke akun panitia atau klub' }}</span>
                                    </span>
                                    <span class="lap-offcanvas-auth-arrow" aria-hidden="true">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                </a>
                                @auth
                                    <form method="POST" action="{{ route('logout') }}" class="lap-offcanvas-auth-form">
                                        @csrf
                                        <button type="submit" class="lap-offcanvas-auth-button" aria-label="{{ $logoutLabel }}" title="{{ $logoutLabel }}">
                                            <span class="lap-offcanvas-auth-icon" aria-hidden="true">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </span>
                                            <span class="lap-offcanvas-auth-copy">
                                                <span class="lap-offcanvas-auth-title">{{ $logoutLabel }}</span>
                                                <span class="lap-offcanvas-auth-meta">Keluar dari sesi akun pada perangkat ini</span>
                                            </span>
                                            <span class="lap-offcanvas-auth-arrow" aria-hidden="true">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </span>
                                        </button>
                                    </form>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas__overlay"></div>

    @include('public.partials.header-home')

    @if ($showBreadcrumb)
        <div class="gt-breadcrumb-wrapper bg-cover" style="background-image: url('{{ $breadcrumbBg }}');">
            <div class="container">
                <div class="gt-page-heading">
                    <div class="gt-breadcrumb-sub-title">
                        <h1 class="text-white lap-brand-heading hero_title tv_hero_title hero_title_1">{!! $pageHeadingMarkup !!}</h1>
                    </div>
                    <ul class="gt-breadcrumb-items wow fadeInUp" data-wow-delay=".4s">
                        @foreach ($resolvedBreadcrumbItems as $item)
                            <li>
                                @if (! $loop->last && filled($item['url'] ?? null))
                                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                                @else
                                    {{ $item['label'] }}
                                @endif
                            </li>
                            @if (! $loop->last)
                                <li><i class="fa-solid fa-chevron-right"></i></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @yield('content')

    @include('public.partials.footer-home')

    <script src="{{ $publicAsset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ $publicAsset('js/viewport.jquery.js') }}"></script>
    <script src="{{ $publicAsset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ $publicAsset('js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ $publicAsset('js/jquery.waypoints.js') }}"></script>
    <script src="{{ $publicAsset('js/jquery.counterup.min.js') }}"></script>
    <script src="{{ $publicAsset('js/swiper-bundle.min.js') }}"></script>
    <script src="{{ $publicAsset('js/jquery.meanmenu.min.js') }}"></script>
    <script src="{{ $publicAsset('js/parallaxie.js') }}"></script>
    <script src="{{ $publicAsset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ $publicAsset('js/wow.min.js') }}"></script>
    <script src="{{ $publicAsset('js/gsap.min.js') }}"></script>
    <script src="{{ $publicAsset('js/ScrollTrigger.min.js') }}"></script>
    <script src="{{ $publicAsset('js/SplitText.min.js') }}"></script>
    <script src="{{ $publicAsset('js/splitType.js') }}"></script>
    <script src="{{ $publicAsset('js/main.js') }}"></script>
    <script>
        (function () {
            const preloader = document.getElementById('rts__preloader');

            if (!preloader) {
                return;
            }

            const hidePreloader = function () {
                preloader.classList.add('is-hidden');
                window.setTimeout(function () {
                    preloader.remove();
                }, 500);
            };

            if (document.readyState === 'complete') {
                hidePreloader();
                return;
            }

            window.addEventListener('load', hidePreloader, { once: true });
        })();
    </script>
    @stack('scripts')
</body>
</html>

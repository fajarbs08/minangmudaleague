<header id="header-sticky" class="header-1 lap-home-header">
    <div class="container">
        <div class="mega-menu-wrapper">
            <div class="header-main">
                <a href="{{ route('public.home') }}" class="logo">
                    <img class="lap-logo-default" src="{{ asset('images/logo-full-transparent.png') }}" alt="Liga Anak Piaman Laweh">
                    <img class="lap-logo-sticky" src="{{ asset('images/logo-dark.png') }}" alt="Liga Anak Piaman Laweh">
                </a>
                <div class="header-left">
                    <div class="mean__menu-wrapper">
                        <div class="main-menu">
                            <nav id="mobile-menu">
                                <ul>
                                    @foreach ($publicMenu as $item)
                                        <li class="{{ ($activePublicPage ?? 'home') === $item['key'] ? 'active' : '' }}">
                                            <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="header-right d-flex justify-content-end align-items-center">
                    @auth
                        <div class="dropdown lap-header-user-dropdown d-none d-xl-flex">
                            <button
                                class="lap-header-user-toggle {{ $notificationTotal > 0 ? 'has-notifications' : '' }}"
                                type="button"
                                id="public-header-user-dropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <span class="lap-header-user-avatar">
                                    @if ($currentUser?->profile_avatar_url)
                                        <img src="{{ $currentUser->profile_avatar_url }}" alt="{{ $currentUser->name }}">
                                    @else
                                        {{ $currentUser?->profile_initials }}
                                    @endif
                                </span>
                                <span class="lap-header-user-meta d-none d-xxl-flex">
                                    <span class="lap-header-user-name">{{ $currentUser?->name }}</span>
                                    <span class="lap-header-user-role">{{ strtoupper($currentUser?->role ?? '') }}</span>
                                </span>
                                <span class="lap-header-user-alert" aria-hidden="true">
                                    <i class="fas fa-bell"></i>
                                    @if ($notificationTotal > 0)
                                        <span class="lap-header-user-alert-badge">{{ min($notificationTotal, 99) }}</span>
                                    @endif
                                </span>
                                <span class="lap-header-user-caret" aria-hidden="true">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end lap-header-user-menu" aria-labelledby="public-header-user-dropdown">
                                <div class="lap-header-user-menu-head">
                                    <span class="lap-header-user-menu-avatar">
                                        @if ($currentUser?->profile_avatar_url)
                                            <img src="{{ $currentUser->profile_avatar_url }}" alt="{{ $currentUser->name }}">
                                        @else
                                            {{ $currentUser?->profile_initials }}
                                        @endif
                                    </span>
                                    <span class="lap-header-user-menu-meta">
                                        <span class="lap-header-user-menu-label">Akun Aktif</span>
                                        <span class="lap-header-user-menu-name">{{ $currentUser?->name }}</span>
                                        <span class="lap-header-user-menu-role">{{ strtoupper($currentUser?->role ?? '') }}</span>
                                    </span>
                                </div>
                                <div class="lap-header-user-menu-notice">
                                    <div class="lap-header-user-menu-notice-head">
                                        <span class="lap-header-user-menu-notice-title">
                                            <i class="fas fa-bell"></i>
                                            Notifikasi
                                        </span>
                                        <span class="lap-header-user-menu-notice-count">{{ min($notificationTotal, 99) }}</span>
                                    </div>
                                    <div class="lap-header-user-menu-notice-list">
                                        @forelse ($notificationItems as $item)
                                            <a class="lap-header-user-menu-notice-item" href="{{ $item['route'] }}">
                                                <span class="lap-header-user-menu-notice-label">
                                                    <i class="fas fa-circle-exclamation"></i>
                                                    <span>{{ $item['label'] }}</span>
                                                    <span class="lap-header-user-menu-notice-badge">{{ $item['count'] }}</span>
                                                </span>
                                                <span class="lap-header-user-menu-notice-copy">{{ $item['message'] }}</span>
                                            </a>
                                        @empty
                                            <div class="lap-header-user-menu-notice-empty">Tidak ada notifikasi baru.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <a class="dropdown-item" href="{{ $dashboardRoute }}">
                                    <i class="fas {{ $dashboardIcon }}"></i>
                                    <span>{{ $dashboardLabel }}</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>{{ $logoutLabel }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ $dashboardRoute }}" class="lap-auth-link d-none d-xl-inline-flex" aria-label="{{ $dashboardLabel }}" title="{{ $dashboardLabel }}">
                            <span class="lap-auth-link-icon" aria-hidden="true">
                                <i class="fas {{ $dashboardIcon }}"></i>
                            </span>
                            <span class="lap-auth-link-label">{{ $dashboardLabel }}</span>
                        </a>
                    @endauth
                    <div class="header__hamburger d-xl-block my-auto">
                        <button type="button" class="sidebar__toggle" aria-label="Buka menu navigasi">
                            <div class="header-bar">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

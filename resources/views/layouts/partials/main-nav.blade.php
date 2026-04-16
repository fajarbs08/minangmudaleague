<div class="main-nav">
     @php
          $adminPendingCounts = auth()->user()->isAdmin()
               ? \Illuminate\Support\Facades\Cache::remember('admin_sidebar_counts', 45, function () {
                    return [
                         'clubs' => \App\Models\Club::query()->where('verification_status', \App\Models\Club::STATUS_SUBMITTED)->count(),
                         'officials' => \App\Models\Official::query()->where('verification_status', \App\Models\Official::STATUS_SUBMITTED)->count(),
                         'players' => \App\Models\Player::query()->where('verification_status', \App\Models\Player::STATUS_SUBMITTED)->count(),
                         'lineups' => \App\Models\LineupList::query()->where('verification_status', \App\Models\LineupList::STATUS_SUBMITTED)->count(),
                    ];
               })
               : [];
     @endphp
     <style>
          .main-nav .main-logo-box {
               justify-content: center;
               padding: 0 16px;
               position: relative;
          }

          .main-nav .main-logo-box .button-toggle-menu {
               position: absolute;
               right: 12px;
               top: 50%;
               width: 40px;
               height: 40px;
               align-items: center;
               justify-content: center;
               transform: translateY(-50%);
          }

          html.sidebar-hover .main-nav:not(:hover) .main-logo-box .button-toggle-menu,
          html[data-sidenav-size="condensed"] .main-nav .main-logo-box .button-toggle-menu {
               left: 50%;
               right: auto;
               transform: translate(-50%, -50%);
          }

          .main-nav .logo-box {
               width: 100%;
               display: flex;
               justify-content: center;
               align-items: center;
               text-align: center;
          }

          .main-nav .logo-box .logo-dark,
          .main-nav .logo-box .logo-light {
               width: 100%;
               justify-content: center;
               align-items: center;
          }

          .main-nav .logo-box .logo-dark {
               display: flex;
          }

          .main-nav .logo-box .logo-light {
               display: none;
          }

          html[data-bs-theme="dark"] .main-nav .logo-box .logo-dark,
          .sidebar-dark .main-nav .logo-box .logo-dark {
               display: none;
          }

          html[data-bs-theme="dark"] .main-nav .logo-box .logo-light,
          .sidebar-dark .main-nav .logo-box .logo-light {
               display: flex;
          }

          .main-nav .logo-box .logo-sm {
               display: none;
          }

          html[data-sidenav-size="default"] .main-nav .logo-box .logo-lg,
          html.sidebar-enable .main-nav .logo-box .logo-lg {
               display: block;
          }

          html[data-sidenav-size="default"] .main-nav .logo-box .logo-sm,
          html.sidebar-enable .main-nav .logo-box .logo-sm {
               display: none;
          }

          html[data-sidenav-size="condensed"] .main-nav .logo-box .logo-lg,
          html[data-sidenav-size="hover"] .main-nav:not(:hover) .logo-box .logo-lg {
               display: none;
          }

          html[data-sidenav-size="condensed"] .main-nav .logo-box .logo-sm,
          html[data-sidenav-size="hover"] .main-nav:not(:hover) .logo-box .logo-sm {
               display: block;
          }
     </style>
     <div class="d-flex justify-content-between main-logo-box">
          <div class="logo-box">
               <a href="{{ route('dashboard.index') }}" class="logo-dark">
                    <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm" style="height: 42px; width: auto;">
                    <img src="/images/logo-full-transparent.png" class="logo-lg" alt="logo dark" style="height: 68px; width: auto; margin-left: 0;">
               </a>

               <a href="{{ route('dashboard.index') }}" class="logo-light">
                    <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm" style="height: 42px; width: auto;">
                    <img src="/images/logo-full-transparent.png" class="logo-lg" alt="logo light" style="height: 68px; width: auto; margin-left: 0;">
               </a>
          </div>
          <button type="button" class="btn btn-link d-flex button-sm-hover button-toggle-menu" aria-label="Show Full Sidebar">
               <i data-lucide="menu" class="button-sm-hover-icon"></i>
          </button>
     </div>

     <div class="h-100" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <li class="menu-title">Fitur Aplikasi</li>

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('dashboard.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="house"></i>
                         </span>
                         <span class="nav-text">Dashboard</span>
                    </a>
               </li>

               @if (auth()->user()->isAdmin())
               <li class="menu-item">
                    <a class="menu-link" href="#sidebarAccountManagement" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAccountManagement">
                         <span class="nav-icon">
                              <i data-lucide="users"></i>
                         </span>
                         <span class="nav-text">Manajemen Akun</span>
                         <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                    </a>
                    <div class="collapse" id="sidebarAccountManagement">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('admin-accounts.index') }}">Akun Admin</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link" href="{{ route('club-accounts.create') }}">Akun Club</a>
                              </li>
                         </ul>
                    </div>
               </li>
               @endif

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('clubs.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="shield"></i>
                         </span>
                         <span class="nav-text">Klub</span>
                         @if (auth()->user()->isAdmin() && $adminPendingCounts['clubs'] > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto">{{ $adminPendingCounts['clubs'] }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('officials.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="users-round"></i>
                         </span>
                         <span class="nav-text">Official</span>
                         @if (auth()->user()->isAdmin() && $adminPendingCounts['officials'] > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto">{{ $adminPendingCounts['officials'] }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('players.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="user-round"></i>
                         </span>
                         <span class="nav-text">Pemain</span>
                         @if (auth()->user()->isAdmin() && $adminPendingCounts['players'] > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto">{{ $adminPendingCounts['players'] }}</span>
                         @endif
                    </a>
               </li>

               @if (auth()->user()->isAdmin())
               <li class="menu-item">
                    <a class="menu-link" href="{{ route('matches.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="calendar-range"></i>
                         </span>
                         <span class="nav-text">Jadwal Match</span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('information-resources.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="folder-kanban"></i>
                         </span>
                         <span class="nav-text">Pusat Informasi</span>
                    </a>
               </li>
               @endif

               <li class="menu-item">
                    <a class="menu-link" href="{{ route('lineup-lists.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="list-checks"></i>
                         </span>
                         <span class="nav-text">DSP</span>
                         @if (auth()->user()->isAdmin() && $adminPendingCounts['lineups'] > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto">{{ $adminPendingCounts['lineups'] }}</span>
                         @endif
                    </a>
               </li>

               @if (auth()->user()->isClubUser())
               <li class="menu-item">
                    <a class="menu-link" href="{{ route('club-resources.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="folder-down"></i>
                         </span>
                         <span class="nav-text">Pusat Informasi</span>
                    </a>
               </li>
               @endif

          </ul>
     </div>
</div>

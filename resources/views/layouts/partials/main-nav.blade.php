<div class="main-nav">
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
               <li class="menu-title">
                    <span class="menu-title-line" aria-hidden="true"></span>
                    <span class="menu-title-text">Ringkasan</span>
                    <span class="menu-title-line" aria-hidden="true"></span>
               </li>

               <li class="menu-item {{ $sidebarViewModel->activeClass('dashboard') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('dashboard') }}" href="{{ route('dashboard.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="house"></i>
                         </span>
                         <span class="nav-text">Dashboard</span>
                    </a>
               </li>

               <li class="menu-title">
                    <span class="menu-title-line" aria-hidden="true"></span>
                    <span class="menu-title-text">Data Kompetisi</span>
                    <span class="menu-title-line" aria-hidden="true"></span>
               </li>

                @if (auth()->user()->isAdmin())
                <li class="menu-item {{ $sidebarViewModel->activeClass('account_management') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('account_management') }}" href="#sidebarAccountManagement" data-bs-toggle="collapse" role="button" aria-expanded="{{ $sidebarViewModel->ariaExpanded('account_management') }}" aria-controls="sidebarAccountManagement">
                         <span class="nav-icon">
                              <i data-lucide="users"></i>
                         </span>
                         <span class="nav-text">Manajemen Akun</span>
                         <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                    </a>
                    <div class="collapse {{ $sidebarViewModel->collapseClass('account_management') }}" id="sidebarAccountManagement">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('admin-accounts.*') ? 'active' : '' }}" href="{{ route('admin-accounts.index') }}">Akun Admin</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('club-accounts.*') ? 'active' : '' }}" href="{{ route('club-accounts.create') }}">Akun Klub</a>
                              </li>
                         </ul>
                    </div>
                </li>
               @endif

               <li class="menu-item {{ $sidebarViewModel->activeClass('clubs') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('clubs') }}" href="{{ route('clubs.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="shield"></i>
                         </span>
                         <span class="nav-text">Klub</span>
                         @if (auth()->user()->isAdmin() && $sidebarViewModel->pendingCount('clubs') > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto sidebar-count-badge">{{ $sidebarViewModel->pendingCount('clubs') }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item {{ $sidebarViewModel->activeClass('officials') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('officials') }}" href="{{ route('officials.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="users-round"></i>
                         </span>
                         <span class="nav-text">Ofisial</span>
                         @if (auth()->user()->isAdmin() && $sidebarViewModel->pendingCount('officials') > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto sidebar-count-badge">{{ $sidebarViewModel->pendingCount('officials') }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item {{ $sidebarViewModel->activeClass('players') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('players') }}" href="{{ route('players.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="user-round"></i>
                         </span>
                         <span class="nav-text">Pemain</span>
                         @if (auth()->user()->isAdmin() && $sidebarViewModel->pendingCount('players') > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto sidebar-count-badge">{{ $sidebarViewModel->pendingCount('players') }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-title">
                    <span class="menu-title-line" aria-hidden="true"></span>
                    <span class="menu-title-text">Pertandingan</span>
                    <span class="menu-title-line" aria-hidden="true"></span>
               </li>

                @if (auth()->user()->isAdmin())
                <li class="menu-item {{ $sidebarViewModel->activeClass('match_schedules') }}">
                     <a class="menu-link {{ $sidebarViewModel->activeClass('match_schedules') }}" href="#sidebarMatchSchedules" data-bs-toggle="collapse" role="button" aria-expanded="{{ $sidebarViewModel->ariaExpanded('match_schedules') }}" aria-controls="sidebarMatchSchedules">
                          <span class="nav-icon">
                               <i data-lucide="flag"></i>
                          </span>
                          <span class="nav-text">Jadwal Match</span>
                          <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                     </a>
                     <div class="collapse {{ $sidebarViewModel->collapseClass('match_schedules') }}" id="sidebarMatchSchedules">
                          <ul class="sub-menu-nav">
                               <li class="sub-menu-item">
                                    <a class="sub-menu-link {{ request()->routeIs('matches.league.index') ? 'active' : '' }}" href="{{ route('matches.league.index') }}">Jadwal Liga</a>
                               </li>
                               <li class="sub-menu-item">
                                    <a class="sub-menu-link {{ request()->routeIs('matches.knockout.index') ? 'active' : '' }}" href="{{ route('matches.knockout.index') }}">Jadwal Knockout</a>
                               </li>
                          </ul>
                     </div>
                </li>

                @endif

               <li class="menu-item {{ $sidebarViewModel->activeClass('lineups') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('lineups') }}" href="{{ route('lineup-lists.index') }}">
                         <span class="nav-icon">
                              <i data-lucide="list-checks"></i>
                         </span>
                         <span class="nav-text">DSP</span>
                         @if (auth()->user()->isAdmin() && $sidebarViewModel->pendingCount('lineups') > 0)
                              <span class="badge bg-warning-subtle text-warning ms-auto sidebar-count-badge">{{ $sidebarViewModel->pendingCount('lineups') }}</span>
                         @endif
                    </a>
               </li>

               <li class="menu-item {{ $sidebarViewModel->activeClass('match_results') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('match_results') }}" href="#sidebarMatchResults" data-bs-toggle="collapse" role="button" aria-expanded="{{ $sidebarViewModel->ariaExpanded('match_results') }}" aria-controls="sidebarMatchResults">
                         <span class="nav-icon">
                              <i data-lucide="trophy"></i>
                         </span>
                         <span class="nav-text">Hasil Pertandingan</span>
                         <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                    </a>
                    <div class="collapse {{ $sidebarViewModel->collapseClass('match_results') }}" id="sidebarMatchResults">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('match-results.league.index') ? 'active' : '' }}" href="{{ route('match-results.league.index') }}">Hasil Liga</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('match-results.knockout.index') ? 'active' : '' }}" href="{{ route('match-results.knockout.index') }}">Hasil Knockout</a>
                              </li>
                         </ul>
                    </div>
               </li>

                @if (auth()->user()->isAdmin())
                <li class="menu-item {{ $sidebarViewModel->activeClass('seasons') }}">
                     <a class="menu-link {{ $sidebarViewModel->activeClass('seasons') }}" href="{{ route('seasons.index') }}">
                          <span class="nav-icon">
                               <i data-lucide="calendar-range"></i>
                          </span>
                          <span class="nav-text">Season</span>
                     </a>
                </li>
                @endif

               <li class="menu-item {{ $sidebarViewModel->activeClass('reports') }}">
                    <a class="menu-link {{ $sidebarViewModel->activeClass('reports') }}" href="#sidebarReports" data-bs-toggle="collapse" role="button" aria-expanded="{{ $sidebarViewModel->ariaExpanded('reports') }}" aria-controls="sidebarReports">
                         <span class="nav-icon">
                              <i data-lucide="file-text"></i>
                         </span>
                         <span class="nav-text">Laporan</span>
                         <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                    </a>
                    <div class="collapse {{ $sidebarViewModel->collapseClass('reports') }}" id="sidebarReports">
                         <ul class="sub-menu-nav">
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('reports.standings') || request()->routeIs('reports.standings.pdf') ? 'active' : '' }}" href="{{ route('reports.standings') }}">Klasemen</a>
                               </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('reports.top-scorers') || request()->routeIs('reports.top-scorers.pdf') ? 'active' : '' }}" href="{{ route('reports.top-scorers') }}">Top Skor</a>
                               </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('reports.top-assists') || request()->routeIs('reports.top-assists.pdf') ? 'active' : '' }}" href="{{ route('reports.top-assists') }}">Top Assist</a>
                               </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('reports.brackets') || request()->routeIs('reports.brackets.print') ? 'active' : '' }}" href="{{ route('reports.brackets') }}">Bagan Knockout</a>
                              </li>
                              <li class="sub-menu-item">
                                   <a class="sub-menu-link {{ request()->routeIs('reports.overview') || request()->routeIs('reports.overview.pdf') ? 'active' : '' }}" href="{{ route('reports.overview') }}">Rekap PDF</a>
                              </li>
                         </ul>
                    </div>
               </li>

               @if (auth()->user()->isAdmin())
               <li class="menu-title">
                    <span class="menu-title-line" aria-hidden="true"></span>
                    <span class="menu-title-text">Publik</span>
                    <span class="menu-title-line" aria-hidden="true"></span>
               </li>
                <li class="menu-item {{ $sidebarViewModel->activeClass('sponsors') }}">
                     <a class="menu-link {{ $sidebarViewModel->activeClass('sponsors') }}" href="{{ route('sponsors.index') }}">
                          <span class="nav-icon">
                              <i data-lucide="badge-dollar-sign"></i>
                         </span>
                          <span class="nav-text">Sponsor</span>
                     </a>
                </li>
                @endif

           </ul>

           <div class="sidebar-logout">
                <form method="POST" action="{{ route('logout') }}">
                     @csrf
                      <button type="submit" class="btn logout-button" aria-label="Logout" title="Logout">
                           <span class="nav-icon" aria-hidden="true">
                                <i data-lucide="log-out"></i>
                           </span>
                           <span class="logout-label">Logout</span>
                      </button>
                </form>
           </div>
      </div>
</div>

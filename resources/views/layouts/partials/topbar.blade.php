@php
     $currentUser = auth()->user();
     $notificationItems = [];
     $notificationTotal = 0;
     $cacheTtl = 45;

     if ($currentUser->isAdmin()) {
          $pendingCounts = \Illuminate\Support\Facades\Cache::remember('admin_notification_counts', $cacheTtl, function () {
               return [
                    ['label' => 'Klub', 'count' => \App\Models\Club::query()->where('verification_status', \App\Models\Club::STATUS_SUBMITTED)->count(), 'route' => route('clubs.index', ['status' => 'submitted']), 'message' => 'Pengajuan klub menunggu verifikasi.'],
                    ['label' => 'Official', 'count' => \App\Models\Official::query()->where('verification_status', \App\Models\Official::STATUS_SUBMITTED)->count(), 'route' => route('officials.index', ['status' => 'submitted']), 'message' => 'Data official menunggu verifikasi.'],
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

               $clubNeedsAttention = in_array($club->verification_status, $needsAttentionStatuses, true);
               if ($clubNeedsAttention) {
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
                         ['label' => 'Official', 'count' => \App\Models\Official::query()->where('club_id', $clubId)->whereIn('verification_status', $needsAttentionStatuses)->count(), 'route' => route('officials.index', ['status' => 'revision']), 'message' => 'Beberapa official perlu revisi.'],
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
@endphp

<style>
     @media (max-width: 991.98px) {
          .topbar .button-toggle-menu {
               width: 44px;
               height: 44px;
               padding: 0;
               align-items: center;
               justify-content: center;
          }

          .topbar .button-toggle-menu .button-sm-hover-icon {
               width: 30px;
               height: 30px;
          }
     }
</style>

<header class="topbar d-flex">
     <div class="container-fluid">
          <div class="navbar-header">

               <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-link d-inline-flex d-lg-none button-toggle-menu" aria-label="Buka menu">
                         <i data-lucide="menu" class="button-sm-hover-icon"></i>
                    </button>
                    <!-- App Search-->
                    <form class="app-search d-none d-md-block me-auto" method="GET" action="{{ route('search.index') }}" role="search" data-search-form>
                         <div class="position-relative">
                              <input
                                   type="search"
                                   class="form-control"
                                   name="q"
                                   placeholder="Cari klub, pemain, official, atau DSP..."
                                   autocomplete="off"
                                   value="{{ request('q') }}"
                                   aria-label="Cari data"
                                   data-search-autocomplete
                                   data-search-suggest-url="{{ route('search.suggestions') }}"
                              >
                              <i data-lucide="search" class="search-widget-icon"></i>
                         </div>
                    </form>
               </div>

               <div class="d-flex align-items-center gap-2 ms-auto">
                    <!-- Theme Color (Light/Dark) -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button fs-24" id="light-dark-mode">
                              <i data-lucide="moon" class="light-mode"></i>
                              <i data-lucide="sun" class="dark-mode"></i>
                         </button>
                    </div>

                    <!-- Notification -->
                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i data-lucide="bell" class="fs-20"></i>
                              @if ($notificationTotal > 0)
                                   <span class="topbar-badge text-bg-danger rounded-pill">{{ $notificationTotal }}<span class="visually-hidden">notifikasi baru</span></span>
                              @endif
                         </button>
                         <div class="dropdown-menu pt-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                   <div class="row align-items-center">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold">Notifikasi</h6>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;">
                                   @forelse ($notificationItems as $item)
                                        <a href="{{ $item['route'] }}" class="dropdown-item py-3 border-bottom">
                                             <p class="mb-1 fw-semibold">{{ $item['label'] }} <span class="badge bg-warning-subtle text-warning ms-1">{{ $item['count'] }}</span></p>
                                             <p class="mb-0 text-wrap">{{ $item['message'] }}</p>
                                        </a>
                                   @empty
                                        <div class="dropdown-item py-4 text-center text-muted">
                                             Tidak ada notifikasi baru.
                                        </div>
                                   @endforelse

                              </div>
                         </div>
                    </div>

                    <!-- User -->
                    <div class="dropdown topbar-item topbar-user-dropdown">
                         <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center gap-2">
                                   @if ($currentUser->profile_avatar_url)
                                        <img class="rounded-circle object-fit-cover" width="32" height="32" src="{{ $currentUser->profile_avatar_url }}" alt="{{ $currentUser->name }}">
                                   @else
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-white" style="width: 32px; height: 32px; background: #1d4ed8; font-size: 12px;">
                                             {{ $currentUser->profile_initials }}
                                        </span>
                                   @endif
                                   <span class="d-lg-flex flex-column gap-1 d-none">
                                        <h5 class="my-0 text-reset fs-14">{{ $currentUser->name }}</h5>
                                        <small class="text-muted text-uppercase">{{ $currentUser->role }}</small>
                                    </span>
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end topbar-user-menu">

                              <form method="POST" action="{{ route('logout') }}">
                                   @csrf
                                   <button class="dropdown-item" type="submit">
                                        <i data-lucide="log-out" class="fs-16 text-muted align-middle me-2"></i><span class="align-middle">Logout</span>
                                   </button>
                              </form>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>

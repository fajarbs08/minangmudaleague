@extends('layouts.vertical', ['title' => $title])

@php
    $selectedAgeGroupId = request('age_group_id') ? (int) request('age_group_id') : null;
    $visiblePlayers = $players->getCollection();
    $visibleTotal = $visiblePlayers->count();
    $approvedCount = $visiblePlayers->where('verification_status', 'approved')->count();
    $needsReviewCount = $visiblePlayers->whereIn('verification_status', ['submitted', 'revision'])->count();
    $filterCount = collect(request()->only(['search', 'club_id', 'age_group_id', 'status']))->filter(fn ($value) => filled($value))->count();
    $statusOptions = [
        'draft' => 'Draft',
        'submitted' => 'Dalam Proses',
        'revision' => 'Perlu Revisi',
        'approved' => 'Diterima',
        'rejected' => 'Ditolak',
    ];
@endphp

@section('content')
<div class="lap-admin-page-head">
    <div class="lap-admin-page-meta">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pemain</li>
            </ol>
        </nav>
        <h4 class="lap-admin-page-title">Pemain</h4>
        <p class="lap-admin-page-copy">Kelola data pemain, kelompok usia, dan ID card dari satu halaman kerja.</p>
    </div>
    <div class="lap-admin-page-actions">
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#playerFilterCanvas"
            aria-controls="playerFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
        @include('competition.partials.icon-button', [
            'href' => route('players.create'),
            'icon' => 'user-plus',
            'label' => 'Tambah Pemain',
            'class' => 'btn-primary',
        ])
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i data-lucide="id-card" class="fs-14"></i>
                <span>Unduh ID Card</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach ($ageGroups as $ageGroup)
                    <li>
                        <a class="dropdown-item" target="_blank" href="{{ route('players.id-cards.export', ['ageGroup' => $ageGroup->id, 'club_id' => request('club_id')]) }}">{{ $ageGroup->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 lap-admin-stat-card lap-admin-stat-card-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="lap-admin-chip lap-admin-chip-primary mb-2">Pemain</span>
                        <h3 class="lap-admin-stat-value mb-1">{{ $players->total() }}</h3>
                        <p class="lap-admin-stat-copy mb-0">Total pemain terdaftar</p>
                    </div>
                    <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="users" class="fs-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 lap-admin-stat-card lap-admin-stat-card-support">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="lap-admin-chip lap-admin-chip-support mb-2">Klub</span>
                        <h3 class="lap-admin-stat-value mb-1">{{ $visiblePlayers->pluck('club_id')->filter()->unique()->count() }}</h3>
                        <p class="lap-admin-stat-copy mb-0">Klub tampil di halaman ini</p>
                    </div>
                    <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="shield" class="fs-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 lap-admin-stat-card lap-admin-stat-card-approved">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="lap-admin-chip lap-admin-chip-approved mb-2">Disetujui</span>
                        <h3 class="lap-admin-stat-value mb-1">{{ $approvedCount }}</h3>
                        <p class="lap-admin-stat-copy mb-2">Sudah disetujui</p>
                    </div>
                    <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="badge-check" class="fs-22"></i>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div
                        class="progress-bar bg-success"
                        role="progressbar"
                        style="width: {{ $visibleTotal ? round(($approvedCount / $visibleTotal) * 100) : 0 }}%;"
                        aria-valuenow="{{ $approvedCount }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ max($visibleTotal, 1) }}"
                    ></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 lap-admin-stat-card lap-admin-stat-card-pending">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="lap-admin-chip lap-admin-chip-pending mb-2">Menunggu Review</span>
                        <h3 class="lap-admin-stat-value mb-1">{{ $needsReviewCount }}</h3>
                        <p class="lap-admin-stat-copy mb-0">Perlu tindak lanjut admin</p>
                    </div>
                    <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="clipboard-check" class="fs-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">Daftar Pemain</h4>
            <p class="text-muted mb-0">Cari pemain, cek status verifikasi, lalu lanjut ke detail atau unduh ID card dari halaman ini.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if (filled(request('status')) && isset($statusOptions[request('status')]))
                <span class="badge bg-dark-subtle text-dark">Status: {{ $statusOptions[request('status')] }}</span>
            @endif
            @if (filled(request('club_id')))
                <span class="badge bg-secondary-subtle text-secondary">Klub terfilter</span>
            @endif
            @if (filled(request('age_group_id')))
                <span class="badge bg-secondary-subtle text-secondary">Usia terfilter</span>
            @endif
            <span class="badge bg-light text-dark border">{{ $players->total() }} data</span>
        </div>
    </div>
    @if (auth()->user()->isAdmin())
    <form id="bulk-player-review-form" method="POST" action="{{ route('players.bulk-review') }}">
        @csrf
        <div class="card-body border-bottom bg-light-subtle">
            <div class="accordion" id="playerAdminAccordion">
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header" id="playerBulkReviewHeading">
                        <button
                            class="accordion-button collapsed fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#playerBulkReviewCollapse"
                            aria-expanded="false"
                            aria-controls="playerBulkReviewCollapse"
                        >
                            Tindakan Massal Admin
                        </button>
                    </h2>
                    <div
                        id="playerBulkReviewCollapse"
                        class="accordion-collapse collapse"
                        aria-labelledby="playerBulkReviewHeading"
                        data-bs-parent="#playerAdminAccordion"
                    >
                        <div class="accordion-body">
                            <div class="row g-3 align-items-start competition-bulk-panel">
                                <div class="col-lg-3">
                                    <label for="bulk-player-status" class="form-label">Aksi</label>
                                    <select id="bulk-player-status" name="status" class="form-select" data-choices data-choices-search-false data-bulk-choices required>
                                        <option value="">Pilih aksi</option>
                                        <option value="approved">Setujui</option>
                                        <option value="revision">Minta revisi</option>
                                        <option value="rejected">Tolak</option>
                                        <option value="deleted">Hapus</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label for="bulk-player-notes" class="form-label">Catatan verifikasi</label>
                                    <textarea id="bulk-player-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau penolakan."></textarea>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-dark w-100" data-bulk-submit disabled>Terapkan ke Data Terpilih</button>
                                </div>
                                <div class="col-12">
                                    <div class="small text-muted">
                                        <span data-bulk-selected-count>0</span> data dipilih di halaman ini.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive competition-table-wrap">
                <table class="table competition-table align-middle text-nowrap">
    @else
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle text-nowrap">
    @endif
                <thead>
                    <tr>
                        @if (auth()->user()->isAdmin())
                        <th class="text-center" style="width: 48px;">
                            <input type="checkbox" class="form-check-input js-check-all" data-target=".js-player-row">
                        </th>
                        @endif
                        @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Nama', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'club', 'label' => 'Klub', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Kelompok Usia', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'position', 'label' => 'Posisi', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'jersey_number', 'label' => 'No', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'verification_status', 'label' => 'Verifikasi', 'defaultSort' => 'created_at'])
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($players as $player)
                        @php
                            $initials = collect(explode(' ', trim($player->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->implode('');
                        @endphp
                        <tr>
                            @if (auth()->user()->isAdmin())
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input js-player-row" name="selected_ids[]" value="{{ $player->id }}" form="bulk-player-review-form">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($player->photo_file_url)
                                        <img
                                            src="{{ $player->photo_file_url }}"
                                            alt="{{ $player->name }}"
                                            class="avatar-sm rounded-circle object-fit-cover flex-shrink-0"
                                            width="36"
                                            height="36"
                                        >
                                    @else
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold flex-shrink-0">
                                            {{ $initials ?: 'PL' }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $player->name }}</div>
                                        <div class="text-muted small">{{ $player->school_name ?: '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($player->club)
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($player->club->logo_file_url)
                                            <img
                                                src="{{ $player->club->logo_file_url }}"
                                                alt="{{ $player->club->name }}"
                                                class="avatar-sm rounded-circle object-fit-cover flex-shrink-0"
                                                width="36"
                                                height="36"
                                            >
                                        @else
                                            <div class="avatar-sm bg-secondary-subtle text-secondary rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                                <i data-lucide="flag" class="fs-18"></i>
                                            </div>
                                        @endif
                                        <div class="fw-medium">{{ $player->club->name }}</div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $player->registrationForAgeGroup($selectedAgeGroupId)?->ageGroup?->name ?: $player->primaryAgeGroup?->name ?: '-' }}</td>
                            <td>{{ $player->displayPosition($selectedAgeGroupId) ?: '-' }}</td>
                            <td>{{ $player->displayJerseyNumber($selectedAgeGroupId) ?: '-' }}</td>
                            <td>
                                @include('competition.partials.status-badge', ['status' => $player->verification_status])
                                @if ($player->verification_notes)
                                    <div class="small mt-2">
                                        <a
                                            href="#player-note-{{ $player->id }}"
                                            class="link-secondary"
                                            data-bs-toggle="collapse"
                                            role="button"
                                            aria-expanded="false"
                                            aria-controls="player-note-{{ $player->id }}"
                                        >
                                            Lihat catatan
                                        </a>
                                        <div class="collapse mt-2" id="player-note-{{ $player->id }}">
                                            <div class="card card-body bg-light-subtle border-0 p-2 text-wrap">
                                                {{ $player->verification_notes }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @php
                                $isAdmin = auth()->user()->isAdmin();
                                $actionHint = match ($player->verification_status) {
                                    'draft' => $isAdmin ? 'Buka detail atau edit manual admin.' : 'Lengkapi data pemain lalu ajukan verifikasi.',
                                    'submitted' => $isAdmin ? 'Tinjau pengajuan lalu beri keputusan.' : 'Menunggu review admin.',
                                    'revision' => $isAdmin ? 'Minta revisi atau edit manual admin.' : 'Perbaiki data lalu submit ulang.',
                                    'approved' => $isAdmin ? 'Pemain sudah diterima. Edit manual tetap tersedia.' : 'Pemain sudah diterima admin.',
                                    'rejected' => $isAdmin ? 'Tindak lanjuti lewat revisi atau edit manual.' : 'Periksa catatan admin.',
                                    default => 'Lanjutkan sesuai status pemain.',
                                };
                            @endphp
                            <td class="text-end competition-table-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Tindakan</span>
                                        <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                        <div class="competition-action-section">
                                            <div class="competition-action-label px-2 pb-2">Navigasi</div>
                                            <div class="small text-muted px-2 pb-2 text-wrap">{{ $actionHint }}</div>
                                            @include('competition.partials.action-item', [
                                                'href' => route('players.show', $player),
                                                'icon' => 'eye',
                                                'label' => 'Lihat Detail',
                                            ])
                                            @if ($player->verification_notes)
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'message-square-text',
                                                    'label' => 'Lihat Catatan Admin',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#playerNoteModal',
                                                        'data-note-title' => $player->name,
                                                        'data-note-content' => $player->verification_notes,
                                                    ],
                                                ])
                                            @endif
                                            @if ($isAdmin || $player->canBeEditedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('players.edit', $player),
                                                    'icon' => 'square-pen',
                                                    'label' => $isAdmin ? 'Edit oleh Admin' : 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        @if (($isAdmin && $player->canBeReviewedByAdmin()) || (!$isAdmin && $player->canBeSubmittedByClub()))
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                @if ($isAdmin && $player->canBeReviewedByAdmin())
                                                    <div class="competition-action-label px-2 pb-2">Verifikasi Admin</div>
                                                    @if ($player->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'check',
                                                            'label' => 'Setujui',
                                                            'class' => 'text-success',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#playerReviewModal',
                                                                'data-review-route' => route('players.review', $player),
                                                                'data-review-status' => 'approved',
                                                                'data-review-label' => 'Setujui Pemain',
                                                                'data-review-title' => $player->name,
                                                                'data-review-notes-required' => '0',
                                                                'data-review-placeholder' => 'Catatan admin opsional.',
                                                            ],
                                                        ])
                                                    @endif
                                                    @include('competition.partials.action-item', [
                                                        'icon' => 'refresh-ccw',
                                                        'label' => 'Minta Revisi',
                                                        'class' => 'text-warning',
                                                        'attributes' => [
                                                            'data-bs-toggle' => 'modal',
                                                            'data-bs-target' => '#playerReviewModal',
                                                            'data-review-route' => route('players.review', $player),
                                                            'data-review-status' => 'revision',
                                                            'data-review-label' => 'Minta Revisi Pemain',
                                                            'data-review-title' => $player->name,
                                                            'data-review-notes-required' => '1',
                                                            'data-review-placeholder' => 'Catatan admin wajib diisi untuk revisi.',
                                                        ],
                                                    ])
                                                    @if ($player->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'x',
                                                            'label' => 'Tolak',
                                                            'class' => 'text-danger',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#playerReviewModal',
                                                                'data-review-route' => route('players.review', $player),
                                                                'data-review-status' => 'rejected',
                                                                'data-review-label' => 'Tolak Pemain',
                                                                'data-review-title' => $player->name,
                                                                'data-review-notes-required' => '1',
                                                                'data-review-placeholder' => 'Catatan admin wajib diisi untuk penolakan.',
                                                            ],
                                                        ])
                                                    @endif
                                                @elseif (!$isAdmin && $player->canBeSubmittedByClub())
                                                    <form method="POST" action="{{ route('players.submit', $player) }}" class="px-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                                            <i data-lucide="send" class="review-actions-icon" aria-hidden="true"></i>
                                                            <span>Ajukan Verifikasi</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($isAdmin || $player->canBeSubmittedByClub())
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'trash-2',
                                                    'label' => 'Hapus',
                                                    'class' => 'text-danger js-delete-player',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#deletePlayerModal',
                                                        'data-action' => route('players.destroy', $player),
                                                        'data-name' => $player->name,
                                                    ],
                                                ])
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="competition-table-empty">Belum ada data pemain.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $players->links() }}</div>
    </div>
    @if (auth()->user()->isAdmin())
    </form>
    @endif
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="playerFilterCanvas" aria-labelledby="playerFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="playerFilterCanvasLabel">Filter Pemain</h5>
            <p class="text-muted mb-0 small">Pakai filter detail tanpa memenuhi area tabel.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="player-search" class="form-label">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fs-16" data-lucide="search"></i>
                    </span>
                    <input id="player-search" type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari nama, registrasi, posisi">
                </div>
            </div>
            <div>
                <label for="player-club-id" class="form-label">Klub</label>
                <select id="player-club-id" name="club_id" class="form-select">
                    <option value="">Semua klub</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((string) request('club_id') === (string) $club->id)>{{ $club->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="player-age-group-id" class="form-label">Kelompok usia</label>
                <select id="player-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="player-status" class="form-label">Status verifikasi</label>
                <select id="player-status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                <a href="{{ route('players.index') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deletePlayerModal',
    'title' => 'Hapus Pemain',
    'formId' => 'delete-player-form',
    'nameClass' => 'js-delete-player-name',
    'messagePrefix' => 'Pemain',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

<div class="modal fade" id="playerNoteModal" tabindex="-1" aria-labelledby="playerNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="playerNoteModalLabel">Catatan Admin</h5>
                    <div class="small text-muted mt-1" id="player-note-title">-</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-wrap mb-0" id="player-note-content">-</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="playerReviewModal" tabindex="-1" aria-labelledby="playerReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="player-review-form">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="playerReviewModalLabel">Review Pemain</h5>
                        <div class="small text-muted mt-1" id="player-review-title">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" id="player-review-status">
                    <div class="mb-0">
                        <label for="player-review-notes" class="form-label">Catatan Admin</label>
                        <textarea
                            name="verification_notes"
                            id="player-review-notes"
                            rows="4"
                            class="form-control"
                            placeholder="Catatan admin"
                        ></textarea>
                        <div class="form-text" id="player-review-help">Isi catatan bila diperlukan.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="player-review-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="card-title mb-1">Shortcut Kelompok Usia</h4>
            <p class="text-muted mb-0">Pilih kelompok usia untuk langsung memfokuskan daftar pemain.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach ($ageGroups as $ageGroup)
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('players.index', ['age_group_id' => $ageGroup->id]) }}" class="text-decoration-none">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold">{{ $ageGroup->name }}</div>
                            <div class="text-muted small mt-1">{{ $players->getCollection()->filter(fn ($player) => $player->ageRegistrations->contains('age_group_id', $ageGroup->id))->count() }} pemain pada halaman ini</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

@include('layouts.partials.action-confirm-modal', [
    'modalId' => 'bulkPlayerActionConfirmModal',
    'title' => 'Konfirmasi Aksi Massal',
    'message' => 'Aksi ini akan diterapkan ke data terpilih.',
    'submitLabel' => 'Lanjutkan',
    'submitClass' => 'btn-danger',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.querySelector('#bulk-player-review-form');
    if (bulkForm) {
        const checkAll = bulkForm.querySelector('.js-check-all');
        const bulkSubmit = bulkForm.querySelector('[data-bulk-submit]');
        const bulkCount = bulkForm.querySelector('[data-bulk-selected-count]');
        const bulkStatus = bulkForm.querySelector('[name=status]');
        const bulkConfirmModal = document.getElementById('bulkPlayerActionConfirmModal');

        if (checkAll) {
            const getRows = () => Array.from(bulkForm.querySelectorAll('.js-player-row'));

            const syncBulkState = () => {
                const rows = getRows();
                const selectedCount = rows.filter((checkbox) => checkbox.checked).length;

                if (bulkCount) {
                    bulkCount.textContent = selectedCount;
                }

                if (bulkSubmit) {
                    bulkSubmit.disabled = selectedCount === 0;
                }

                checkAll.checked = rows.length > 0 && selectedCount === rows.length;
                checkAll.indeterminate = selectedCount > 0 && selectedCount < rows.length;
            };

            checkAll.addEventListener('change', function () {
                const rows = getRows();
                rows.forEach((checkbox) => {
                    checkbox.checked = checkAll.checked;
                });

                syncBulkState();
            });

            bulkForm.addEventListener('change', function (event) {
                if (event.target.matches('.js-player-row')) {
                    syncBulkState();
                }
            });

            bulkForm.addEventListener('submit', function (event) {
                if (bulkForm.dataset.confirmAccepted === '1') {
                    delete bulkForm.dataset.confirmAccepted;
                    return;
                }

                const selectedCount = getRows().filter((checkbox) => checkbox.checked).length;

                if (selectedCount === 0) {
                    event.preventDefault();
                    return;
                }

                const bulkAction = bulkStatus?.value;

                if (!['deleted', 'rejected'].includes(bulkAction || '')) {
                    return;
                }

                event.preventDefault();

                if (!bulkConfirmModal) {
                    return;
                }

                const isDelete = bulkAction === 'deleted';

                bulkConfirmModal.setAttribute('data-confirm-form', '#bulk-player-review-form');
                bulkConfirmModal.setAttribute('data-confirm-title', isDelete ? 'Hapus Pemain' : 'Tolak Pemain');
                bulkConfirmModal.setAttribute('data-confirm-message', isDelete
                    ? `Hapus ${selectedCount} pemain terpilih? Data yang sudah dihapus tidak bisa dikembalikan.`
                    : `Tolak ${selectedCount} pemain terpilih? Status akan berubah menjadi ditolak dan catatan admin wajib diisi.`);
                bulkConfirmModal.setAttribute('data-confirm-submit-label', isDelete ? 'Hapus' : 'Tolak');
                bulkConfirmModal.setAttribute('data-confirm-submit-class', 'btn-danger');

                bootstrap.Modal.getOrCreateInstance(bulkConfirmModal).show();
            });

            syncBulkState();
        }
    }

    const reviewModal = document.getElementById('playerReviewModal');
    if (reviewModal) {
        const reviewForm = reviewModal.querySelector('#player-review-form');
        const reviewStatus = reviewModal.querySelector('#player-review-status');
        const reviewTitle = reviewModal.querySelector('#player-review-title');
        const reviewNotes = reviewModal.querySelector('#player-review-notes');
        const reviewHelp = reviewModal.querySelector('#player-review-help');
        const reviewSubmit = reviewModal.querySelector('#player-review-submit');
        const reviewHeading = reviewModal.querySelector('#playerReviewModalLabel');

        reviewModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const route = trigger.getAttribute('data-review-route') || '';
            const status = trigger.getAttribute('data-review-status') || '';
            const label = trigger.getAttribute('data-review-label') || 'Review Pemain';
            const title = trigger.getAttribute('data-review-title') || '-';
            const notesRequired = trigger.getAttribute('data-review-notes-required') === '1';
            const placeholder = trigger.getAttribute('data-review-placeholder') || 'Catatan admin';

            reviewForm.setAttribute('action', route);
            reviewStatus.value = status;
            reviewTitle.textContent = title;
            reviewNotes.value = '';
            reviewNotes.placeholder = placeholder;
            reviewNotes.required = notesRequired;
            reviewHelp.textContent = notesRequired ? 'Catatan admin wajib diisi untuk aksi ini.' : 'Isi catatan bila diperlukan.';
            reviewSubmit.textContent = label;
            reviewHeading.textContent = label;
        });
    }

    const noteModal = document.getElementById('playerNoteModal');
    if (noteModal) {
        const noteTitle = noteModal.querySelector('#player-note-title');
        const noteContent = noteModal.querySelector('#player-note-content');

        noteModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            noteTitle.textContent = trigger.getAttribute('data-note-title') || '-';
            noteContent.textContent = trigger.getAttribute('data-note-content') || '-';
        });
    }
});
</script>
@endpush

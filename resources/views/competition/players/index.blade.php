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
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pemain</li>
            </ol>
        </nav>
        <h4 class="mb-1">Pemain</h4>
        <p class="text-muted mb-0">Kelola data pemain, kelompok usia, dan ID card dari panel yang konsisten dengan modul kompetisi lain.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a
            href="#player-filter-panel"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="{{ $filterCount ? 'true' : 'false' }}"
            aria-controls="player-filter-panel"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </a>
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
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge bg-primary-subtle text-primary mb-2">Table</span>
                        <h3 class="mb-1">{{ $players->total() }}</h3>
                        <p class="text-muted mb-0">Total pemain terdaftar</p>
                    </div>
                    <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="users" class="fs-22 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge bg-info-subtle text-info mb-2">Klub</span>
                        <h3 class="mb-1">{{ $visiblePlayers->pluck('club_id')->filter()->unique()->count() }}</h3>
                        <p class="text-muted mb-0">Klub tampil di halaman ini</p>
                    </div>
                    <div class="avatar-md bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="shield" class="fs-22 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge bg-success-subtle text-success mb-2">Progress</span>
                        <h3 class="mb-1">{{ $approvedCount }}</h3>
                        <p class="text-muted mb-2">Sudah disetujui</p>
                    </div>
                    <div class="avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="badge-check" class="fs-22 text-success"></i>
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
        <div class="card h-100 border-warning border-opacity-25">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge bg-warning-subtle text-warning mb-2">Review</span>
                        <h3 class="mb-1">{{ $needsReviewCount }}</h3>
                        <p class="text-muted mb-0">Perlu tindak lanjut admin</p>
                    </div>
                    <div class="avatar-md bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="clipboard-check" class="fs-22 text-warning"></i>
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
            <p class="text-muted mb-0">Cari pemain dengan cepat, cek status verifikasi, lalu lanjut ke detail atau ID card dari satu panel.</p>
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
    <div class="card-body border-bottom collapse {{ $filterCount ? 'show' : '' }}" id="player-filter-panel">
        <form class="row g-3">
            <div class="col-xl-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fs-16" data-lucide="search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari nama, registrasi, posisi">
                </div>
            </div>
            <div class="col-xl-2 col-md-6">
                <select name="club_id" class="form-select">
                    <option value="">Semua klub</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((string) request('club_id') === (string) $club->id)>{{ $club->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-4">
                <select name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Dalam Proses</option>
                    <option value="revision" @selected(request('status') === 'revision')>Perlu Revisi</option>
                    <option value="approved" @selected(request('status') === 'approved')>Diterima</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-4">
                <div class="d-grid gap-2 d-md-flex">
                    <button class="btn btn-primary flex-fill" type="submit">Filter</button>
                    <a href="{{ route('players.index') }}" class="btn btn-light flex-fill">Reset</a>
                </div>
            </div>
        </form>
    </div>
    @if (auth()->user()->isAdmin())
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
                        Bulk Action Admin
                    </button>
                </h2>
                <div
                    id="playerBulkReviewCollapse"
                    class="accordion-collapse collapse"
                    aria-labelledby="playerBulkReviewHeading"
                    data-bs-parent="#playerAdminAccordion"
                >
                    <div class="accordion-body">
                        <form id="bulk-player-review-form" method="POST" action="{{ route('players.bulk-review') }}" class="row g-3 align-items-start">
                            @csrf
                            <div class="col-lg-3">
                                <label for="bulk-player-status" class="form-label">Aksi</label>
                                <select id="bulk-player-status" name="status" class="form-select" required>
                                    <option value="">Bulk action admin</option>
                                    <option value="approved">Approve terpilih</option>
                                    <option value="revision">Minta revisi terpilih</option>
                                    <option value="rejected">Reject terpilih</option>
                                    <option value="deleted">Hapus terpilih</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label for="bulk-player-notes" class="form-label">Catatan verifikasi</label>
                                <textarea id="bulk-player-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau reject."></textarea>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-dark w-100">Terapkan ke Data Terpilih</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle text-nowrap">
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
                        <tr>
                            @if (auth()->user()->isAdmin())
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input js-player-row" name="selected_ids[]" value="{{ $player->id }}" form="bulk-player-review-form">
                            </td>
                            @endif
                            <td>
                                <div class="fw-semibold">{{ $player->name }}</div>
                                <div class="text-muted small">{{ $player->registration_number ?: '-' }}</div>
                            </td>
                            <td>{{ $player->club?->name }}</td>
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
                            <td class="text-end">
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
                                            @include('competition.partials.action-item', [
                                                'href' => route('players.show', $player),
                                                'icon' => 'eye',
                                                'label' => 'Detail',
                                            ])
                                            @if (auth()->user()->isAdmin() || $player->canBeEditedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('players.edit', $player),
                                                    'icon' => 'square-pen',
                                                    'label' => 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="competition-action-section">
                                            @include('competition.partials.review-actions', [
                                                'item' => $player,
                                                'submitRoute' => route('players.submit', $player),
                                                'reviewRoute' => route('players.review', $player),
                                            ])
                                        </div>
                                        @if (auth()->user()->isAdmin() || $player->canBeSubmittedByClub())
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
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deletePlayerModal',
    'title' => 'Hapus Pemain',
    'formId' => 'delete-player-form',
    'nameClass' => 'js-delete-player-name',
    'messagePrefix' => 'Pemain',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.querySelector('.js-check-all');
    if (!checkAll) {
        return;
    }

    const rows = document.querySelectorAll(checkAll.dataset.target);

    checkAll.addEventListener('change', function () {
        rows.forEach((checkbox) => {
            checkbox.checked = checkAll.checked;
        });
    });
});
</script>
@endpush

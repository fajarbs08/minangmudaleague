@extends('layouts.vertical', ['title' => $title])

@php
    $isAdmin = auth()->user()->isAdmin();
    $visibleClubs = $clubs->getCollection();
    $visibleTotal = $visibleClubs->count();
    $approvedCount = $visibleClubs->where('verification_status', 'approved')->count();
    $needsReviewCount = $visibleClubs->whereIn('verification_status', ['submitted', 'revision'])->count();
    $totalOfficials = $visibleClubs->sum('officials_count');
    $totalPlayers = $visibleClubs->sum('players_count');
    $filterCount = collect(request()->only(['search', 'status']))->filter(fn ($value) => filled($value))->count();
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
                <li class="breadcrumb-item active" aria-current="page">Klub</li>
            </ol>
        </nav>
        <h4 class="mb-1">Klub</h4>
        <p class="text-muted mb-0">Kelola klub peserta, kelengkapan berkas, dan status verifikasi dari panel yang konsisten dengan modul kompetisi lain.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($isAdmin)
            <a
                href="#club-filter-panel"
                class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
                data-bs-toggle="collapse"
                role="button"
                aria-expanded="{{ $filterCount ? 'true' : 'false' }}"
                aria-controls="club-filter-panel"
            >
                <i data-lucide="filter" class="fs-14"></i>
                <span>Filter</span>
                @if ($filterCount)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
                @endif
            </a>
        @endif
        @if ($isAdmin || (auth()->user()->isClubUser() && $clubs->isEmpty()))
        @include('competition.partials.icon-button', [
            'href' => route('clubs.create'),
            'icon' => 'plus-circle',
            'label' => 'Tambah Klub',
            'class' => 'btn-primary',
        ])
        @endif
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
                        <h3 class="mb-1">{{ $clubs->total() }}</h3>
                        <p class="text-muted mb-0">Total klub terdaftar</p>
                    </div>
                    <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="shield" class="fs-22 text-primary"></i>
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
                        <span class="badge bg-info-subtle text-info mb-2">Official</span>
                        <h3 class="mb-1">{{ $totalOfficials }}</h3>
                        <p class="text-muted mb-0">Total official di halaman ini</p>
                    </div>
                    <div class="avatar-md bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="users" class="fs-22 text-info"></i>
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
                        <p class="text-muted mb-2">Klub sudah disetujui</p>
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
            <h4 class="card-title mb-1">{{ $isAdmin ? 'Daftar Klub' : 'Profil Klub' }}</h4>
            <p class="text-muted mb-0">
                {{ $isAdmin
                    ? 'Cari klub, cek progres verifikasi, lalu jalankan aksi dari menu per baris dengan format yang seragam.'
                    : 'Pantau data klub dan status verifikasi Anda dari halaman ini.' }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($isAdmin)
                @if (filled(request('status')) && isset($statusOptions[request('status')]))
                    <span class="badge bg-dark-subtle text-dark">Status: {{ $statusOptions[request('status')] }}</span>
                @endif
                @if (filled(request('search')))
                    <span class="badge bg-secondary-subtle text-secondary">Pencarian aktif</span>
                @endif
                <span class="badge bg-light text-dark border">{{ $clubs->total() }} data</span>
            @else
                @if ($visibleClubs->first())
                    @include('competition.partials.status-badge', ['status' => $visibleClubs->first()->verification_status])
                @endif
            @endif
        </div>
    </div>
    @if ($isAdmin)
        <div class="card-body border-bottom collapse {{ $filterCount ? 'show' : '' }}" id="club-filter-panel">
            <form class="row g-3">
                <div class="col-xl-5 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fs-16" data-lucide="search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari nama klub, kota, zona">
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Dalam Proses</option>
                        <option value="revision" @selected(request('status') === 'revision')>Perlu Revisi</option>
                        <option value="approved" @selected(request('status') === 'approved')>Diterima</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                    </select>
                </div>
                <div class="col-xl-4 col-md-12">
                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-primary flex-fill" type="submit">Filter</button>
                        <a href="{{ route('clubs.index') }}" class="btn btn-light flex-fill">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    @endif
    @if ($isAdmin)
    <div class="card-body border-bottom bg-light-subtle">
        <div class="accordion" id="clubAdminAccordion">
            <div class="accordion-item border rounded">
                <h2 class="accordion-header" id="clubBulkReviewHeading">
                    <button
                        class="accordion-button collapsed fw-semibold"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#clubBulkReviewCollapse"
                        aria-expanded="false"
                        aria-controls="clubBulkReviewCollapse"
                    >
                        Bulk Action Admin
                    </button>
                </h2>
                <div
                    id="clubBulkReviewCollapse"
                    class="accordion-collapse collapse"
                    aria-labelledby="clubBulkReviewHeading"
                    data-bs-parent="#clubAdminAccordion"
                >
                    <div class="accordion-body">
                        <form id="bulk-club-review-form" method="POST" action="{{ route('clubs.bulk-review') }}" class="row g-3 align-items-start">
                            @csrf
                            <div class="col-lg-3">
                                <label for="bulk-club-status" class="form-label">Aksi</label>
                                <select id="bulk-club-status" name="status" class="form-select" required>
                                    <option value="">Bulk action admin</option>
                                    <option value="approved">Approve terpilih</option>
                                    <option value="revision">Minta revisi terpilih</option>
                                    <option value="rejected">Reject terpilih</option>
                                    <option value="deleted">Hapus terpilih</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label for="bulk-club-notes" class="form-label">Catatan verifikasi</label>
                                <textarea id="bulk-club-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau reject."></textarea>
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
                            <input type="checkbox" class="form-check-input js-check-all" data-target=".js-club-row">
                        </th>
                        @endif
                        @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Klub', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'zone', 'label' => 'Zona', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'city', 'label' => 'Kota', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'officials_count', 'label' => 'Official', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'players_count', 'label' => 'Pemain', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'lineup_lists_count', 'label' => 'DSP', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'verification_status', 'label' => 'Verifikasi', 'defaultSort' => 'created_at'])
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clubs as $club)
                        <tr>
                            @if (auth()->user()->isAdmin())
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input js-club-row" name="selected_ids[]" value="{{ $club->id }}" form="bulk-club-review-form">
                            </td>
                            @endif
                            <td>
                                <div class="fw-semibold">{{ $club->name }}</div>
                                <div class="text-muted small">{{ $club->short_name ?: '-' }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if ($club->deed_file_url)
                                        <a href="{{ $club->deed_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i data-lucide="file-text" class="fs-14"></i>
                                            <span>Akta</span>
                                        </a>
                                    @endif
                                    @if ($club->statement_file_url)
                                        <a href="{{ $club->statement_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i data-lucide="file-text" class="fs-14"></i>
                                            <span>Pernyataan</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $club->zone ?: '-' }}</td>
                            <td>{{ $club->city ?: '-' }}</td>
                            <td>{{ $club->officials_count }}</td>
                            <td>{{ $club->players_count }}</td>
                            <td>{{ $club->lineup_lists_count }}</td>
                            <td>
                                @include('competition.partials.status-badge', ['status' => $club->verification_status])
                                @if ($club->verification_notes)
                                    <div class="small mt-2">
                                        <a
                                            href="#club-note-{{ $club->id }}"
                                            class="link-secondary"
                                            data-bs-toggle="collapse"
                                            role="button"
                                            aria-expanded="false"
                                            aria-controls="club-note-{{ $club->id }}"
                                        >
                                            Lihat catatan
                                        </a>
                                        <div class="collapse mt-2" id="club-note-{{ $club->id }}">
                                            <div class="card card-body bg-light-subtle border-0 p-2 text-wrap">
                                                {{ $club->verification_notes }}
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
                                                'href' => route('clubs.show', $club),
                                                'icon' => 'eye',
                                                'label' => 'View',
                                            ])
                                            @if (auth()->user()->isAdmin() || $club->canBeSubmittedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('clubs.edit', $club),
                                                    'icon' => 'square-pen',
                                                    'label' => 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="competition-action-section">
                                            @include('competition.partials.review-actions', [
                                                'item' => $club,
                                                'submitRoute' => route('clubs.submit', $club),
                                                'reviewRoute' => route('clubs.review', $club),
                                            ])
                                        </div>
                                        @if (auth()->user()->isAdmin() || $club->canBeSubmittedByClub())
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'trash-2',
                                                    'label' => 'Hapus',
                                                    'class' => 'text-danger js-delete-club',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#deleteClubModal',
                                                        'data-action' => route('clubs.destroy', $club),
                                                        'data-name' => $club->name,
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
                            <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="competition-table-empty">Belum ada data klub.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $clubs->links() }}</div>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteClubModal',
    'title' => 'Hapus Klub',
    'formId' => 'delete-club-form',
    'nameClass' => 'js-delete-club-name',
    'messagePrefix' => 'Klub',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])
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

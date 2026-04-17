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
                        <input type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari nama klub atau zona kota/kabupaten">
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
    <form id="bulk-club-review-form" method="POST" action="{{ route('clubs.bulk-review') }}">
        @csrf
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
                            <div class="row g-3 align-items-start competition-bulk-panel">
                                <div class="col-lg-3">
                                    <label for="bulk-club-status" class="form-label">Aksi</label>
                                    <select id="bulk-club-status" name="status" class="form-select" data-choices data-choices-search-false data-bulk-choices required>
                                        <option value="">Pilih aksi</option>
                                        <option value="approved">Approve</option>
                                        <option value="revision">Minta revisi</option>
                                        <option value="rejected">Reject</option>
                                        <option value="deleted">Hapus</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label for="bulk-club-notes" class="form-label">Catatan verifikasi</label>
                                    <textarea id="bulk-club-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau reject."></textarea>
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
                            <input type="checkbox" class="form-check-input js-check-all" data-target=".js-club-row">
                        </th>
                        @endif
                        @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Klub', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'zone', 'label' => 'Zona (Kota / Kabupaten)', 'defaultSort' => 'created_at'])
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
                                <div class="d-flex align-items-start gap-3">
                                    @if ($club->logo_file_url)
                                        <div class="rounded border bg-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; padding: 6px;">
                                            <img
                                                src="{{ $club->logo_file_url }}"
                                                alt="{{ $club->name }}"
                                                style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;"
                                            >
                                        </div>
                                    @else
                                        <div class="avatar-sm bg-secondary-subtle text-secondary rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                            <i data-lucide="flag" class="fs-18"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $club->name }}</div>
                                        <div class="text-muted small">{{ $club->short_name ?: '-' }}</div>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @if ($club->statement_file_url)
                                                <a href="{{ $club->statement_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                                    <i data-lucide="file-text" class="fs-14"></i>
                                                    <span>Pernyataan</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $club->zone ?: '-' }}</td>
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
                            @php
                                $actionHint = match ($club->verification_status) {
                                    'draft' => $isAdmin ? 'Buka detail atau edit manual admin.' : 'Lengkapi data klub lalu ajukan verifikasi.',
                                    'submitted' => $isAdmin ? 'Review pengajuan admin.' : 'Menunggu review admin.',
                                    'revision' => $isAdmin ? 'Minta revisi atau edit manual admin.' : 'Perbaiki data lalu submit ulang.',
                                    'approved' => $isAdmin ? 'Klub sudah diterima. Edit manual tetap tersedia.' : 'Klub sudah diterima admin.',
                                    'rejected' => $isAdmin ? 'Tindak lanjuti lewat revisi atau edit manual.' : 'Periksa catatan admin.',
                                    default => 'Lanjutkan sesuai status klub.',
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
                                                'href' => route('clubs.show', $club),
                                                'icon' => 'eye',
                                                'label' => 'Lihat Detail',
                                            ])
                                            @if ($club->verification_notes)
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'message-square-text',
                                                    'label' => 'Lihat Catatan Admin',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#clubNoteModal',
                                                        'data-note-title' => $club->name,
                                                        'data-note-content' => $club->verification_notes,
                                                    ],
                                                ])
                                            @endif
                                            @if ($isAdmin || $club->canBeSubmittedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('clubs.edit', $club),
                                                    'icon' => 'square-pen',
                                                    'label' => $isAdmin ? 'Edit Manual Admin' : 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        @if (($isAdmin && $club->canBeReviewedByAdmin()) || (!$isAdmin && $club->canBeSubmittedByClub()))
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                @if ($isAdmin && $club->canBeReviewedByAdmin())
                                                    <div class="competition-action-label px-2 pb-2">Review Admin</div>
                                                    @if ($club->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'check',
                                                            'label' => 'Approve',
                                                            'class' => 'text-success',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#clubReviewModal',
                                                                'data-review-route' => route('clubs.review', $club),
                                                                'data-review-status' => 'approved',
                                                                'data-review-label' => 'Approve Klub',
                                                                'data-review-title' => $club->name,
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
                                                            'data-bs-target' => '#clubReviewModal',
                                                            'data-review-route' => route('clubs.review', $club),
                                                            'data-review-status' => 'revision',
                                                            'data-review-label' => 'Minta Revisi Klub',
                                                            'data-review-title' => $club->name,
                                                            'data-review-notes-required' => '1',
                                                            'data-review-placeholder' => 'Catatan admin wajib diisi untuk revisi.',
                                                        ],
                                                    ])
                                                    @if ($club->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'x',
                                                            'label' => 'Reject',
                                                            'class' => 'text-danger',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#clubReviewModal',
                                                                'data-review-route' => route('clubs.review', $club),
                                                                'data-review-status' => 'rejected',
                                                                'data-review-label' => 'Reject Klub',
                                                                'data-review-title' => $club->name,
                                                                'data-review-notes-required' => '1',
                                                                'data-review-placeholder' => 'Catatan admin wajib diisi untuk reject.',
                                                            ],
                                                        ])
                                                    @endif
                                                @elseif (!$isAdmin && $club->canBeSubmittedByClub())
                                                    <form method="POST" action="{{ route('clubs.submit', $club) }}" class="px-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                                            <i data-lucide="send" class="review-actions-icon" aria-hidden="true"></i>
                                                            <span>Submit Verifikasi</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($isAdmin || $club->canBeSubmittedByClub())
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
    @if ($isAdmin)
    </form>
    @endif
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteClubModal',
    'title' => 'Hapus Klub',
    'formId' => 'delete-club-form',
    'nameClass' => 'js-delete-club-name',
    'messagePrefix' => 'Klub',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

<div class="modal fade" id="clubNoteModal" tabindex="-1" aria-labelledby="clubNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="clubNoteModalLabel">Catatan Admin</h5>
                    <div class="small text-muted mt-1" id="club-note-title">-</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-wrap mb-0" id="club-note-content">-</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="clubReviewModal" tabindex="-1" aria-labelledby="clubReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="club-review-form">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="clubReviewModalLabel">Review Klub</h5>
                        <div class="small text-muted mt-1" id="club-review-title">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" id="club-review-status">
                    <div class="mb-0">
                        <label for="club-review-notes" class="form-label">Catatan Admin</label>
                        <textarea
                            name="verification_notes"
                            id="club-review-notes"
                            rows="4"
                            class="form-control"
                            placeholder="Catatan admin"
                        ></textarea>
                        <div class="form-text" id="club-review-help">Isi catatan bila diperlukan.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="club-review-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.querySelector('#bulk-club-review-form');
    if (!bulkForm) {
        return;
    }

    const checkAll = bulkForm.querySelector('.js-check-all');
    const bulkSubmit = bulkForm.querySelector('[data-bulk-submit]');
    const bulkCount = bulkForm.querySelector('[data-bulk-selected-count]');

    if (!checkAll) {
        return;
    }

    const getRows = () => Array.from(bulkForm.querySelectorAll('.js-club-row'));

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
        if (event.target.matches('.js-club-row')) {
            syncBulkState();
        }
    });

    bulkForm.addEventListener('submit', function (event) {
        const selectedCount = getRows().filter((checkbox) => checkbox.checked).length;

        if (selectedCount === 0) {
            event.preventDefault();
        }
    });

    syncBulkState();

    const reviewModal = document.getElementById('clubReviewModal');
    if (reviewModal) {
        const reviewForm = reviewModal.querySelector('#club-review-form');
        const reviewStatus = reviewModal.querySelector('#club-review-status');
        const reviewTitle = reviewModal.querySelector('#club-review-title');
        const reviewNotes = reviewModal.querySelector('#club-review-notes');
        const reviewHelp = reviewModal.querySelector('#club-review-help');
        const reviewSubmit = reviewModal.querySelector('#club-review-submit');
        const reviewHeading = reviewModal.querySelector('#clubReviewModalLabel');

        reviewModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const route = trigger.getAttribute('data-review-route') || '';
            const status = trigger.getAttribute('data-review-status') || '';
            const label = trigger.getAttribute('data-review-label') || 'Review Klub';
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

    const noteModal = document.getElementById('clubNoteModal');
    if (noteModal) {
        const noteTitle = noteModal.querySelector('#club-note-title');
        const noteContent = noteModal.querySelector('#club-note-content');

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

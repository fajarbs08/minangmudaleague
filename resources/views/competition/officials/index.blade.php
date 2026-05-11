@extends('layouts.vertical', ['title' => $title])

@php
    $isHistoryView = app(\App\Services\SeasonContext::class)->isViewingHistory();
    $visibleOfficials = $officials->getCollection();
    $visibleTotal = $visibleOfficials->count();
    $activeCount = $visibleOfficials->where('is_active', true)->count();
    $approvedCount = $visibleOfficials->where('verification_status', 'approved')->count();
    $needsReviewCount = $visibleOfficials->whereIn('verification_status', ['submitted', 'revision'])->count();
    $filterCount = collect(request()->only(['search', 'club_id', 'age_group_id', 'status']))->filter(fn ($value) => filled($value))->count();
    $statusOptions = [
        'draft' => 'Draft',
        'submitted' => 'Dalam Proses',
        'revision' => 'Perlu Revisi',
        'approved' => 'Diterima',
        'rejected' => 'Ditolak',
    ];
@endphp

@push('css')
<style>
    .lap-index-deferred {
        content-visibility: auto;
        contain-intrinsic-size: 1px 1080px;
    }
    .lap-index-deferred.is-compact {
        contain-intrinsic-size: 1px 420px;
    }
</style>
@endpush

@section('content')
<div class="lap-admin-page-head">
    <div class="lap-admin-page-meta">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ofisial</li>
            </ol>
        </nav>
        <h4 class="lap-admin-page-title">Ofisial</h4>
        <p class="lap-admin-page-copy">Kelola data ofisial tiap klub, status aktif, dan verifikasi dari satu halaman kerja.</p>
    </div>
    <div class="lap-admin-page-actions">
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#officialFilterCanvas"
            aria-controls="officialFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
        @if (! $isHistoryView && $idCardExportUrl && $canDownloadIdCards)
            <a class="btn btn-success d-inline-flex align-items-center gap-2" target="_blank" href="{{ $idCardExportUrl }}">
                <i data-lucide="id-card" class="fs-14"></i>
                <span>Unduh ID Card</span>
            </a>
        @elseif (! $isHistoryView)
            <button class="btn btn-success d-inline-flex align-items-center gap-2" type="button" disabled>
                <i data-lucide="id-card" class="fs-14"></i>
                <span>Unduh ID Card</span>
            </button>
        @endif
        @unless ($isHistoryView)
            @include('competition.partials.icon-button', [
                'href' => route('officials.create'),
                'icon' => 'user-plus',
                'label' => 'Tambah Ofisial',
                'class' => 'btn-primary',
            ])
        @endunless
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4 lap-index-deferred is-compact">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 lap-admin-stat-card lap-admin-stat-card-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="lap-admin-chip lap-admin-chip-primary mb-2">Ofisial</span>
                        <h3 class="lap-admin-stat-value mb-1">{{ $officials->total() }}</h3>
                        <p class="lap-admin-stat-copy mb-0">Total ofisial terdaftar</p>
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
                        <span class="lap-admin-chip lap-admin-chip-support mb-2">Aktif</span>
                        <h3 class="lap-admin-stat-value mb-1">{{ $activeCount }}</h3>
                        <p class="lap-admin-stat-copy mb-2">Aktif di halaman ini</p>
                    </div>
                    <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="badge-check" class="fs-22"></i>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div
                        class="progress-bar bg-success"
                        role="progressbar"
                        style="width: {{ $visibleTotal ? round(($activeCount / $visibleTotal) * 100) : 0 }}%;"
                        aria-valuenow="{{ $activeCount }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ max($visibleTotal, 1) }}"
                    ></div>
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
                        <i data-lucide="shield-check" class="fs-22"></i>
                    </div>
                </div>
                <div class="progress progress-sm">
                    <div
                        class="progress-bar bg-info"
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

<div class="card lap-index-deferred">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">Daftar Ofisial</h4>
            <p class="text-muted mb-0">Gunakan filter cepat, cek status verifikasi, lalu jalankan aksi dari menu per baris.</p>
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
            <span class="badge bg-light text-dark border">{{ $officials->total() }} data</span>
        </div>
    </div>

    @if (auth()->user()->isAdmin() && ! $isHistoryView)
    <form id="bulk-official-review-form" method="POST" action="{{ route('officials.bulk-review') }}">
        @csrf
        <div class="card-body border-bottom bg-light-subtle">
            <div class="accordion" id="officialAdminAccordion">
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header" id="bulkReviewHeading">
                        <button
                            class="accordion-button collapsed fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#bulkReviewCollapse"
                            aria-expanded="false"
                            aria-controls="bulkReviewCollapse"
                        >
                            Tindakan Massal Admin
                        </button>
                    </h2>
                    <div
                        id="bulkReviewCollapse"
                        class="accordion-collapse collapse"
                        aria-labelledby="bulkReviewHeading"
                        data-bs-parent="#officialAdminAccordion"
                    >
                        <div class="accordion-body">
                            <div class="row g-3 align-items-start competition-bulk-panel">
                                <div class="col-lg-3">
                                    <label for="bulk-status" class="form-label">Aksi</label>
                                    <select id="bulk-status" name="status" class="form-select" data-choices data-choices-search-false data-bulk-choices required>
                                        <option value="">Pilih aksi</option>
                                        <option value="approved">Setujui</option>
                                        <option value="revision">Minta revisi</option>
                                        <option value="rejected">Tolak</option>
                                        <option value="deleted">Hapus</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label for="bulk-notes" class="form-label">Catatan verifikasi</label>
                                    <textarea id="bulk-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau penolakan."></textarea>
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
                        @if (auth()->user()->isAdmin() && ! $isHistoryView)
                        <th class="text-center" style="width: 48px;">
                            <input type="checkbox" class="form-check-input js-check-all" data-target=".js-official-row">
                        </th>
                        @endif
                        @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Ofisial', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'club', 'label' => 'Klub', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'role', 'label' => 'Peran', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Usia', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'email', 'label' => 'Kontak', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'is_active', 'label' => 'Status', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'verification_status', 'label' => 'Verifikasi', 'defaultSort' => 'created_at'])
                        <th class="text-end competition-table-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($officials as $official)
                        @php
                            $ageGroupName = $official->registrationForAgeGroup(request('age_group_id') ? (int) request('age_group_id') : null)?->ageGroup?->name
                                ?: $official->ageGroup?->name
                                ?: '-';
                            $initials = collect(explode(' ', trim($official->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                ->implode('');
                        @endphp
                        <tr>
                            @if (auth()->user()->isAdmin() && ! $isHistoryView)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input js-official-row" name="selected_ids[]" value="{{ $official->id }}" form="bulk-official-review-form">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($official->photo_file_url)
                                            <img
                                                src="{{ $official->photo_file_url }}"
                                                alt="{{ $official->name }}"
                                                class="avatar-sm rounded-circle object-fit-cover flex-shrink-0"
                                                width="36"
                                                height="36"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                    @else
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold flex-shrink-0">
                                            {{ $initials ?: 'OF' }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $official->name }}</div>
                                        <div class="text-muted small">{{ $official->email ?: 'Email belum tersedia' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($isHistoryView ? $official->seasonClub : $official->club)
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($isHistoryView ? $official->seasonClub?->logo_file_url : $official->club?->logo_file_url)
                                            <img
                                                src="{{ $isHistoryView ? $official->seasonClub?->logo_file_url : $official->club?->logo_file_url }}"
                                                alt="{{ $isHistoryView ? $official->seasonClub?->name : $official->club?->name }}"
                                                class="avatar-sm rounded-circle object-fit-cover flex-shrink-0"
                                                width="36"
                                                height="36"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        @else
                                            <div class="avatar-sm bg-secondary-subtle text-secondary rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                                <i data-lucide="flag" class="fs-18"></i>
                                            </div>
                                        @endif
                                        <div class="fw-medium">{{ $isHistoryView ? $official->seasonClub?->name : $official->club?->name }}</div>
                                    </div>
                                @else
                                    <div class="fw-medium">-</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $official->role }}</span>
                            </td>
                            <td>{{ $ageGroupName }}</td>
                            <td>
                                <div>{{ $official->phone ?: '-' }}</div>
                                <button
                                    type="button"
                                    class="btn btn-link btn-sm p-0 text-decoration-none"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="{{ $official->email ?: 'Email belum diisi' }}"
                                >
                                    Lihat email
                                </button>
                            </td>
                            <td>
                                <span class="badge {{ $official->is_active ? 'lap-admin-chip lap-admin-chip-approved' : 'lap-admin-chip lap-admin-chip-danger' }}">
                                    {{ $official->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                @include('competition.partials.status-badge', ['status' => $official->verification_status])
                                @if ($official->verification_notes)
                                    <div class="small mt-2">
                                        <a
                                            href="#official-note-{{ $official->id }}"
                                            class="link-secondary"
                                            data-bs-toggle="collapse"
                                            role="button"
                                            aria-expanded="false"
                                            aria-controls="official-note-{{ $official->id }}"
                                        >
                                            Lihat catatan
                                        </a>
                                        <div class="collapse mt-2" id="official-note-{{ $official->id }}">
                                            <div class="card card-body bg-light-subtle border-0 p-2 text-wrap">
                                                {{ $official->verification_notes }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @php
                                $isAdmin = auth()->user()->isAdmin();
                                $actionHint = match ($official->verification_status) {
                                    'draft' => $isAdmin ? 'Buka detail atau edit manual admin.' : 'Lengkapi data ofisial lalu ajukan verifikasi.',
                                    'submitted' => $isAdmin ? 'Tinjau pengajuan lalu beri keputusan.' : 'Menunggu review admin.',
                                    'revision' => $isAdmin ? 'Minta revisi atau edit manual admin.' : 'Perbaiki data lalu submit ulang.',
                                    'approved' => $isAdmin ? 'Ofisial sudah diterima. Edit manual tetap tersedia.' : 'Ofisial sudah diterima admin.',
                                    'rejected' => $isAdmin ? 'Tindak lanjuti lewat revisi atau edit manual.' : 'Periksa catatan admin.',
                                    default => 'Lanjutkan sesuai status ofisial.',
                                };
                            @endphp
                            <td class="text-end competition-table-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Aksi</span>
                                        <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                        <div class="competition-action-section">
                                            <div class="competition-action-label px-2 pb-2">Navigasi</div>
                                            <div class="small text-muted px-2 pb-2 text-wrap">{{ $actionHint }}</div>
                                            @include('competition.partials.action-item', [
                                                'href' => route('officials.show', $isHistoryView ? $official->official_id : $official),
                                                'icon' => 'eye',
                                                'label' => 'Lihat Detail',
                                            ])
                                            @if ($official->verification_notes)
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'message-square-text',
                                                    'label' => 'Lihat Catatan Admin',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#officialNoteModal',
                                                        'data-note-title' => $official->name,
                                                        'data-note-content' => $official->verification_notes,
                                                    ],
                                                ])
                                            @endif
                                            @if (! $isHistoryView && ($isAdmin || $official->canBeEditedByClub()))
                                                @include('competition.partials.action-item', [
                                                    'href' => route('officials.edit', $official),
                                                    'icon' => 'square-pen',
                                                    'label' => $isAdmin ? 'Edit oleh Admin' : 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        @if (! $isHistoryView && (($isAdmin && $official->canBeReviewedByAdmin()) || (!$isAdmin && $official->canBeSubmittedByClub())))
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                @if ($isAdmin && $official->canBeReviewedByAdmin())
                                                    <div class="competition-action-label px-2 pb-2">Verifikasi Admin</div>
                                                    @if ($official->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'check',
                                                            'label' => 'Setujui',
                                                            'class' => 'text-success',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#officialReviewModal',
                                                                'data-review-route' => route('officials.review', $official),
                                                                'data-review-status' => 'approved',
                                                                'data-review-label' => 'Setujui Ofisial',
                                                                'data-review-title' => $official->name,
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
                                                            'data-bs-target' => '#officialReviewModal',
                                                            'data-review-route' => route('officials.review', $official),
                                                            'data-review-status' => 'revision',
                                                            'data-review-label' => 'Minta Revisi Ofisial',
                                                            'data-review-title' => $official->name,
                                                            'data-review-notes-required' => '1',
                                                            'data-review-placeholder' => 'Catatan admin wajib diisi untuk revisi.',
                                                        ],
                                                    ])
                                                    @if ($official->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'x',
                                                            'label' => 'Tolak',
                                                            'class' => 'text-danger',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#officialReviewModal',
                                                                'data-review-route' => route('officials.review', $official),
                                                                'data-review-status' => 'rejected',
                                                                'data-review-label' => 'Tolak Ofisial',
                                                                'data-review-title' => $official->name,
                                                                'data-review-notes-required' => '1',
                                                                'data-review-placeholder' => 'Catatan admin wajib diisi untuk penolakan.',
                                                            ],
                                                        ])
                                                    @endif
                                                @elseif (!$isAdmin && $official->canBeSubmittedByClub())
                                                    <form method="POST" action="{{ route('officials.submit', $official) }}" class="px-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                                            <i data-lucide="send" class="review-actions-icon" aria-hidden="true"></i>
                                                            <span>Ajukan Verifikasi</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                        @if (! $isHistoryView && ($isAdmin || $official->canBeSubmittedByClub()))
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'trash-2',
                                                    'label' => 'Hapus',
                                                    'class' => 'text-danger js-delete-official',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#deleteOfficialModal',
                                                        'data-action' => route('officials.destroy', $official),
                                                        'data-name' => $official->name,
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
                            <td colspan="{{ (auth()->user()->isAdmin() && ! $isHistoryView) ? 9 : 8 }}" class="competition-table-empty py-5">
                                <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                    <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center">
                                        <i data-lucide="inbox" class="fs-22"></i>
                                    </div>
                                    <div class="fw-semibold text-dark">Belum ada data ofisial.</div>
                                    <div class="small">Ubah filter atau tambahkan ofisial baru untuk mulai mengisi daftar ini.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if (auth()->user()->isAdmin() && ! $isHistoryView)
    </form>
    @endif

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
        <p class="text-muted mb-0">Menampilkan {{ $officials->firstItem() ?? 0 }}-{{ $officials->lastItem() ?? 0 }} dari {{ $officials->total() }} ofisial.</p>
        <div>{{ $officials->links() }}</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="officialFilterCanvas" aria-labelledby="officialFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="officialFilterCanvasLabel">Filter Ofisial</h5>
            <p class="text-muted mb-0 small">Pakai filter detail tanpa memenuhi area tabel.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="search" class="form-label">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fs-16" data-lucide="search"></i>
                    </span>
                    <input id="search" type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari nama, peran, email">
                </div>
            </div>
            <div>
                <label for="club_id" class="form-label">Klub</label>
                <select id="club_id" name="club_id" class="form-select">
                    <option value="">Semua klub</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((string) request('club_id') === (string) $club->id)>{{ $club->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="age_group_id" class="form-label">Kelompok usia</label>
                <select id="age_group_id" name="age_group_id" class="form-select">
                    <option value="">Semua usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="form-label">Status verifikasi</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                <a href="{{ route('officials.index') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteOfficialModal',
    'title' => 'Hapus Ofisial',
    'formId' => 'delete-official-form',
    'nameClass' => 'js-delete-official-name',
    'messagePrefix' => 'Ofisial',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

<div class="modal fade" id="officialNoteModal" tabindex="-1" aria-labelledby="officialNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="officialNoteModalLabel">Catatan Admin</h5>
                    <div class="small text-muted mt-1" id="official-note-title">-</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-wrap mb-0" id="official-note-content">-</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="officialReviewModal" tabindex="-1" aria-labelledby="officialReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="official-review-form">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="officialReviewModalLabel">Review Ofisial</h5>
                        <div class="small text-muted mt-1" id="official-review-title">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" id="official-review-status">
                    <div class="mb-0">
                        <label for="official-review-notes" class="form-label">Catatan Admin</label>
                        <textarea
                            name="verification_notes"
                            id="official-review-notes"
                            rows="4"
                            class="form-control"
                            placeholder="Catatan admin"
                        ></textarea>
                        <div class="form-text" id="official-review-help">Isi catatan bila diperlukan.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="official-review-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.partials.action-confirm-modal', [
    'modalId' => 'bulkOfficialActionConfirmModal',
    'title' => 'Konfirmasi Aksi Massal',
    'message' => 'Aksi ini akan diterapkan ke data terpilih.',
    'submitLabel' => 'Lanjutkan',
    'submitClass' => 'btn-danger',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        bootstrap.Tooltip.getOrCreateInstance(element);
    });

    const bulkForm = document.querySelector('#bulk-official-review-form');
    if (bulkForm) {
        const checkAll = bulkForm.querySelector('.js-check-all');
        const bulkSubmit = bulkForm.querySelector('[data-bulk-submit]');
        const bulkCount = bulkForm.querySelector('[data-bulk-selected-count]');
        const bulkStatus = bulkForm.querySelector('[name=status]');
        const bulkConfirmModal = document.getElementById('bulkOfficialActionConfirmModal');

        if (checkAll) {
            const getRows = () => Array.from(bulkForm.querySelectorAll('.js-official-row'));

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
                if (event.target.matches('.js-official-row')) {
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

                bulkConfirmModal.setAttribute('data-confirm-form', '#bulk-official-review-form');
                bulkConfirmModal.setAttribute('data-confirm-title', isDelete ? 'Hapus Ofisial' : 'Tolak Ofisial');
                bulkConfirmModal.setAttribute('data-confirm-message', isDelete
                    ? `Hapus ${selectedCount} ofisial terpilih? Data yang sudah dihapus tidak bisa dikembalikan.`
                    : `Tolak ${selectedCount} ofisial terpilih? Status akan berubah menjadi ditolak dan catatan admin wajib diisi.`);
                bulkConfirmModal.setAttribute('data-confirm-submit-label', isDelete ? 'Hapus' : 'Tolak');
                bulkConfirmModal.setAttribute('data-confirm-submit-class', 'btn-danger');

                bootstrap.Modal.getOrCreateInstance(bulkConfirmModal).show();
            });

            syncBulkState();
        }
    }

    const reviewModal = document.getElementById('officialReviewModal');
    if (reviewModal) {
        const reviewForm = reviewModal.querySelector('#official-review-form');
        const reviewStatus = reviewModal.querySelector('#official-review-status');
        const reviewTitle = reviewModal.querySelector('#official-review-title');
        const reviewNotes = reviewModal.querySelector('#official-review-notes');
        const reviewHelp = reviewModal.querySelector('#official-review-help');
        const reviewSubmit = reviewModal.querySelector('#official-review-submit');
        const reviewHeading = reviewModal.querySelector('#officialReviewModalLabel');

        reviewModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const route = trigger.getAttribute('data-review-route') || '';
            const status = trigger.getAttribute('data-review-status') || '';
            const label = trigger.getAttribute('data-review-label') || 'Review Ofisial';
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

    const noteModal = document.getElementById('officialNoteModal');
    if (noteModal) {
        const noteTitle = noteModal.querySelector('#official-note-title');
        const noteContent = noteModal.querySelector('#official-note-content');

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

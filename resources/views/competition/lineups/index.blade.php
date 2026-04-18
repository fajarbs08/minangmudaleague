@extends('layouts.vertical', ['title' => $title])

@php
    $visibleLineups = $lineupLists->getCollection();
    $visibleTotal = $visibleLineups->count();
    $approvedCount = $visibleLineups->where('verification_status', 'approved')->count();
    $needsReviewCount = $visibleLineups->whereIn('verification_status', ['submitted', 'revision'])->count();
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
                <li class="breadcrumb-item active" aria-current="page">DSP</li>
            </ol>
        </nav>
        <h4 class="mb-1">Daftar Susunan Pemain</h4>
        <p class="text-muted mb-0">Kelola DSP pertandingan, cek status verifikasi, dan buka lembar cetak dari satu halaman kerja.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#lineupFilterCanvas"
            aria-controls="lineupFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
        @include('competition.partials.icon-button', [
            'href' => route('lineup-lists.create'),
            'icon' => 'plus-circle',
            'label' => 'Tambah DSP',
            'class' => 'btn-primary',
        ])
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge bg-primary-subtle text-primary mb-2">DSP</span>
                        <h3 class="mb-1">{{ $lineupLists->total() }}</h3>
                        <p class="text-muted mb-0">Total DSP terdaftar</p>
                    </div>
                    <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="clipboard-list" class="fs-22 text-primary"></i>
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
                        <span class="badge bg-info-subtle text-info mb-2">Pemain</span>
                        <h3 class="mb-1">{{ $visibleLineups->sum(fn ($lineup) => (int) $lineup->starter_count + (int) $lineup->substitute_count) }}</h3>
                        <p class="text-muted mb-0">Total pemain di halaman ini</p>
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
                        <span class="badge bg-success-subtle text-success mb-2">Disetujui</span>
                        <h3 class="mb-1">{{ $approvedCount }}</h3>
                        <p class="text-muted mb-2">DSP sudah disetujui</p>
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
                        <span class="badge bg-warning-subtle text-warning mb-2">Menunggu Review</span>
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
            <h4 class="card-title mb-1">Daftar DSP</h4>
            <p class="text-muted mb-0">Saring data berdasarkan klub, usia, atau status verifikasi lalu buka detail DSP atau lembar cetak.</p>
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
            <span class="badge bg-light text-dark border">{{ $lineupLists->total() }} data</span>
        </div>
    </div>
    @if (auth()->user()->isAdmin())
    <form id="bulk-lineup-review-form" method="POST" action="{{ route('lineup-lists.bulk-review') }}">
        @csrf
        <div class="card-body border-bottom bg-light-subtle">
            <div class="accordion" id="lineupAdminAccordion">
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header" id="lineupBulkReviewHeading">
                        <button
                            class="accordion-button collapsed fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#lineupBulkReviewCollapse"
                            aria-expanded="false"
                            aria-controls="lineupBulkReviewCollapse"
                        >
                            Tindakan Massal Admin
                        </button>
                    </h2>
                    <div
                        id="lineupBulkReviewCollapse"
                        class="accordion-collapse collapse"
                        aria-labelledby="lineupBulkReviewHeading"
                        data-bs-parent="#lineupAdminAccordion"
                    >
                        <div class="accordion-body">
                            <div class="row g-3 align-items-start competition-bulk-panel">
                                <div class="col-lg-3">
                                    <label for="bulk-lineup-status" class="form-label">Aksi</label>
                                    <select id="bulk-lineup-status" name="status" class="form-select" data-choices data-choices-search-false data-bulk-choices required>
                                        <option value="">Pilih aksi</option>
                                        <option value="approved">Setujui</option>
                                        <option value="revision">Minta revisi</option>
                                        <option value="rejected">Tolak</option>
                                        <option value="deleted">Hapus</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label for="bulk-lineup-notes" class="form-label">Catatan verifikasi</label>
                                    <textarea id="bulk-lineup-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau penolakan."></textarea>
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
                            <input type="checkbox" class="form-check-input js-check-all" data-target=".js-lineup-row">
                        </th>
                        @endif
                        @include('competition.partials.sortable-th', ['key' => 'title', 'label' => 'Judul', 'defaultSort' => 'match_date'])
                        @include('competition.partials.sortable-th', ['key' => 'club', 'label' => 'Klub', 'defaultSort' => 'match_date'])
                        <th>Lawan</th>
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Kelompok Usia', 'defaultSort' => 'match_date'])
                        @include('competition.partials.sortable-th', ['key' => 'match_date', 'label' => 'Tanggal', 'defaultSort' => 'match_date'])
                        <th>Susunan</th>
                        @include('competition.partials.sortable-th', ['key' => 'verification_status', 'label' => 'Verifikasi', 'defaultSort' => 'match_date'])
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                        @forelse ($lineupLists as $lineup)
                        @php
                            $matchLineupClubIds = $lineup->match?->lineupLists?->pluck('club_id')->map(fn ($id) => (int) $id) ?? collect();
                            $matchComplete = $lineup->match
                                && $matchLineupClubIds->contains((int) $lineup->match->club_a_id)
                                && $matchLineupClubIds->contains((int) $lineup->match->club_b_id);
                            $isAdmin = auth()->user()->isAdmin();
                            $actionHint = match ($lineup->verification_status) {
                                'draft' => $isAdmin ? 'Buka DSP atau edit manual admin.' : 'Lengkapi DSP lalu ajukan verifikasi.',
                                'submitted' => $isAdmin ? 'Tinjau pengajuan lalu beri keputusan.' : 'Menunggu review admin.',
                                'revision' => $isAdmin ? 'Minta revisi atau edit manual admin.' : 'Perbaiki data lalu submit ulang.',
                                'approved' => $isAdmin ? 'DSP sudah diterima. Edit manual tetap tersedia.' : 'DSP sudah diterima admin.',
                                'rejected' => $isAdmin ? 'Tindak lanjuti lewat revisi atau edit manual.' : 'Periksa catatan admin.',
                                default => 'Lanjutkan sesuai status DSP.',
                            };
                        @endphp
                        <tr>
                            @if (auth()->user()->isAdmin())
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input js-lineup-row" name="selected_ids[]" value="{{ $lineup->id }}">
                            </td>
                            @endif
                            <td>
                                <div class="fw-semibold">{{ $lineup->title }}</div>
                                <div class="text-muted small">
                                    {{ $lineup->match?->clubA?->name && $lineup->match?->clubB?->name ? $lineup->match->clubA->name.' vs '.$lineup->match->clubB->name : ($lineup->coach_name ?: '-') }}
                                </div>
                                @if ($lineup->played_at || $lineup->played_time || $lineup->match?->venue || $lineup->match?->kickoff_time)
                                    <div class="text-muted small">{{ $lineup->played_at ?: $lineup->match?->venue ?: '-' }} · {{ optional($lineup->played_time ?: $lineup->match?->kickoff_time)->format('H:i') ?: '-' }}</div>
                                @endif
                            </td>
                            <td>{{ $lineup->club?->name }}</td>
                            <td>{{ $lineup->opponent()?->name ?: '-' }}</td>
                            <td>{{ $lineup->ageGroup?->name }}</td>
                            <td>{{ optional($lineup->match_date ?: $lineup->match?->match_date)->format('d M Y') ?: '-' }}</td>
                            <td>
                                <div class="small">Starter: {{ $lineup->starter_count }}</div>
                                <div class="small text-muted">Cadangan: {{ $lineup->substitute_count }}</div>
                            </td>
                            <td>
                                @include('competition.partials.status-badge', ['status' => $lineup->verification_status])
                                @if ($lineup->match)
                                    <div class="mt-2">
                                        <span class="badge {{ $matchComplete ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $matchComplete ? 'Lengkap DSP' : 'Menunggu DSP lawan' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($lineup->verification_notes)
                                    <div class="small mt-2">
                                        <a
                                            href="#lineup-note-{{ $lineup->id }}"
                                            class="link-secondary"
                                            data-bs-toggle="collapse"
                                            role="button"
                                            aria-expanded="false"
                                            aria-controls="lineup-note-{{ $lineup->id }}"
                                        >
                                            Lihat catatan
                                        </a>
                                        <div class="collapse mt-2" id="lineup-note-{{ $lineup->id }}">
                                            <div class="card card-body bg-light-subtle border-0 p-2 text-wrap">
                                                {{ $lineup->verification_notes }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
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
                                                'href' => route('lineup-lists.show', $lineup),
                                                'icon' => 'file-output',
                                                'label' => 'Lihat DSP',
                                            ])
                                            @if ($lineup->verification_notes)
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'message-square-text',
                                                    'label' => 'Lihat Catatan Admin',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#lineupNoteModal',
                                                        'data-note-title' => $lineup->title,
                                                        'data-note-content' => $lineup->verification_notes,
                                                    ],
                                                ])
                                            @endif
                                            @if ($isAdmin || $lineup->canBeEditedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('lineup-lists.edit', $lineup),
                                                    'icon' => 'square-pen',
                                                    'label' => $isAdmin ? 'Edit oleh Admin' : 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        @if (($isAdmin && $lineup->canBeReviewedByAdmin()) || (!$isAdmin && $lineup->canBeSubmittedByClub()))
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                @if ($isAdmin && $lineup->canBeReviewedByAdmin())
                                                    <div class="competition-action-label px-2 pb-2">Verifikasi Admin</div>
                                                    @if ($lineup->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'check',
                                                            'label' => 'Setujui',
                                                            'class' => 'text-success',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#lineupReviewModal',
                                                                'data-review-route' => route('lineup-lists.review', $lineup),
                                                                'data-review-status' => 'approved',
                                                                'data-review-label' => 'Setujui DSP',
                                                                'data-review-title' => $lineup->title,
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
                                                            'data-bs-target' => '#lineupReviewModal',
                                                            'data-review-route' => route('lineup-lists.review', $lineup),
                                                            'data-review-status' => 'revision',
                                                            'data-review-label' => 'Minta Revisi DSP',
                                                            'data-review-title' => $lineup->title,
                                                            'data-review-notes-required' => '1',
                                                            'data-review-placeholder' => 'Catatan admin wajib diisi untuk revisi.',
                                                        ],
                                                    ])
                                                    @if ($lineup->verification_status !== 'approved')
                                                        @include('competition.partials.action-item', [
                                                            'icon' => 'x',
                                                            'label' => 'Tolak',
                                                            'class' => 'text-danger',
                                                            'attributes' => [
                                                                'data-bs-toggle' => 'modal',
                                                                'data-bs-target' => '#lineupReviewModal',
                                                                'data-review-route' => route('lineup-lists.review', $lineup),
                                                                'data-review-status' => 'rejected',
                                                                'data-review-label' => 'Tolak DSP',
                                                                'data-review-title' => $lineup->title,
                                                                'data-review-notes-required' => '1',
                                                                'data-review-placeholder' => 'Catatan admin wajib diisi untuk penolakan.',
                                                            ],
                                                        ])
                                                    @endif
                                                @elseif (!$isAdmin && $lineup->canBeSubmittedByClub())
                                                    <form method="POST" action="{{ route('lineup-lists.submit', $lineup) }}" class="px-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                                            <i data-lucide="send" class="review-actions-icon" aria-hidden="true"></i>
                                                            <span>Ajukan Verifikasi</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($isAdmin || $lineup->canBeSubmittedByClub())
                                            <div class="dropdown-divider"></div>
                                            <div class="competition-action-section">
                                                <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'trash-2',
                                                    'label' => 'Hapus',
                                                    'class' => 'text-danger js-delete-lineup',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#deleteLineupModal',
                                                        'data-action' => route('lineup-lists.destroy', $lineup),
                                                        'data-name' => $lineup->title,
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
                            <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="competition-table-empty">Belum ada DSP.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $lineupLists->links() }}</div>
    </div>
    @if (auth()->user()->isAdmin())
    </form>
    @endif
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="lineupFilterCanvas" aria-labelledby="lineupFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="lineupFilterCanvasLabel">Filter DSP</h5>
            <p class="text-muted mb-0 small">Pakai filter detail tanpa memenuhi area tabel.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="lineup-search" class="form-label">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fs-16" data-lucide="search"></i>
                    </span>
                    <input id="lineup-search" type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari judul, pelatih, match day">
                </div>
            </div>
            <div>
                <label for="lineup-club-id" class="form-label">Klub</label>
                <select id="lineup-club-id" name="club_id" class="form-select">
                    <option value="">Semua klub</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((string) request('club_id') === (string) $club->id)>{{ $club->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="lineup-age-group-id" class="form-label">Kelompok usia</label>
                <select id="lineup-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="lineup-status" class="form-label">Status verifikasi</label>
                <select id="lineup-status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                <a href="{{ route('lineup-lists.index') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteLineupModal',
    'title' => 'Hapus DSP',
    'formId' => 'delete-lineup-form',
    'nameClass' => 'js-delete-lineup-name',
    'messagePrefix' => 'DSP',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

<div class="modal fade" id="lineupNoteModal" tabindex="-1" aria-labelledby="lineupNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="lineupNoteModalLabel">Catatan Admin</h5>
                    <div class="small text-muted mt-1" id="lineup-note-title">-</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-wrap mb-0" id="lineup-note-content">-</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lineupReviewModal" tabindex="-1" aria-labelledby="lineupReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="lineup-review-form">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="lineupReviewModalLabel">Review DSP</h5>
                        <div class="small text-muted mt-1" id="lineup-review-title">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" id="lineup-review-status">
                    <div class="mb-0">
                        <label for="lineup-review-notes" class="form-label">Catatan Admin</label>
                        <textarea
                            name="verification_notes"
                            id="lineup-review-notes"
                            rows="4"
                            class="form-control"
                            placeholder="Catatan admin"
                        ></textarea>
                        <div class="form-text" id="lineup-review-help">Isi catatan bila diperlukan.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="lineup-review-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.partials.action-confirm-modal', [
    'modalId' => 'bulkLineupActionConfirmModal',
    'title' => 'Konfirmasi Aksi Massal',
    'message' => 'Aksi ini akan diterapkan ke data terpilih.',
    'submitLabel' => 'Lanjutkan',
    'submitClass' => 'btn-danger',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.querySelector('#bulk-lineup-review-form');
    if (!bulkForm) {
        return;
    }

    const checkAll = bulkForm.querySelector('.js-check-all');
    const bulkSubmit = bulkForm.querySelector('[data-bulk-submit]');
    const bulkCount = bulkForm.querySelector('[data-bulk-selected-count]');
    const bulkStatus = bulkForm.querySelector('[name=status]');
    const bulkConfirmModal = document.getElementById('bulkLineupActionConfirmModal');

    if (!checkAll) {
        return;
    }

    const getRows = () => Array.from(bulkForm.querySelectorAll('.js-lineup-row'));

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
        if (event.target.matches('.js-lineup-row')) {
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

        bulkConfirmModal.setAttribute('data-confirm-form', '#bulk-lineup-review-form');
        bulkConfirmModal.setAttribute('data-confirm-title', isDelete ? 'Hapus DSP' : 'Tolak DSP');
        bulkConfirmModal.setAttribute('data-confirm-message', isDelete
            ? `Hapus ${selectedCount} DSP terpilih? Data yang sudah dihapus tidak bisa dikembalikan.`
            : `Tolak ${selectedCount} DSP terpilih? Status akan berubah menjadi ditolak dan catatan admin wajib diisi.`);
        bulkConfirmModal.setAttribute('data-confirm-submit-label', isDelete ? 'Hapus' : 'Tolak');
        bulkConfirmModal.setAttribute('data-confirm-submit-class', 'btn-danger');

        bootstrap.Modal.getOrCreateInstance(bulkConfirmModal).show();
    });

    syncBulkState();

    const reviewModal = document.getElementById('lineupReviewModal');
    if (reviewModal) {
        const reviewForm = reviewModal.querySelector('#lineup-review-form');
        const reviewStatus = reviewModal.querySelector('#lineup-review-status');
        const reviewTitle = reviewModal.querySelector('#lineup-review-title');
        const reviewNotes = reviewModal.querySelector('#lineup-review-notes');
        const reviewHelp = reviewModal.querySelector('#lineup-review-help');
        const reviewSubmit = reviewModal.querySelector('#lineup-review-submit');
        const reviewHeading = reviewModal.querySelector('#lineupReviewModalLabel');

        reviewModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const route = trigger.getAttribute('data-review-route') || '';
            const status = trigger.getAttribute('data-review-status') || '';
            const label = trigger.getAttribute('data-review-label') || 'Review DSP';
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

    const noteModal = document.getElementById('lineupNoteModal');
    if (noteModal) {
        const noteTitle = noteModal.querySelector('#lineup-note-title');
        const noteContent = noteModal.querySelector('#lineup-note-content');

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

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
        <p class="text-muted mb-0">Kelola DSP dengan panel yang konsisten untuk filter, review, dan generate lembar pertandingan.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a
            href="#lineup-filter-panel"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="{{ $filterCount ? 'true' : 'false' }}"
            aria-controls="lineup-filter-panel"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </a>
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
                        <span class="badge bg-primary-subtle text-primary mb-2">Table</span>
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
                        <span class="badge bg-info-subtle text-info mb-2">Roster</span>
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
                        <span class="badge bg-success-subtle text-success mb-2">Progress</span>
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
            <h4 class="card-title mb-1">Daftar DSP</h4>
            <p class="text-muted mb-0">Saring data berdasarkan klub, usia, atau status verifikasi lalu lanjut ke generate DSP.</p>
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
    <div class="card-body border-bottom collapse {{ $filterCount ? 'show' : '' }}" id="lineup-filter-panel">
        <form class="row g-3">
            <div class="col-xl-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fs-16" data-lucide="search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cari judul, pelatih, match day">
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
                    <a href="{{ route('lineup-lists.index') }}" class="btn btn-light flex-fill">Reset</a>
                </div>
            </div>
        </form>
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
                            Bulk Action Admin
                        </button>
                    </h2>
                    <div
                        id="lineupBulkReviewCollapse"
                        class="accordion-collapse collapse"
                        aria-labelledby="lineupBulkReviewHeading"
                        data-bs-parent="#lineupAdminAccordion"
                    >
                        <div class="accordion-body">
                            <div class="row g-3 align-items-start">
                                <div class="col-lg-3">
                                    <label for="bulk-lineup-status" class="form-label">Aksi</label>
                                    <select id="bulk-lineup-status" name="status" class="form-select" required>
                                        <option value="">Bulk action admin</option>
                                        <option value="approved">Approve terpilih</option>
                                        <option value="revision">Minta revisi terpilih</option>
                                        <option value="rejected">Reject terpilih</option>
                                        <option value="deleted">Hapus terpilih</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label for="bulk-lineup-notes" class="form-label">Catatan verifikasi</label>
                                    <textarea id="bulk-lineup-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau reject."></textarea>
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
                        <th>Roster</th>
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
                                                'href' => route('lineup-lists.show', $lineup),
                                                'icon' => 'file-output',
                                                'label' => 'Generate DSP',
                                            ])
                                            @if (auth()->user()->isAdmin() || $lineup->canBeEditedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('lineup-lists.edit', $lineup),
                                                    'icon' => 'square-pen',
                                                    'label' => 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="competition-action-section">
                                            @include('competition.partials.review-actions', [
                                                'item' => $lineup,
                                                'submitRoute' => route('lineup-lists.submit', $lineup),
                                                'reviewRoute' => route('lineup-lists.review', $lineup),
                                            ])
                                        </div>
                                        @if (auth()->user()->isAdmin() || $lineup->canBeSubmittedByClub())
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

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteLineupModal',
    'title' => 'Hapus DSP',
    'formId' => 'delete-lineup-form',
    'nameClass' => 'js-delete-lineup-name',
    'messagePrefix' => 'DSP',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
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
        const selectedCount = getRows().filter((checkbox) => checkbox.checked).length;

        if (selectedCount === 0) {
            event.preventDefault();
        }
    });

    syncBulkState();
});
</script>
@endpush

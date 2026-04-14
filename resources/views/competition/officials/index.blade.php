@extends('layouts.vertical', ['title' => $title])

@php
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

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Official</li>
            </ol>
        </nav>
        <h4 class="mb-1">Official</h4>
        <p class="text-muted mb-0">Kelola data official tiap klub dengan panel yang lebih rapi untuk filter, review, dan export ID card.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
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
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i data-lucide="id-card" class="fs-14"></i>
                <span>Preview ID Card</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach ($ageGroups as $ageGroup)
                    <li>
                        <a class="dropdown-item" target="_blank" href="{{ route('officials.id-cards', ['ageGroup' => $ageGroup->id, 'club_id' => request('club_id')]) }}">
                            {{ $ageGroup->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @include('competition.partials.icon-button', [
            'href' => route('officials.create'),
            'icon' => 'user-plus',
            'label' => 'Tambah Official',
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
                        <h3 class="mb-1">{{ $officials->total() }}</h3>
                        <p class="text-muted mb-0">Total official terdaftar</p>
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
                        <span class="badge bg-success-subtle text-success mb-2">Badge</span>
                        <h3 class="mb-1">{{ $activeCount }}</h3>
                        <p class="text-muted mb-2">Aktif di halaman ini</p>
                    </div>
                    <div class="avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="badge-check" class="fs-22 text-success"></i>
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
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge bg-info-subtle text-info mb-2">Progress</span>
                        <h3 class="mb-1">{{ $approvedCount }}</h3>
                        <p class="text-muted mb-2">Sudah disetujui</p>
                    </div>
                    <div class="avatar-md bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="shield-check" class="fs-22 text-info"></i>
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
            <h4 class="card-title mb-1">Daftar Official</h4>
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

    @if (auth()->user()->isAdmin())
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
                        Bulk Action Admin
                    </button>
                </h2>
                <div
                    id="bulkReviewCollapse"
                    class="accordion-collapse collapse"
                    aria-labelledby="bulkReviewHeading"
                    data-bs-parent="#officialAdminAccordion"
                >
                    <div class="accordion-body">
                        <form id="bulk-official-review-form" method="POST" action="{{ route('officials.bulk-review') }}" class="row g-3 align-items-start">
                            @csrf
                            <div class="col-lg-3">
                                <label for="bulk-status" class="form-label">Aksi</label>
                                <select id="bulk-status" name="status" class="form-select" required>
                                    <option value="">Bulk action admin</option>
                                    <option value="approved">Approve terpilih</option>
                                    <option value="revision">Minta revisi terpilih</option>
                                    <option value="rejected">Reject terpilih</option>
                                    <option value="deleted">Hapus terpilih</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label for="bulk-notes" class="form-label">Catatan verifikasi</label>
                                <textarea id="bulk-notes" name="verification_notes" rows="2" class="form-control" placeholder="Wajib untuk revisi atau reject."></textarea>
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
                            <input type="checkbox" class="form-check-input js-check-all" data-target=".js-official-row">
                        </th>
                        @endif
                        @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Official', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'club', 'label' => 'Klub', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'role', 'label' => 'Peran', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Usia', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'email', 'label' => 'Kontak', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'is_active', 'label' => 'Status', 'defaultSort' => 'created_at'])
                        @include('competition.partials.sortable-th', ['key' => 'verification_status', 'label' => 'Verifikasi', 'defaultSort' => 'created_at'])
                        <th class="text-end">Aksi</th>
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
                            @if (auth()->user()->isAdmin())
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input js-official-row" name="selected_ids[]" value="{{ $official->id }}" form="bulk-official-review-form">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold">
                                        {{ $initials ?: 'OF' }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $official->name }}</div>
                                        <div class="text-muted small">{{ $official->email ?: 'Email belum tersedia' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $official->club?->name ?: '-' }}</div>
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
                                <span class="badge {{ $official->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
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
                                                'href' => route('officials.show', $official),
                                                'icon' => 'eye',
                                                'label' => 'Detail',
                                            ])
                                            @if (auth()->user()->isAdmin() || $official->canBeEditedByClub())
                                                @include('competition.partials.action-item', [
                                                    'href' => route('officials.edit', $official),
                                                    'icon' => 'square-pen',
                                                    'label' => 'Edit',
                                                ])
                                            @endif
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="competition-action-section">
                                            @include('competition.partials.review-actions', [
                                                'item' => $official,
                                                'submitRoute' => route('officials.submit', $official),
                                                'reviewRoute' => route('officials.review', $official),
                                            ])
                                        </div>
                                        @if (auth()->user()->isAdmin() || $official->canBeSubmittedByClub())
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
                            <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="competition-table-empty py-5">
                                <div class="d-flex flex-column align-items-center gap-2 text-muted">
                                    <div class="avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center">
                                        <i data-lucide="inbox" class="fs-22"></i>
                                    </div>
                                    <div class="fw-semibold text-dark">Belum ada data official.</div>
                                    <div class="small">Ubah filter atau tambahkan official baru untuk mulai mengisi daftar ini.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
        <p class="text-muted mb-0">Menampilkan {{ $officials->firstItem() ?? 0 }}-{{ $officials->lastItem() ?? 0 }} dari {{ $officials->total() }} official.</p>
        <div>{{ $officials->links() }}</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="officialFilterCanvas" aria-labelledby="officialFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="officialFilterCanvasLabel">Filter Official</h5>
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
    'title' => 'Hapus Official',
    'formId' => 'delete-official-form',
    'nameClass' => 'js-delete-official-name',
    'messagePrefix' => 'Official',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        bootstrap.Tooltip.getOrCreateInstance(element);
    });

    const checkAll = document.querySelector('.js-check-all');

    if (checkAll) {
        const rows = document.querySelectorAll(checkAll.dataset.target);

        checkAll.addEventListener('change', function () {
            rows.forEach((checkbox) => {
                checkbox.checked = checkAll.checked;
            });
        });
    }

});
</script>
@endpush

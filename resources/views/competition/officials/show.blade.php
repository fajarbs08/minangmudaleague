@extends('layouts.vertical', ['title' => $title])

@section('content')
@php
    $isHistoryView = app(\App\Services\SeasonContext::class)->isViewingHistory();
    $canManageAgeRegistrations = ! $isHistoryView && (auth()->user()->isAdmin() || $official->canBeEditedByClub());
    $canDownloadIdCard = auth()->user()->isAdmin() || $official->canClubAccessIdCard();
    $idCardAgeGroupId = $official->preferredIdCardAgeGroupId();
@endphp
@push('css')
<style>
    .lap-detail-deferred {
        content-visibility: auto;
        contain-intrinsic-size: 1px 760px;
    }
</style>
@endpush
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 lap-detail-head">
    <div>
        <h4 class="mb-1">Detail Ofisial</h4>
        <p class="text-muted mb-0">{{ $official->name }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap lap-detail-actions">
        @if (! $isHistoryView && (auth()->user()->isAdmin() || $official->canBeEditedByClub()))
            <a href="{{ route('officials.edit', $official) }}" class="btn btn-light d-inline-flex align-items-center gap-2 lap-detail-action-btn">
                <i data-lucide="square-pen" class="fs-14"></i>
                <span>Edit</span>
            </a>
        @endif
        @if (! $isHistoryView && $idCardAgeGroupId)
            @if ($canDownloadIdCard)
                <a href="{{ route('officials.id-card', [$official, $idCardAgeGroupId]) }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 lap-detail-action-btn">
                    <i data-lucide="id-card" class="fs-14"></i>
                    <span>Unduh ID Card</span>
                </a>
            @else
                <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 lap-detail-action-btn" disabled>
                    <i data-lucide="id-card" class="fs-14"></i>
                    <span>Unduh ID Card</span>
                </button>
            @endif
        @endif
        <a href="{{ route('officials.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 lap-detail-action-btn lap-detail-action-btn-back">
            <i data-lucide="arrow-left" class="fs-14"></i>
            <span>Kembali</span>
        </a>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                @if ($official->photo_file_url)
                    <img src="{{ $official->photo_file_url }}" alt="{{ $official->name }}" class="img-fluid rounded border mb-3" width="280" height="280" decoding="async" fetchpriority="high" style="max-height: 280px;">
                @else
                    <div class="border rounded d-flex align-items-center justify-content-center text-muted mb-3" style="height: 280px;">
                        Belum ada foto
                    </div>
                @endif
                <h5 class="mb-1">{{ $official->name }}</h5>
                <div class="text-muted">{{ $isHistoryView ? ($official->seasonClub?->name ?: '-') : ($official->club?->name ?: '-') }}</div>
                <div class="mt-3">@include('competition.partials.status-badge', ['status' => $official->verification_status])</div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Data Ofisial</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted small">Nama</div><div class="fw-semibold">{{ $official->name }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Peran</div><div class="fw-semibold">{{ $official->role }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Telepon</div><div class="fw-semibold">{{ $official->phone ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Email</div><div class="fw-semibold">{{ $official->email ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Tempat Lahir</div><div class="fw-semibold">{{ $official->birth_place ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Tanggal Lahir</div><div class="fw-semibold">{{ optional($official->birth_date)->format('d M Y') ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Kewarganegaraan</div><div class="fw-semibold">{{ $official->citizenship ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">NIK / Identitas</div><div class="fw-semibold">{{ $official->identity_number ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Lisensi</div><div class="fw-semibold">{{ $official->license_levels ?: $official->license_number ?: '-' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Catatan</div><div class="fw-semibold">{{ $official->notes ?: '-' }}</div></div>
                </div>
            </div>
        </div>

        <div class="card mt-4 lap-detail-deferred">
            <div class="card-header">
                <h4 class="card-title mb-0">Detail Kelompok Usia</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table competition-table-compact align-middle">
                        <thead>
                            <tr>
                                <th>Kelompok Usia</th>
                                <th>Season</th>
                                <th>Jabatan</th>
                                <th>Lisensi</th>
                                <th>Status</th>
                                <th>Status Date</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($official->ageRegistrations as $registration)
                                <tr>
                                    <td>{{ $registration->ageGroup?->name ?: '-' }}</td>
                                    <td>{{ $registration->season ?: '-' }}</td>
                                    <td>{{ $registration->role ?: '-' }}</td>
                                    <td>{{ $registration->license_levels ?: '-' }}</td>
                                    <td>@include('competition.partials.status-badge', ['status' => $registration->registration_status ?: $official->verification_status])</td>
                                    <td>{{ optional($registration->status_date)->format('d M Y H:i') ?: '-' }}</td>
                                    <td>{{ $registration->notes ?: '-' }}</td>
                                    <td>
                                        @if ($canManageAgeRegistrations)
                                            <div class="d-flex gap-1">
                                                <a
                                                    href="{{ route('officials.edit', $official) }}#age-registrations"
                                                    class="btn btn-sm btn-outline-primary px-2"
                                                    title="Edit kelompok usia"
                                                    aria-label="Edit kelompok usia"
                                                >
                                                    <i data-lucide="square-pen" class="fs-14"></i>
                                                </a>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger js-delete-official-age px-2"
                                                    title="Hapus kelompok usia"
                                                    aria-label="Hapus kelompok usia"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteOfficialAgeModal"
                                                    data-action="{{ route('officials.age-registrations.destroy', [$official, $registration->age_group_id]) }}"
                                                    data-name="{{ $registration->ageGroup?->name ?: 'Kelompok usia' }}"
                                                >
                                                    <i data-lucide="trash-2" class="fs-14"></i>
                                                </button>
                                            </div>
                                        @elseif ($isHistoryView)
                                            <span class="text-muted small">Read-only histori</span>
                                        @else
                                            <span class="text-muted small">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="competition-table-empty">Belum ada detail kelompok usia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-4 lap-detail-deferred">
            <div class="card-header">
                <h4 class="card-title mb-0">Berkas Ofisial</h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @if ($official->license_file_url)
                        <a href="{{ $official->license_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i data-lucide="file-text" class="fs-14"></i>
                            <span>Lihat Lisensi</span>
                        </a>
                    @endif
                    @if ($official->identity_file_url)
                        <a href="{{ $official->identity_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i data-lucide="file-text" class="fs-14"></i>
                            <span>Lihat Identitas</span>
                        </a>
                    @endif
                    @unless ($official->license_file_url || $official->identity_file_url)
                        <div class="text-muted">Belum ada berkas.</div>
                    @endunless
                </div>
            </div>
        </div>

        @unless ($isHistoryView)
            @include('competition.partials.workflow-panel', [
                'item' => $official,
                'submitRoute' => route('officials.submit', $official),
                'reviewRoute' => route('officials.review', $official),
            ])
        @endunless
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteOfficialAgeModal',
    'title' => 'Hapus Kelompok Usia',
    'formId' => 'delete-official-age-form',
    'nameClass' => 'js-delete-official-age-name',
    'messagePrefix' => 'Kelompok usia',
    'messageSuffix' => 'akan dihapus dari ofisial ini. Tindakan ini tidak bisa dibatalkan.',
])
@endsection

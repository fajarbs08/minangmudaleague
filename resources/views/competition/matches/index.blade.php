@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['club_id', 'age_group_id', 'lineup_status', 'competition_format']))->filter(fn ($value) => filled($value))->count();
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Jadwal Pertandingan</li>
            </ol>
        </nav>
        <h4 class="mb-1">Jadwal Pertandingan</h4>
        <p class="text-muted mb-0">Admin menentukan lawan, lokasi, tanggal, jam, dan hari pertandingan untuk dipakai di DSP.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#matchFilterCanvas"
            aria-controls="matchFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
        <a href="{{ route('matches.create') }}" class="btn btn-primary">Tambah Jadwal</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="card">
    <form id="bulk-match-delete-form" method="POST" action="{{ route('matches.bulk-delete', request()->only(['club_id', 'age_group_id', 'lineup_status', 'competition_format', 'sort', 'direction'])) }}">
        @csrf
        <div class="card-body border-bottom bg-light-subtle">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="card-title mb-1">Hapus Jadwal Massal</h4>
                    <p class="text-muted mb-0">Pilih jadwal yang belum dipakai DSP untuk dihapus sekaligus.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="small text-muted"><span data-bulk-selected-count>0</span> jadwal dipilih di halaman ini.</div>
                    <button type="submit" class="btn btn-dark" data-bulk-submit disabled>Hapus Jadwal Terpilih</button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 64px;">
                            <input type="checkbox" class="form-check-input js-check-all">
                        </th>
                        @include('competition.partials.sortable-th', ['key' => 'match_day', 'label' => 'Hari Pertandingan', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'matchup', 'label' => 'Pertandingan', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Kelompok Usia', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'competition_format', 'label' => 'Format', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'round_order', 'label' => 'Babak', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'venue', 'label' => 'Lokasi', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'match_date', 'label' => 'Tanggal', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'kickoff_time', 'label' => 'Jam', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        <th>Status DSP</th>
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matches as $match)
                        @php
                            $lineupClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id);
                            $clubALineup = $match->lineupLists->firstWhere('club_id', $match->club_a_id);
                            $clubBLineup = $match->lineupLists->firstWhere('club_id', $match->club_b_id);
                            $clubAReady = $lineupClubIds->contains((int) $match->club_a_id);
                            $clubBReady = $lineupClubIds->contains((int) $match->club_b_id);
                            $isComplete = $clubAReady && $clubBReady;
                            $canBulkDelete = $match->lineupLists->isEmpty();
                        @endphp
                        <tr>
                            <td class="text-center">
                                @if ($canBulkDelete)
                                    <input type="checkbox" class="form-check-input js-match-row" name="selected_ids[]" value="{{ $match->id }}">
                                @else
                                    <span class="badge bg-light text-dark border">DSP</span>
                                @endif
                            </td>
                            <td>{{ $match->match_day }}</td>
                            <td>{{ $match->clubA?->name }} vs {{ $match->clubB?->name }}</td>
                            <td>{{ $match->ageGroup?->name }}</td>
                            <td>
                                <span class="badge {{ $match->competition_format === 'knockout' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }}">
                                    {{ $match->competition_format_label }}
                                </span>
                            </td>
                            <td>{{ $match->round_display_label }}</td>
                            <td>{{ $match->venue }}</td>
                            <td>{{ optional($match->match_date)->format('d M Y') }}</td>
                            <td>{{ optional($match->kickoff_time)->format('H:i') }} WIB</td>
                            <td>
                                <span class="badge {{ $isComplete ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $isComplete ? 'Lengkap' : 'Belum lengkap' }}
                                </span>
                                <div class="small mt-2 d-flex flex-column gap-1">
                                    <div>
                                        <span class="text-muted">{{ $match->clubA?->short_name ?: $match->clubA?->name }}:</span>
                                        @if ($clubALineup)
                                            <a href="{{ route('lineup-lists.show', $clubALineup) }}" class="fw-semibold">Lihat DSP</a>
                                        @else
                                            <span class="text-muted">belum ada</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-muted">{{ $match->clubB?->short_name ?: $match->clubB?->name }}:</span>
                                        @if ($clubBLineup)
                                            <a href="{{ route('lineup-lists.show', $clubBLineup) }}" class="fw-semibold">Lihat DSP</a>
                                        @else
                                            <span class="text-muted">belum ada</span>
                                        @endif
                                    </div>
                                </div>
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
                                            <div class="competition-action-label px-2 pb-2">Jadwal Pertandingan</div>
                                            <div class="small text-muted px-2 pb-2 text-wrap">Kelola detail jadwal atau hapus jadwal yang belum dipakai DSP.</div>
                                            @include('competition.partials.action-item', [
                                                'href' => route('matches.edit', $match),
                                                'icon' => 'square-pen',
                                                'label' => 'Edit Jadwal',
                                            ])
                                            @include('competition.partials.action-item', [
                                                'icon' => 'trash-2',
                                                'label' => 'Hapus Jadwal',
                                                'class' => 'text-danger js-delete-match',
                                                'attributes' => [
                                                    'data-bs-toggle' => 'modal',
                                                    'data-bs-target' => '#deleteMatchModal',
                                                    'data-action' => route('matches.destroy', $match),
                                                    'data-name' => $match->clubA?->name.' vs '.$match->clubB?->name,
                                                ],
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="competition-table-empty">Belum ada jadwal pertandingan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 px-3 pb-3">{{ $matches->links() }}</div>
        </div>
    </form>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="matchFilterCanvas" aria-labelledby="matchFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="matchFilterCanvasLabel">Filter Jadwal Pertandingan</h5>
            <p class="text-muted mb-0 small">Pakai filter detail tanpa memenuhi area tabel.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="match-club-id" class="form-label">Klub</label>
                <select id="match-club-id" name="club_id" class="form-select">
                    <option value="">Semua klub</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((string) request('club_id') === (string) $club->id)>{{ $club->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="match-age-group-id" class="form-label">Kelompok usia</label>
                <select id="match-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="match-competition-format" class="form-label">Format pertandingan</label>
                <select id="match-competition-format" name="competition_format" class="form-select">
                    <option value="">Semua format</option>
                    @foreach ($formatOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_format') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="match-lineup-status" class="form-label">Status DSP</label>
                <select id="match-lineup-status" name="lineup_status" class="form-select">
                    <option value="">Semua status DSP</option>
                    <option value="pending" @selected(request('lineup_status') === 'pending')>Belum lengkap DSP</option>
                    <option value="complete" @selected(request('lineup_status') === 'complete')>Lengkap DSP</option>
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ route('matches.index') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteMatchModal',
    'title' => 'Hapus Jadwal Pertandingan',
    'formId' => 'delete-match-form',
    'nameClass' => 'js-delete-match-name',
    'messagePrefix' => 'Jadwal',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

@include('layouts.partials.action-confirm-modal', [
    'modalId' => 'bulkMatchActionConfirmModal',
    'title' => 'Hapus Jadwal Terpilih',
    'message' => 'Jadwal terpilih akan dihapus. Tindakan ini tidak bisa dibatalkan.',
    'submitLabel' => 'Hapus',
    'submitClass' => 'btn-danger',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.querySelector('#bulk-match-delete-form');
    if (!bulkForm) {
        return;
    }

    const checkAll = bulkForm.querySelector('.js-check-all');
    const bulkSubmit = bulkForm.querySelector('[data-bulk-submit]');
    const bulkCount = bulkForm.querySelector('[data-bulk-selected-count]');
    const bulkConfirmModal = document.getElementById('bulkMatchActionConfirmModal');

    if (!checkAll) {
        return;
    }

    const getRows = () => Array.from(bulkForm.querySelectorAll('.js-match-row'));

    const syncBulkState = () => {
        const rows = getRows();
        const selectedCount = rows.filter((checkbox) => checkbox.checked).length;

        if (bulkCount) {
            bulkCount.textContent = selectedCount;
        }

        if (bulkSubmit) {
            bulkSubmit.disabled = selectedCount === 0;
        }

        checkAll.disabled = rows.length === 0;
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
        if (event.target.matches('.js-match-row')) {
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

        event.preventDefault();

        if (!bulkConfirmModal) {
            return;
        }

        bulkConfirmModal.setAttribute('data-confirm-form', '#bulk-match-delete-form');
        bulkConfirmModal.setAttribute('data-confirm-title', 'Hapus Jadwal Terpilih');
        bulkConfirmModal.setAttribute('data-confirm-message', `Hapus ${selectedCount} jadwal terpilih? Data yang sudah dihapus tidak bisa dikembalikan.`);
        bulkConfirmModal.setAttribute('data-confirm-submit-label', 'Hapus');
        bulkConfirmModal.setAttribute('data-confirm-submit-class', 'btn-danger');

        bootstrap.Modal.getOrCreateInstance(bulkConfirmModal).show();
    });

    syncBulkState();
});
</script>
@endpush

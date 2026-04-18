@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Detail Pemain</h4>
        <p class="text-muted mb-0">{{ $player->name }}</p>
    </div>
    <div class="d-flex gap-2">
        @if (auth()->user()->isAdmin() || $player->canBeEditedByClub())
            <a href="{{ route('players.edit', $player) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
                <i data-lucide="square-pen" class="fs-14"></i>
                <span>Edit</span>
            </a>
        @endif
        @if ($player->ageRegistrations->isNotEmpty())
            <a href="{{ route('players.id-card', [$player, $player->ageRegistrations->first()->age_group_id]) }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="id-card" class="fs-14"></i>
                <span>Lihat ID Card</span>
            </a>
        @endif
        @if (auth()->user()->isAdmin() || $player->canBeSubmittedByClub())
            <button
                type="button"
                class="btn btn-danger js-delete-player-detail d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal"
                data-bs-target="#deletePlayerDetailModal"
                data-action="{{ route('players.destroy', $player) }}"
                data-name="{{ $player->name }}"
            >
                <i data-lucide="trash-2" class="fs-14"></i>
                <span>Hapus</span>
            </button>
        @endif
        <a href="{{ route('players.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
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
                @if ($player->photo_file_url)
                    <img src="{{ $player->photo_file_url }}" alt="{{ $player->name }}" class="img-fluid rounded border mb-3" style="max-height: 280px;">
                @else
                    <div class="border rounded d-flex align-items-center justify-content-center text-muted mb-3" style="height: 280px;">
                        Belum ada foto
                    </div>
                @endif
                <h5 class="mb-1">{{ $player->name }}</h5>
                <div class="text-muted">{{ $player->club?->name ?: '-' }}</div>
                <div class="mt-3">@include('competition.partials.status-badge', ['status' => $player->verification_status])</div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Data Pemain</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted small">Nama Lengkap</div><div class="fw-semibold">{{ $player->name }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Nama Ibu Kandung</div><div class="fw-semibold">{{ $player->mother_name ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Sekolah</div><div class="fw-semibold">{{ $player->school_name ?: '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Kewarganegaraan</div><div class="fw-semibold">{{ $player->citizenship ?: '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Tempat Lahir</div><div class="fw-semibold">{{ $player->birth_place ?: '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Tanggal Lahir</div><div class="fw-semibold">{{ optional($player->birth_date)->format('d M Y') ?: '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Tinggi</div><div class="fw-semibold">{{ $player->height_cm ? $player->height_cm.' cm' : '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Berat</div><div class="fw-semibold">{{ $player->weight_kg ? $player->weight_kg.' kg' : '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Dominant Foot</div><div class="fw-semibold">{{ $player->dominant_foot ?: '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Kapten</div><div class="fw-semibold">{{ $player->is_captain ? 'Ya' : 'Tidak' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Catatan</div><div class="fw-semibold">{{ $player->notes ?: '-' }}</div></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Detail Kelompok Usia</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table competition-table-compact align-middle" data-update-url="{{ route('players.age-registrations.update', [$player, 'AGE_GROUP_ID']) }}">
                        <thead>
                            <tr>
                                <th>Kelompok Usia</th>
                                <th>Season</th>
                                <th>No. Punggung</th>
                                <th>Posisi</th>
                                <th>Status</th>
                                <th>Status Date</th>
                                <th>Starter</th>
                                <th>Cadangan</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($player->ageRegistrations as $registration)
                                <tr>
                                    <td>{{ $registration->ageGroup?->name ?: '-' }}</td>
                                    <td>{{ $registration->season ?: '-' }}</td>
                                    <td>{{ $registration->jersey_number ?: '-' }}</td>
                                    <td>{{ $registration->position ?: '-' }}</td>
                                    <td>@include('competition.partials.status-badge', ['status' => $registration->registration_status ?: $player->verification_status])</td>
                                    <td>{{ optional($registration->status_date)->format('d M Y H:i') ?: '-' }}</td>
                                    <td>
                                        <input
                                            type="checkbox"
                                            class="form-check-input js-toggle"
                                            data-age-group-id="{{ $registration->age_group_id }}"
                                            data-role="starter"
                                            @checked($registration->is_starter)
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="checkbox"
                                            class="form-check-input js-toggle"
                                            data-age-group-id="{{ $registration->age_group_id }}"
                                            data-role="substitute"
                                            @checked($registration->is_substitute)
                                        >
                                    </td>
                                    <td>
                                        {{ $registration->notes ?: '-' }}
                                        <div class="text-danger small mt-1 d-none" data-age-error></div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-edit-age px-2"
                                                title="Edit kelompok usia"
                                                aria-label="Edit kelompok usia"
                                                data-age-group-id="{{ $registration->age_group_id }}"
                                                data-season="{{ $registration->season }}"
                                                data-jersey-number="{{ $registration->jersey_number }}"
                                                data-position="{{ $registration->position }}"
                                                data-notes="{{ $registration->notes }}"
                                            >
                                                <i data-lucide="square-pen" class="fs-14"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-danger js-delete-player-age px-2"
                                                title="Hapus kelompok usia"
                                                aria-label="Hapus kelompok usia"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePlayerAgeModal"
                                                data-action="{{ route('players.age-registrations.destroy', [$player, $registration->age_group_id]) }}"
                                                data-name="{{ $registration->ageGroup?->name ?: 'Kelompok usia' }}"
                                            >
                                                <i data-lucide="trash-2" class="fs-14"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="competition-table-empty">Belum ada detail kelompok usia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editAgeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editAgeForm">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Kelompok Usia</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Kelompok Usia</label>
                                <input type="text" class="form-control" id="editAgeGroupName" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Season</label>
                                <input type="text" class="form-control" name="season" id="editSeason">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor Punggung</label>
                                <input type="number" class="form-control" name="jersey_number" id="editJersey">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Posisi</label>
                                <input type="text" class="form-control" name="position" id="editPosition">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <input type="text" class="form-control" name="notes" id="editNotes">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('competition.partials.delete-modal', [
            'modalId' => 'deletePlayerDetailModal',
            'title' => 'Hapus Pemain',
            'formId' => 'delete-player-detail-form',
            'nameClass' => 'js-delete-player-detail-name',
            'messagePrefix' => 'Pemain',
            'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
        ])

        @include('competition.partials.delete-modal', [
            'modalId' => 'deletePlayerAgeModal',
            'title' => 'Hapus Kelompok Usia',
            'formId' => 'delete-player-age-form',
            'nameClass' => 'js-delete-player-age-name',
            'messagePrefix' => 'Kelompok usia',
            'messageSuffix' => 'akan dihapus dari pemain ini. Tindakan ini tidak bisa dibatalkan.',
        ])

        <div class="card mt-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Berkas Pemain</h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @if ($player->family_card_file_url)
                        <a href="{{ $player->family_card_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i data-lucide="file-text" class="fs-14"></i>
                            <span>Lihat KK</span>
                        </a>
                    @endif
                    @if ($player->diploma_file_url)
                        <a href="{{ $player->diploma_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i data-lucide="file-text" class="fs-14"></i>
                            <span>Lihat Ijazah</span>
                        </a>
                    @endif
                    @if ($player->report_file_url)
                        <a href="{{ $player->report_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i data-lucide="file-text" class="fs-14"></i>
                            <span>Lihat Rapor</span>
                        </a>
                    @endif
                    @if ($player->birth_certificate_file_url)
                        <a href="{{ $player->birth_certificate_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                            <i data-lucide="file-text" class="fs-14"></i>
                            <span>Lihat Akta</span>
                        </a>
                    @endif
                    @unless ($player->family_card_file_url || $player->diploma_file_url || $player->report_file_url || $player->birth_certificate_file_url)
                        <div class="text-muted">Belum ada berkas.</div>
                    @endunless
                </div>
            </div>
        </div>

        @include('competition.partials.workflow-panel', [
            'item' => $player,
            'submitRoute' => route('players.submit', $player),
            'reviewRoute' => route('players.review', $player),
        ])
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const pendingTimers = new Map();
    const pendingUpdates = new Map();

    const table = document.querySelector('table[data-update-url]');

    const sendUpdate = async ({ row, role, updateUrl }, options = {}) => {
        const starter = row?.querySelector('[data-role="starter"]');
        const substitute = row?.querySelector('[data-role="substitute"]');
        const errorNode = row?.querySelector('[data-age-error]');
        const payload = {
            is_starter: starter?.checked ? 1 : 0,
            is_substitute: substitute?.checked ? 1 : 0,
        };

        if (errorNode) {
            errorNode.textContent = '';
            errorNode.classList.add('d-none');
        }

        try {
            const res = await fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                },
                body: JSON.stringify(payload),
                keepalive: !!options.keepalive,
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                if (role === 'starter' && starter) starter.checked = !starter.checked;
                if (role === 'substitute' && substitute) substitute.checked = !substitute.checked;
                if (errorNode) {
                    errorNode.textContent = data.message || 'Gagal menyimpan perubahan.';
                    errorNode.classList.remove('d-none');
                }
            }
        } catch {
            if (role === 'starter' && starter) starter.checked = !starter.checked;
            if (role === 'substitute' && substitute) substitute.checked = !substitute.checked;
            if (errorNode) {
                errorNode.textContent = 'Gagal menyimpan perubahan.';
                errorNode.classList.remove('d-none');
            }
        }
    };

    const flushPending = () => {
        for (const [ageGroupId, entry] of pendingUpdates.entries()) {
            if (pendingTimers.has(ageGroupId)) {
                clearTimeout(pendingTimers.get(ageGroupId));
                pendingTimers.delete(ageGroupId);
            }
            pendingUpdates.delete(ageGroupId);
            sendUpdate(entry, { keepalive: true });
        }
    };

    window.addEventListener('beforeunload', flushPending);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            flushPending();
        }
    });

    document.querySelectorAll('.js-toggle').forEach((checkbox) => {
        checkbox.addEventListener('change', async function () {
            const ageGroupId = this.dataset.ageGroupId;
            const role = this.dataset.role;
            const row = this.closest('tr');
            const starter = row?.querySelector('[data-role="starter"]');
            const substitute = row?.querySelector('[data-role="substitute"]');
            const errorNode = row?.querySelector('[data-age-error]');
            const updateUrl = table?.dataset.updateUrl?.replace('AGE_GROUP_ID', ageGroupId);

            if (!updateUrl) {
                return;
            }

            // Enforce mutual exclusivity client-side for smoother UX.
            if (role === 'starter' && starter?.checked) {
                if (substitute) substitute.checked = false;
            }
            if (role === 'substitute' && substitute?.checked) {
                if (starter) starter.checked = false;
            }

            if (pendingTimers.has(ageGroupId)) {
                clearTimeout(pendingTimers.get(ageGroupId));
            }

            const timer = setTimeout(async () => {
                pendingUpdates.delete(ageGroupId);
                await sendUpdate({ row, role, updateUrl });
            }, 350);

            pendingTimers.set(ageGroupId, timer);
            pendingUpdates.set(ageGroupId, { row, role, updateUrl });

        });
    });

    const modalEl = document.getElementById('editAgeModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('editAgeForm');
    const nameInput = document.getElementById('editAgeGroupName');
    const seasonInput = document.getElementById('editSeason');
    const jerseyInput = document.getElementById('editJersey');
    const positionInput = document.getElementById('editPosition');
    const notesInput = document.getElementById('editNotes');

    document.querySelectorAll('.js-edit-age').forEach((button) => {
        button.addEventListener('click', () => {
            const ageGroupId = button.dataset.ageGroupId;
            const ageGroupName = button.closest('tr')?.querySelector('td')?.textContent?.trim() ?? '-';
            form.action = `{{ route('players.age-registrations.update', [$player, 'AGE_GROUP_ID']) }}`.replace('AGE_GROUP_ID', ageGroupId);
            nameInput.value = ageGroupName;
            seasonInput.value = button.dataset.season || '';
            jerseyInput.value = button.dataset.jerseyNumber || '';
            positionInput.value = button.dataset.position || '';
            notesInput.value = button.dataset.notes || '';
            modal.show();
        });
    });
});
</script>
@endpush
@endsection

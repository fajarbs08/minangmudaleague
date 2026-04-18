@php
    $ageRegistrations = old('age_registrations', $player->exists
        ? $player->ageRegistrations->map(fn ($registration) => [
            'age_group_id' => $registration->age_group_id,
            'season' => $registration->season,
            'jersey_number' => $registration->jersey_number,
            'position' => $registration->position,
            'notes' => $registration->notes,
            'is_starter' => $registration->is_starter,
            'is_substitute' => $registration->is_substitute,
        ])->values()->all()
        : [[
            'age_group_id' => old('primary_age_group_id', $player->primary_age_group_id),
            'season' => date('Y'),
            'jersey_number' => old('jersey_number', $player->jersey_number),
            'position' => old('position', $player->position),
            'notes' => null,
            'is_starter' => false,
            'is_substitute' => false,
        ]]);
    $uploadHelp = 'Foto: JPG, PNG, atau WebP, maks. 3 MB. Dokumen: PDF, JPG, PNG, atau WebP, maks. 4 MB.';
    $requiresPhotoUpload = blank($player->photo_path);
    $requiresBirthCertificateUpload = blank($player->birth_certificate_file_path);
    $requiresFamilyCardUpload = blank($player->family_card_file_path);
    $requiresDiplomaUpload = blank($player->diploma_file_path);
    $requiresReportUpload = blank($player->report_file_path);
@endphp

<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>
<div class="text-muted small mb-3">{{ $uploadHelp }}</div>
<div class="row">
    <div class="col-lg-6 mb-3">
        <label class="form-label">Klub <span class="text-danger">*</span></label>
        <select name="club_id" class="form-select" required>
            <option value="">Pilih klub</option>
            @foreach ($clubs as $club)
                <option value="{{ $club->id }}" @selected(old('club_id', $player->club_id) == $club->id)>{{ $club->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Pemain <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $player->name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Ibu Kandung <span class="text-danger">*</span></label>
        <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $player->mother_name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Sekolah <span class="text-danger">*</span></label>
        <input type="text" name="school_name" class="form-control" value="{{ old('school_name', $player->school_name) }}" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Pas Foto 3x4 <span class="text-danger">*</span></label>
        <input type="file" name="photo_file" class="form-control" accept=".jpg,.jpeg,.png,.webp" {{ $requiresPhotoUpload ? 'required' : '' }}>
        @if ($player->photo_file_url)
            <a href="{{ $player->photo_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="image" class="fs-14"></i>
                <span>Lihat foto</span>
            </a>
        @endif
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">File KK <span class="text-danger">*</span></label>
        <input type="file" name="family_card_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $requiresFamilyCardUpload ? 'required' : '' }}>
        @if ($player->family_card_file_url)
            <a href="{{ $player->family_card_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat KK</span>
            </a>
        @endif
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">File Ijazah <span class="text-danger">*</span></label>
        <input type="file" name="diploma_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $requiresDiplomaUpload ? 'required' : '' }}>
        @if ($player->diploma_file_url)
            <a href="{{ $player->diploma_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat ijazah</span>
            </a>
        @endif
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">File Rapor <span class="text-danger">*</span></label>
        <input type="file" name="report_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $requiresReportUpload ? 'required' : '' }}>
        @if ($player->report_file_url)
            <a href="{{ $player->report_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat rapor</span>
            </a>
        @endif
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Akta Kelahiran <span class="text-danger">*</span></label>
        <input type="file" name="birth_certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $requiresBirthCertificateUpload ? 'required' : '' }}>
        @if ($player->birth_certificate_file_url)
            <a href="{{ $player->birth_certificate_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat akta</span>
            </a>
        @endif
    </div>
    <div class="col-lg-3 mb-3">
        <label class="form-label">Tinggi (cm)</label>
        <input type="number" name="height_cm" class="form-control" value="{{ old('height_cm', $player->height_cm) }}">
    </div>
    <div class="col-lg-3 mb-3">
        <label class="form-label">Berat (kg)</label>
        <input type="number" name="weight_kg" class="form-control" value="{{ old('weight_kg', $player->weight_kg) }}">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kewarganegaraan <span class="text-danger">*</span></label>
        <select name="citizenship" class="form-select" required>
            <option value="">Pilih status</option>
            <option value="WNI" @selected(old('citizenship', $player->citizenship) === 'WNI')>WNI</option>
            <option value="WNA" @selected(old('citizenship', $player->citizenship) === 'WNA')>WNA</option>
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
        <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place', $player->birth_place) }}" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($player->birth_date)->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Dominant Foot</label>
        <select name="dominant_foot" class="form-select">
            @php($dominantFoot = old('dominant_foot', $player->dominant_foot))
            <option value="">Pilih</option>
            <option value="Kanan" @selected($dominantFoot === 'Kanan')>Kanan</option>
            <option value="Kiri" @selected($dominantFoot === 'Kiri')>Kiri</option>
            <option value="Keduanya" @selected($dominantFoot === 'Keduanya')>Keduanya</option>
        </select>
    </div>
    <div class="col-lg-8 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_captain" name="is_captain" value="1" @checked(old('is_captain', $player->is_captain))>
            <label class="form-check-label" for="is_captain">Tandai sebagai kapten tim</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $player->notes) }}</textarea>
    </div>
</div>

<div class="card border mt-4" id="age-registrations">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h5 class="mb-1">Kelompok Usia</h5>
                <div class="text-muted">Satu pemain bisa terdaftar di lebih dari satu kelompok usia, dengan nomor punggung dan posisi yang berbeda.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2" data-add-age-row>
                <i data-lucide="plus" class="fs-14"></i>
                <span>Tambah Usia</span>
            </button>
        </div>

        <div class="d-flex flex-column gap-3" data-age-registration-list>
            @foreach ($ageRegistrations as $index => $registration)
                <div class="border rounded p-3" data-age-registration-row>
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Kelompok Usia <span class="text-danger">*</span></label>
                            <select name="age_registrations[{{ $index }}][age_group_id]" class="form-select" required>
                                <option value="">Pilih kelompok usia</option>
                                @foreach ($ageGroups as $ageGroup)
                                    <option value="{{ $ageGroup->id }}" @selected((string) ($registration['age_group_id'] ?? '') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Season</label>
                            <input type="text" name="age_registrations[{{ $index }}][season]" class="form-control" value="{{ $registration['season'] ?? date('Y') }}">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">No. Punggung</label>
                            <input type="number" name="age_registrations[{{ $index }}][jersey_number]" class="form-control" value="{{ $registration['jersey_number'] ?? '' }}" min="1" max="99">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Posisi</label>
                            @php($positionValue = $registration['position'] ?? '')
                            <select name="age_registrations[{{ $index }}][position]" class="form-select">
                                <option value="">Pilih posisi</option>
                                <option value="GK" @selected($positionValue === 'GK')>GK</option>
                                <option value="CB" @selected($positionValue === 'CB')>CB</option>
                                <option value="LB" @selected($positionValue === 'LB')>LB</option>
                                <option value="RB" @selected($positionValue === 'RB')>RB</option>
                                <option value="LWB" @selected($positionValue === 'LWB')>LWB</option>
                                <option value="RWB" @selected($positionValue === 'RWB')>RWB</option>
                                <option value="DM" @selected($positionValue === 'DM')>DM</option>
                                <option value="CM" @selected($positionValue === 'CM')>CM</option>
                                <option value="AM" @selected($positionValue === 'AM')>AM</option>
                                <option value="LM" @selected($positionValue === 'LM')>LM</option>
                                <option value="RM" @selected($positionValue === 'RM')>RM</option>
                                <option value="LW" @selected($positionValue === 'LW')>LW</option>
                                <option value="RW" @selected($positionValue === 'RW')>RW</option>
                                <option value="ST" @selected($positionValue === 'ST')>ST</option>
                                <option value="CF" @selected($positionValue === 'CF')>CF</option>
                                <option value="SS" @selected($positionValue === 'SS')>SS</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Notes</label>
                            <input type="text" name="age_registrations[{{ $index }}][notes]" class="form-control" value="{{ $registration['notes'] ?? '' }}">
                        </div>
                        <div class="col-lg-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="age_registrations[{{ $index }}][is_starter]" value="1" @checked(!empty($registration['is_starter']))>
                                <label class="form-check-label">Starter</label>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="age_registrations[{{ $index }}][is_substitute]" value="1" @checked(!empty($registration['is_substitute']))>
                                <label class="form-check-label">Cadangan</label>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <button type="button" class="btn btn-outline-danger w-100" data-remove-age-row>Hapus</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<template id="age-registration-template">
    <div class="border rounded p-3" data-age-registration-row>
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label">Kelompok Usia <span class="text-danger">*</span></label>
                <select data-name="age_group_id" class="form-select" required>
                    <option value="">Pilih kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}">{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Season</label>
                <input type="text" data-name="season" class="form-control" value="{{ date('Y') }}">
            </div>
            <div class="col-lg-2">
                <label class="form-label">No. Punggung</label>
                <input type="number" data-name="jersey_number" class="form-control" min="1" max="99">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Posisi</label>
                <select data-name="position" class="form-select">
                    <option value="">Pilih posisi</option>
                    <option value="GK">GK</option>
                    <option value="CB">CB</option>
                    <option value="LB">LB</option>
                    <option value="RB">RB</option>
                    <option value="LWB">LWB</option>
                    <option value="RWB">RWB</option>
                    <option value="DM">DM</option>
                    <option value="CM">CM</option>
                    <option value="AM">AM</option>
                    <option value="LM">LM</option>
                    <option value="RM">RM</option>
                    <option value="LW">LW</option>
                    <option value="RW">RW</option>
                    <option value="ST">ST</option>
                    <option value="CF">CF</option>
                    <option value="SS">SS</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Notes</label>
                <input type="text" data-name="notes" class="form-control">
            </div>
            <div class="col-lg-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" data-name="is_starter" value="1">
                    <label class="form-check-label">Starter</label>
                </div>
            </div>
            <div class="col-lg-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" data-name="is_substitute" value="1">
                    <label class="form-check-label">Cadangan</label>
                </div>
            </div>
            <div class="col-lg-1">
                <button type="button" class="btn btn-outline-danger w-100" data-remove-age-row>Hapus</button>
            </div>
        </div>
    </div>
</template>

<script>
(() => {
    const list = document.querySelector('[data-age-registration-list]');
    const addButton = document.querySelector('[data-add-age-row]');
    const template = document.getElementById('age-registration-template');

    if (!list || !addButton || !template) {
        return;
    }

    const reindex = () => {
        list.querySelectorAll('[data-age-registration-row]').forEach((row, index) => {
            row.querySelectorAll('[name], [data-name]').forEach((field) => {
                const key = field.getAttribute('data-name') ?? field.name.match(/\]\[([^\]]+)\]$/)?.[1];
                if (!key) {
                    return;
                }

                field.name = `age_registrations[${index}][${key}]`;
            });
        });
    };

    addButton.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        list.appendChild(fragment);
        reindex();
    });

    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-age-row]');
        if (!button) {
            return;
        }

        const rows = list.querySelectorAll('[data-age-registration-row]');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input, select').forEach((field) => {
                if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else {
                    field.value = '';
                }
            });
            return;
        }

        button.closest('[data-age-registration-row]')?.remove();
        reindex();
    });

    reindex();
})();
</script>

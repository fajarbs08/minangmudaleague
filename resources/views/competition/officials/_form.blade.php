@php
    $ageRegistrations = old('age_registrations', $official->exists
        ? $official->ageRegistrations->map(fn ($registration) => [
            'age_group_id' => $registration->age_group_id,
            'season' => $registration->season,
            'role' => $registration->role,
            'license_levels' => $registration->license_levels,
            'notes' => $registration->notes,
        ])->values()->all()
        : [[
            'age_group_id' => old('age_group_id', $official->age_group_id),
            'season' => app(\App\Services\SeasonContext::class)->activeName() ?? date('Y'),
            'role' => old('role', $official->role),
            'license_levels' => old('license_levels', $official->license_levels),
            'notes' => null,
        ]]);
@endphp

@php
    $selectedClubId = old('club_id', $official->club_id ?: $clubs->first()?->id);
    $uploadHelp = 'Foto: JPG, PNG, atau WebP, maks. 512 KB. Dokumen: PDF, JPG, PNG, atau WebP, maks. 512 KB.';
    $requiresPhotoUpload = blank($official->photo_path);
    $requiresIdentityUpload = blank($official->identity_file_path);
@endphp

<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>
<div class="text-muted small mb-3">{{ $uploadHelp }}</div>
<div class="row">
    <div class="col-lg-6 mb-3">
        <label class="form-label">Klub <span class="text-danger">*</span></label>
        @if (auth()->user()->isAdmin())
            <select name="club_id" class="form-select" required>
                <option value="">Pilih klub</option>
                @foreach ($clubs as $club)
                    <option value="{{ $club->id }}" @selected((string) $selectedClubId === (string) $club->id)>{{ $club->name }}</option>
                @endforeach
            </select>
        @else
            <input type="text" class="form-control" value="{{ $clubs->firstWhere('id', $selectedClubId)?->name ?? '-' }}" disabled>
            <input type="hidden" name="club_id" value="{{ $selectedClubId }}">
        @endif
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Peran <span class="text-danger">*</span></label>
        @php
            $roleValue = old('role', $official->role);
        @endphp
        <select name="role" class="form-select" required>
            <option value="">Pilih peran</option>
            <option value="Head Coach" @selected($roleValue === 'Head Coach')>Head Coach</option>
            <option value="Assistant Coach" @selected($roleValue === 'Assistant Coach')>Assistant Coach</option>
            <option value="Manager" @selected($roleValue === 'Manager')>Manager</option>
            <option value="Pelatih Kiper" @selected($roleValue === 'Pelatih Kiper')>Pelatih Kiper</option>
            <option value="Fisioterapis" @selected($roleValue === 'Fisioterapis')>Fisioterapis</option>
            <option value="Dokter" @selected($roleValue === 'Dokter')>Dokter</option>
            <option value="Official" @selected($roleValue === 'Official')>Ofisial</option>
        </select>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $official->name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">No. Lisensi</label>
        <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $official->license_number) }}">
        <small class="text-muted d-block mt-2">Isi jika memilih level lisensi A, B, C, atau D.</small>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Pas Foto 3x4 <span class="text-danger">*</span></label>
        <input type="file" name="photo_file" class="form-control" accept=".jpg,.jpeg,.png,.webp" {{ $requiresPhotoUpload ? 'required' : '' }}>
        @if ($official->photo_file_url)
            <a href="{{ $official->photo_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="image" class="fs-14"></i>
                <span>Lihat foto</span>
            </a>
        @endif
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Bukti Lisensi</label>
        <input type="file" name="license_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
        <small class="text-muted d-block mt-1">Unggah salah satu dengan No. Lisensi jika memilih level A, B, C, atau D. Untuk Non-Lisensi, keduanya boleh kosong.</small>
        @if ($official->license_file_url)
            <a href="{{ $official->license_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat lisensi</span>
            </a>
        @endif
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">KTP / Identitas <span class="text-danger">*</span></label>
        <input type="file" name="identity_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $requiresIdentityUpload ? 'required' : '' }}>
        @if ($official->identity_file_url)
            <a href="{{ $official->identity_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat identitas</span>
            </a>
        @endif
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Telepon</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $official->phone) }}">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $official->email) }}">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
        <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place', $official->birth_place) }}" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
        <input
            type="date"
            name="birth_date"
            class="form-control"
            value="{{ old('birth_date', optional($official->birth_date)->format('Y-m-d')) }}"
            max="{{ now()->format('Y-m-d') }}"
            data-native-picker
            required
        >
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kewarganegaraan <span class="text-danger">*</span></label>
        <select name="citizenship" class="form-select" required>
            <option value="">Pilih status</option>
            <option value="WNI" @selected(old('citizenship', $official->citizenship) === 'WNI')>WNI</option>
            <option value="WNA" @selected(old('citizenship', $official->citizenship) === 'WNA')>WNA</option>
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">NIK / Identitas <span class="text-danger">*</span></label>
        <input type="text" name="identity_number" class="form-control" value="{{ old('identity_number', $official->identity_number) }}" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Level Lisensi</label>
        @php
            $licenseValue = old('license_levels', $official->license_levels);
        @endphp
        <select name="license_levels" class="form-select">
            <option value="">Pilih level</option>
            <option value="A" @selected($licenseValue === 'A')>A</option>
            <option value="B" @selected($licenseValue === 'B')>B</option>
            <option value="C" @selected($licenseValue === 'C')>C</option>
            <option value="D" @selected($licenseValue === 'D')>D</option>
            <option value="Non-Lisensi" @selected($licenseValue === 'Non-Lisensi')>Non-Lisensi</option>
        </select>
        <small class="text-muted d-block mt-2">Pilih Non-Lisensi bila ofisial tidak memiliki lisensi resmi.</small>
    </div>
    <div class="col-12 mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $official->exists ? $official->is_active : true))>
            <label class="form-check-label" for="is_active">Ofisial aktif</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $official->notes) }}</textarea>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('input[data-native-picker]').forEach((input) => {
                    input.addEventListener('click', () => {
                        if (typeof input.showPicker === 'function') {
                            input.showPicker();
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce

<div class="card border mt-4" id="age-registrations">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h5 class="mb-1">Kelompok Usia</h5>
                <div class="text-muted">Satu ofisial bisa terdaftar di lebih dari satu kelompok usia dengan detail jabatan dan lisensi yang berbeda.</div>
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
                        <div class="col-lg-3">
                            <label class="form-label">Kelompok Usia <span class="text-danger">*</span></label>
                            <select name="age_registrations[{{ $index }}][age_group_id]" class="form-select" required>
                                <option value="">Pilih kelompok usia</option>
                                @foreach ($ageGroups as $ageGroup)
                                    <option value="{{ $ageGroup->id }}" @selected((string) ($registration['age_group_id'] ?? '') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Season Aktif</label>
                            <input type="text" name="age_registrations[{{ $index }}][season]" class="form-control" value="{{ $registration['season'] ?? (app(\App\Services\SeasonContext::class)->activeName() ?? date('Y')) }}" readonly>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Jabatan</label>
                            @php
                                $ageRoleValue = $registration['role'] ?? '';
                            @endphp
                            <select name="age_registrations[{{ $index }}][role]" class="form-select">
                                <option value="">Pilih peran</option>
                                <option value="Head Coach" @selected($ageRoleValue === 'Head Coach')>Head Coach</option>
                                <option value="Assistant Coach" @selected($ageRoleValue === 'Assistant Coach')>Assistant Coach</option>
                                <option value="Manager" @selected($ageRoleValue === 'Manager')>Manager</option>
                                <option value="Pelatih Kiper" @selected($ageRoleValue === 'Pelatih Kiper')>Pelatih Kiper</option>
                                <option value="Fisioterapis" @selected($ageRoleValue === 'Fisioterapis')>Fisioterapis</option>
                                <option value="Dokter" @selected($ageRoleValue === 'Dokter')>Dokter</option>
                                <option value="Official" @selected($ageRoleValue === 'Official')>Ofisial</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Lisensi</label>
                            @php
                                $ageLicenseValue = $registration['license_levels'] ?? '';
                            @endphp
                            <select name="age_registrations[{{ $index }}][license_levels]" class="form-select">
                                <option value="">Pilih level</option>
                                <option value="A" @selected($ageLicenseValue === 'A')>A</option>
                                <option value="B" @selected($ageLicenseValue === 'B')>B</option>
                                <option value="C" @selected($ageLicenseValue === 'C')>C</option>
                                <option value="D" @selected($ageLicenseValue === 'D')>D</option>
                                <option value="Non-Lisensi" @selected($ageLicenseValue === 'Non-Lisensi')>Non-Lisensi</option>
                            </select>
                        </div>
                        <div class="col-lg-1">
                            <label class="form-label">Notes</label>
                            <input type="text" name="age_registrations[{{ $index }}][notes]" class="form-control" value="{{ $registration['notes'] ?? '' }}">
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
            <div class="col-lg-3">
                <label class="form-label">Kelompok Usia <span class="text-danger">*</span></label>
                <select data-name="age_group_id" class="form-select" required>
                    <option value="">Pilih kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}">{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Season Aktif</label>
                <input type="text" data-name="season" class="form-control" value="{{ app(\App\Services\SeasonContext::class)->activeName() ?? date('Y') }}" readonly>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Jabatan</label>
                <select data-name="role" class="form-select">
                    <option value="">Pilih peran</option>
                    <option value="Head Coach">Head Coach</option>
                    <option value="Assistant Coach">Assistant Coach</option>
                    <option value="Manager">Manager</option>
                    <option value="Pelatih Kiper">Pelatih Kiper</option>
                    <option value="Fisioterapis">Fisioterapis</option>
                    <option value="Dokter">Dokter</option>
                    <option value="Official">Ofisial</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Lisensi</label>
                <select data-name="license_levels" class="form-select">
                    <option value="">Pilih level</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="Non-Lisensi">Non-Lisensi</option>
                </select>
            </div>
            <div class="col-lg-1">
                <label class="form-label">Notes</label>
                <input type="text" data-name="notes" class="form-control">
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

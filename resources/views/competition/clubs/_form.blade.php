<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>
<div class="row">
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Klub <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $club->name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Singkat</label>
        <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $club->short_name) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Manager</label>
        <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name', $club->manager_name) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Jabatan Penanggung Jawab</label>
        <input type="text" name="manager_title" class="form-control" value="{{ old('manager_title', $club->manager_title) }}" placeholder="Ketua / Penanggung Jawab">
    </div>
    <div class="col-lg-3 mb-3">
        <label class="form-label">Zona</label>
        <input type="text" name="zone" class="form-control" value="{{ old('zone', $club->zone) }}">
    </div>
    <div class="col-lg-3 mb-3">
        <label class="form-label">Kota</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $club->city) }}">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tahun Berdiri</label>
        <input type="number" name="founded_year" class="form-control" value="{{ old('founded_year', $club->founded_year) }}">
    </div>
    <div class="col-lg-8 mb-3">
        <label class="form-label">Logo Klub</label>
        <input type="file" name="logo_file" class="form-control" accept=".jpg,.jpeg,.png">
        @if ($club->logo_file_url)
            <div class="mt-2">
                <img src="{{ $club->logo_file_url }}" alt="Logo klub" class="img-fluid rounded border" style="max-height: 100px;">
            </div>
        @endif
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="3" class="form-control">{{ old('address', $club->address) }}</textarea>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Alamat Bersurat</label>
        <textarea name="mailing_address" rows="3" class="form-control">{{ old('mailing_address', $club->mailing_address) }}</textarea>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Alamat Latihan</label>
        <textarea name="training_address" rows="3" class="form-control">{{ old('training_address', $club->training_address) }}</textarea>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kelompok Umur (Surat)</label>
        <input type="text" name="statement_age_group" class="form-control" value="{{ old('statement_age_group', $club->statement_age_group) }}" placeholder="U-12 / U-14 / U-16">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kontak (Surat)</label>
        <input type="text" name="statement_contact" class="form-control" value="{{ old('statement_contact', $club->statement_contact) }}" placeholder="Telepon / Email">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Penandatangan Mengetahui</label>
        <input type="text" name="statement_witness_name" class="form-control" value="{{ old('statement_witness_name', $club->statement_witness_name) }}" placeholder="Manager Team / Admin Club">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Jabatan Penandatangan Mengetahui</label>
        <input type="text" name="statement_witness_title" class="form-control" value="{{ old('statement_witness_title', $club->statement_witness_title) }}" placeholder="Manager Team / Admin Club">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Bukti Akta SSB</label>
        <input type="file" name="deed_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        @if ($club->deed_file_url)
            <a href="{{ $club->deed_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat bukti akta</span>
            </a>
        @endif
    </div>
    <div class="col-lg-6 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label mb-0">Surat Pernyataan</label>
            @if ($club->exists)
                <a
                    href="{{ route('clubs.statement-template', ['club_id' => $club->id]) }}"
                    target="_blank"
                    class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
                >
                    <i data-lucide="download" class="fs-14"></i>
                    <span>Download Template</span>
                </a>
            @else
                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2" disabled>
                    <i data-lucide="download" class="fs-14"></i>
                    <span>Download Template</span>
                </button>
            @endif
        </div>
        <input type="file" name="statement_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        <small class="text-muted d-block mt-2">
            @if ($club->exists)
                Template ini otomatis mengisi nama klub dan penanggung jawab. Cetak, tandatangani, stempel bila ada, lalu unggah kembali.
            @else
                Simpan profil klub dulu agar template otomatis terisi nama klub dan penanggung jawab.
            @endif
        </small>
        @if ($club->statement_file_url)
            <a href="{{ $club->statement_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-flex align-items-center gap-2">
                <i data-lucide="file-text" class="fs-14"></i>
                <span>Lihat surat pernyataan</span>
            </a>
        @endif
    </div>
    <div class="col-12 mb-0">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $club->notes) }}</textarea>
    </div>
</div>

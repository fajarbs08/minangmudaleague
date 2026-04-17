@php
    $imageUploadHelp = 'Format: JPG, JPEG, PNG, atau WebP. Ukuran minimum 120 x 120 px, maks. 3 MB. Logo akan diperbesar dan dipusatkan otomatis ke kanvas standar.';
    $statementUploadHelp = 'Format: PDF, JPG, JPEG, PNG, WebP, DOC, DOCX. Maks. 4 MB. Jika file berupa gambar, sistem akan menormalkan ukuran tanpa crop agar dokumen tetap jelas.';
@endphp

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
    <div class="col-lg-6 mb-3">
        <label class="form-label">Zona (Kota / Kabupaten)</label>
        <input type="text" name="zone" class="form-control" value="{{ old('zone', $club->zone) }}" placeholder="Kota / Kabupaten">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tahun Berdiri</label>
        <input type="number" name="founded_year" class="form-control" value="{{ old('founded_year', $club->founded_year) }}">
    </div>
    <div class="col-lg-8 mb-3">
        <label class="form-label">Logo Klub</label>
        <input type="file" name="logo_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        <small class="text-muted d-block mt-2">{{ $imageUploadHelp }}</small>
        <small class="text-muted d-block mt-1">Gunakan logo dengan background sesederhana mungkin. Tidak perlu menambahkan ruang kosong lebar di sekeliling logo.</small>
        @if ($club->logo_file_url)
            <div class="mt-2">
                <div class="d-inline-flex align-items-center justify-content-center rounded border bg-white p-3" style="width: 120px; height: 120px;">
                    <img src="{{ $club->logo_file_url }}" alt="Logo klub" class="img-fluid" style="max-width: 96px; max-height: 96px; width: auto; height: auto; object-fit: contain;">
                </div>
            </div>
        @endif
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="3" class="form-control">{{ old('address', $club->address) }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Alamat Latihan</label>
        <textarea name="training_address" rows="3" class="form-control">{{ old('training_address', $club->training_address) }}</textarea>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label mb-0">Surat Pernyataan</label>
            <a
                href="{{ route('clubs.statement-template') }}"
                target="_blank"
                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
            >
                <i data-lucide="download" class="fs-14"></i>
                <span>Download Template</span>
            </a>
        </div>
        <input type="file" name="statement_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
        <small class="text-muted d-block mt-2">{{ $statementUploadHelp }}</small>
        <small class="text-muted d-block mt-2">
            Download template, isi data klub, tanda tangan, stempel bila ada, lalu unggah kembali.
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

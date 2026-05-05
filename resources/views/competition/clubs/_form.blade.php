@php
    $uploadHelp = 'Logo: JPG, PNG, atau WebP, min. 120 x 120 px, maks. 512 KB. Surat: PDF, JPG, PNG, WebP, DOC, atau DOCX, maks. 1 MB.';
    $requiresLogoUpload = blank($club->logo_url);
    $requiresStatementUpload = blank($club->statement_file_path);
@endphp

<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>
<div class="text-muted small mb-3">{{ $uploadHelp }}</div>
<div class="row">
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Klub <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $club->name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Singkat <span class="text-danger">*</span></label>
        <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $club->short_name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Manager <span class="text-danger">*</span></label>
        <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name', $club->manager_name) }}" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Jabatan Penanggung Jawab <span class="text-danger">*</span></label>
        <input type="text" name="manager_title" class="form-control" value="{{ old('manager_title', $club->manager_title) }}" placeholder="Ketua / Penanggung Jawab" required>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Zona (Kota / Kabupaten) <span class="text-danger">*</span></label>
        <input type="text" name="zone" class="form-control" value="{{ old('zone', $club->zone) }}" placeholder="Kota / Kabupaten" required>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tahun Berdiri <span class="text-danger">*</span></label>
        <input type="number" name="founded_year" class="form-control" value="{{ old('founded_year', $club->founded_year) }}" required>
    </div>
    <div class="col-lg-8 mb-3">
        <label class="form-label">Logo Klub <span class="text-danger">*</span></label>
        <input type="file" name="logo_file" class="form-control" accept=".jpg,.jpeg,.png,.webp" {{ $requiresLogoUpload ? 'required' : '' }}>
        @if ($club->logo_file_url)
            <div class="mt-2">
                <div class="d-inline-flex align-items-center justify-content-center rounded border bg-white p-3" style="width: 120px; height: 120px;">
                    <img src="{{ $club->logo_file_url }}" alt="Logo klub" class="img-fluid" style="max-width: 96px; max-height: 96px; width: auto; height: auto; object-fit: contain;">
                </div>
            </div>
        @endif
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Alamat <span class="text-danger">*</span></label>
        <textarea name="address" rows="3" class="form-control" required>{{ old('address', $club->address) }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Alamat Latihan <span class="text-danger">*</span></label>
        <textarea name="training_address" rows="3" class="form-control" required>{{ old('training_address', $club->training_address) }}</textarea>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label mb-0">Surat Pernyataan <span class="text-danger">*</span></label>
            <a
                href="{{ route('clubs.statement-template') }}"
                target="_blank"
                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
            >
                <i data-lucide="download" class="fs-14"></i>
                <span>Download Template</span>
            </a>
        </div>
        <input type="file" name="statement_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" {{ $requiresStatementUpload ? 'required' : '' }}>
        <small class="text-muted d-block mt-2">
            Isi template, tanda tangani, lalu unggah kembali.
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

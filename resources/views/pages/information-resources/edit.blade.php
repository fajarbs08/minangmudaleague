@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Edit Dokumen Pusat Informasi</h4>
        <p class="text-muted mb-0">Ubah metadata, status tampil, urutan, atau ganti file dokumen.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('information-resources.index') }}" class="btn btn-light">Kembali</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('information-resources.update', $resource) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Judul <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $resource->title) }}" required>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        @foreach (['template' => 'Template', 'flow' => 'Flow', 'rules' => 'Rules', 'manual' => 'Manual', 'other' => 'Lainnya'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $resource->category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Visibilitas <span class="text-danger">*</span></label>
                    <select name="visibility" class="form-select" required>
                        @foreach ($visibilityOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('visibility', $resource->visibility) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Urutan Tampil</label>
                    <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $resource->sort_order) }}">
                </div>
                <div class="col-lg-3">
                    <label class="form-label d-block">Prioritas</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="edit-is-pinned" name="is_pinned" value="1" @checked(old('is_pinned', $resource->is_pinned))>
                        <label class="form-check-label" for="edit-is-pinned">Pin ke paling atas</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $resource->description) }}</textarea>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Ganti File</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                    <small class="text-muted d-block mt-2">Kosongkan jika file lama tetap dipakai. Format: PDF, JPG, JPEG, PNG, WebP, DOC, DOCX. Maks. 4 MB. Jika file berupa gambar, sistem akan menormalkan ukuran tanpa crop agar tetap jelas.</small>
                </div>
                <div class="col-lg-6">
                    <label class="form-label d-block">File Saat Ini</label>
                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                        <div class="fw-semibold">{{ $resource->file_name }}</div>
                        <div class="text-muted small mb-3">{{ $resource->file_mime ?: 'Tipe file tidak terdeteksi' }}</div>
                        @if ($resource->isImage)
                            <img src="{{ $resource->file_url }}" alt="{{ $resource->title }}" class="img-fluid rounded border mb-3" style="max-height: 180px;">
                        @elseif ($resource->isPdf)
                            <iframe
                                src="{{ $resource->file_url }}#toolbar=0"
                                title="{{ $resource->title }}"
                                style="width: 100%; height: 320px; border: 1px solid #dee2e6; border-radius: 8px; background: #f8f9fa;"
                                class="mb-3"
                            ></iframe>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ $resource->file_url }}" target="_blank" class="btn btn-sm btn-light">Buka</a>
                            <a href="{{ $resource->download_url }}" class="btn btn-sm btn-light">Download</a>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-published" name="is_published" value="1" @checked(old('is_published', $resource->is_published))>
                        <label class="form-check-label" for="edit-is-published">Tampilkan sesuai visibilitas</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('information-resources.index') }}" class="btn btn-light">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

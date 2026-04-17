@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Edit Sponsor</h4>
        <p class="text-muted mb-0">Perbarui metadata sponsor dan logo yang tampil di halaman publik.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sponsors.index') }}" class="btn btn-light">Kembali</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('sponsors.update', $sponsor) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $sponsor->name) }}" required>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Singkatan</label>
                    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $sponsor->short_name) }}">
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $sponsor->sort_order) }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Website</label>
                    <input type="url" name="website_url" class="form-control" value="{{ old('website_url', $sponsor->website_url) }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Tier <span class="text-danger">*</span></label>
                    <input type="text" name="tier" class="form-control" value="{{ old('tier', $sponsor->tier) }}" required>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Ganti Logo</label>
                    <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
                <div class="col-lg-6">
                    <label class="form-label d-block">Logo Saat Ini</label>
                    <div class="border rounded-3 p-3 bg-light-subtle h-100 d-flex align-items-center justify-content-center">
                        @if ($sponsor->logo_url)
                            <img src="{{ $sponsor->logo_url }}" alt="{{ $sponsor->name }}" style="max-width: 180px; max-height: 120px; width: auto; height: auto;">
                        @else
                            <span class="text-muted">Belum ada logo</span>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit-sponsor-published" name="is_published" value="1" @checked(old('is_published', $sponsor->is_published))>
                        <label class="form-check-label" for="edit-sponsor-published">Tampilkan di halaman publik</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('sponsors.index') }}" class="btn btn-light">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

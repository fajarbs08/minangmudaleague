@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Tambah Ofisial</h4>
        <p class="text-muted mb-0">Masukkan data ofisial klub.</p>
    </div>
    <a href="{{ route('officials.index') }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="alert alert-info">
    Lengkapi data wajib (Klub, Peran, Nama, dan Kelompok Usia). File upload akan tersimpan setelah form berhasil disimpan.
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('officials.store') }}" enctype="multipart/form-data" novalidate>
            @csrf
            @include('competition.officials._form')
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('officials.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

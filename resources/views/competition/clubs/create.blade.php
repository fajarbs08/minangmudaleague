@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Tambah Klub</h4>
        <p class="text-muted mb-0">Masukkan profil dasar klub.</p>
    </div>
    <a href="{{ route('clubs.index') }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('clubs.store') }}" enctype="multipart/form-data">
            @csrf
            @include('competition.clubs._form')
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('clubs.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

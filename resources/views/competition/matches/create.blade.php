@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Tambah Jadwal Pertandingan</h4>
        <p class="text-muted mb-0">Admin menentukan lawan, venue, tanggal, jam, dan matchday.</p>
    </div>
    <a href="{{ route('matches.index') }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('matches.store') }}">
            @csrf
            @include('competition.matches._form')
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('matches.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Tambah Jadwal Pertandingan</h4>
        <p class="text-muted mb-0">Atur lawan, lokasi, tanggal, jam, dan hari pertandingan dari halaman ini.</p>
    </div>
    <a href="{{ $backUrl ?? route('matches.league.index') }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('matches.store') }}">
            @csrf
            <input type="hidden" name="redirect_route" value="{{ $redirectRouteName ?? 'matches.league.index' }}">
            @include('competition.matches._form')
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ $backUrl ?? route('matches.league.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

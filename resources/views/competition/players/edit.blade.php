@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Edit Pemain</h4>
        <p class="text-muted mb-0">{{ $player->name }}</p>
    </div>
    <a href="{{ route('players.show', $player) }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('players.update', $player) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('competition.players._form')
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('players.show', $player) }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.workflow-panel', [
    'item' => $player,
    'submitRoute' => route('players.submit', $player),
    'reviewRoute' => route('players.review', $player),
])
@endsection

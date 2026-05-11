@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 lineup-form-page-header">
    <div>
        <h4 class="mb-1">Edit DSP</h4>
        <p class="text-muted mb-0">{{ $lineupList->title }}</p>
    </div>
    <a href="{{ route('lineup-lists.show', $lineupList) }}" class="btn btn-light flex-shrink-0">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card lineup-form-card">
    <div class="card-body">
        <form method="POST" action="{{ route('lineup-lists.update', $lineupList) }}" enctype="multipart/form-data" data-autosave="off">
            @csrf
            @method('PUT')
            @include('competition.lineups._form')
            <div class="mt-4 d-flex flex-column flex-sm-row justify-content-end gap-2 lineup-form-page-actions">
                <a href="{{ route('lineup-lists.show', $lineupList) }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.workflow-panel', [
    'item' => $lineupList,
    'submitRoute' => route('lineup-lists.submit', $lineupList),
    'reviewRoute' => route('lineup-lists.review', $lineupList),
])
@endsection

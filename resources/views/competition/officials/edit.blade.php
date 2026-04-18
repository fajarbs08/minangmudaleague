@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Edit Ofisial</h4>
        <p class="text-muted mb-0">{{ $official->name }}</p>
    </div>
    <a href="{{ route('officials.index') }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('officials.update', $official) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('competition.officials._form')
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('officials.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@include('competition.partials.workflow-panel', [
    'item' => $official,
    'submitRoute' => route('officials.submit', $official),
    'reviewRoute' => route('officials.review', $official),
])
@endsection

@extends('layouts.vertical', ['title' => $document['title']])

@section('content')
@include('competition.id-cards.partials.styles', ['document' => $document, 'renderMode' => 'preview'])

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="mb-1">{{ $document['title'] }}</h4>
        <p class="text-muted mb-0">{{ $document['club']['name'] }} · {{ $document['ageGroup']['name'] }} · {{ $document['count'] }} kartu</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ $backUrl }}" class="btn btn-light">Kembali</a>
        <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-outline-primary">Buka PDF</a>
        <a href="{{ $downloadUrl }}" target="_blank" class="btn btn-primary">Unduh PDF</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Jenis</p>
                <h5 class="mb-0 text-capitalize">{{ $document['subjectType'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Klub</p>
                <h5 class="mb-0">{{ $document['club']['name'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Kelompok Usia</p>
                <h5 class="mb-0">{{ $document['ageGroup']['name'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Total Kartu</p>
                <h5 class="mb-0">{{ $document['count'] }}</h5>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">Pratinjau Kartu</h4>
    </div>
    <div class="card-body">
        @if (empty($document['cards']))
            <div class="text-muted">Belum ada kartu untuk dirender.</div>
        @else
            @include('competition.id-cards.partials.deck', ['document' => $document])
        @endif
    </div>
</div>
@endsection

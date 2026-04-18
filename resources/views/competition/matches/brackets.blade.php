@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['age_group_id']))->filter(fn ($value) => filled($value))->count();
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Laporan</li>
                <li class="breadcrumb-item active" aria-current="page">Bagan Knockout</li>
            </ol>
        </nav>
        <h4 class="mb-1">Bagan Knockout</h4>
        <p class="text-muted mb-0">Bracket dipisah ke page sendiri supaya jalur babak gugur lebih mudah ditinjau.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#bracketFilterCanvas"
            aria-controls="bracketFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
    </div>
</div>

@include('competition.matches.partials.report-nav')
@include('competition.partials.flash')
@include('competition.matches.partials.knockout-brackets', [
    'sectionTitle' => 'Bagan Knockout',
    'sectionDescription' => 'Menampilkan alur babak gugur berdasarkan babak dan slot bracket yang tersimpan.',
    'emptyMessage' => 'Belum ada bracket knockout yang bisa ditampilkan untuk filter ini.',
])

<div class="offcanvas offcanvas-end" tabindex="-1" id="bracketFilterCanvas" aria-labelledby="bracketFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="bracketFilterCanvasLabel">Filter Bagan Knockout</h5>
            <p class="text-muted mb-0 small">Filter kelompok usia agar bracket yang ditampilkan lebih fokus.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="bracket-age-group-id" class="form-label">Kelompok usia</label>
                <select id="bracket-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ route('reports.brackets') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>
@endsection

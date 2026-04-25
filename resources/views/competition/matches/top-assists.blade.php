@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['age_group_id']))->filter(fn ($value) => filled($value))->count();
    $pdfQuery = array_filter([
        'age_group_id' => request('age_group_id'),
    ], fn ($value) => filled($value));
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Laporan</li>
                <li class="breadcrumb-item active" aria-current="page">Top Assist</li>
            </ol>
        </nav>
        <h4 class="mb-1">Top Assist</h4>
        <p class="text-muted mb-0">Laporan assist dipisah agar distribusi kontribusi pemain bisa dibaca tanpa campur dengan data gol.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('reports.top-assists.pdf', $pdfQuery) }}" target="_blank" class="btn btn-primary">Buka PDF</a>
        <a href="{{ route('reports.top-assists.pdf', array_merge($pdfQuery, ['download' => 1])) }}" class="btn btn-light">Unduh PDF</a>
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#topAssistFilterCanvas"
            aria-controls="topAssistFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
    </div>
</div>

@include('competition.partials.flash')
@include('competition.matches.partials.leaderboard-section', [
    'title' => 'Top Assist',
    'description' => 'Peringkat pemberi assist terbanyak dari report gol yang sudah tercatat.',
    'leaderboards' => $topAssists,
    'metricLabel' => 'Assist',
    'emptyMessage' => 'Belum ada data top assist untuk filter ini.',
])

<div class="offcanvas offcanvas-end" tabindex="-1" id="topAssistFilterCanvas" aria-labelledby="topAssistFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="topAssistFilterCanvasLabel">Filter Top Assist</h5>
            <p class="text-muted mb-0 small">Filter per kelompok usia sebelum membuka atau mengunduh PDF.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="top-assist-age-group-id" class="form-label">Kelompok usia</label>
                <select id="top-assist-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ route('reports.top-assists') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>
@endsection

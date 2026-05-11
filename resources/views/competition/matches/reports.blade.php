@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['age_group_id']))->filter(fn ($value) => filled($value))->count();
    $pdfQuery = array_filter([
        'age_group_id' => request('age_group_id'),
    ], fn ($value) => filled($value));
@endphp

@push('css')
<style>
    .lap-report-deferred {
        content-visibility: auto;
        contain-intrinsic-size: 1px 1080px;
    }

    .lap-report-deferred.is-compact {
        contain-intrinsic-size: 1px 420px;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Laporan</li>
                <li class="breadcrumb-item active" aria-current="page">Rekap PDF</li>
            </ol>
        </nav>
        <h4 class="mb-1">Rekap Laporan Pertandingan</h4>
        <p class="text-muted mb-0">Rekap gabungan untuk klasemen, top skor, top assist, dan export PDF resmi.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#reportFilterCanvas"
            aria-controls="reportFilterCanvas"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </button>
        <a href="{{ route('reports.overview.pdf', $pdfQuery) }}" target="_blank" class="btn btn-primary">Buka PDF</a>
        <a href="{{ route('reports.overview.pdf', array_merge($pdfQuery, ['download' => 1])) }}" class="btn btn-light">Unduh PDF</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4 lap-report-deferred is-compact">
    @foreach ($reportSummary as $item)
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $item['label'] }}</div>
                    <div class="display-6 fw-bold mb-2">{{ $item['value'] }}</div>
                    <p class="text-muted mb-0 small">{{ $item['hint'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-grid gap-4 lap-report-deferred">
    @include('competition.matches.partials.leaderboard-section', [
        'title' => 'Top Skor',
        'description' => 'Peringkat pencetak gol terbanyak dari pertandingan yang sudah selesai.',
        'leaderboards' => $topScorers,
        'metricLabel' => 'Gol',
        'emptyMessage' => 'Belum ada data top skor untuk filter ini.',
    ])

    @include('competition.matches.partials.leaderboard-section', [
        'title' => 'Top Assist',
        'description' => 'Peringkat pemberi assist terbanyak dari report gol yang sudah tercatat.',
        'leaderboards' => $topAssists,
        'metricLabel' => 'Assist',
        'emptyMessage' => 'Belum ada data top assist untuk filter ini.',
    ])

    @include('competition.matches.partials.standings-section', [
        'sectionTitle' => 'Klasemen Liga',
        'sectionDescription' => 'Klasemen di bawah ini hanya dihitung dari pertandingan format liga yang selesai.',
        'emptyMessage' => 'Belum ada klasemen liga untuk filter ini.',
    ])
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="reportFilterCanvas" aria-labelledby="reportFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="reportFilterCanvasLabel">Filter Laporan</h5>
            <p class="text-muted mb-0 small">Filter laporan per kelompok usia sebelum dibuka atau diekspor ke PDF.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="report-age-group-id" class="form-label">Kelompok usia</label>
                <select id="report-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ route('reports.overview') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>
@endsection

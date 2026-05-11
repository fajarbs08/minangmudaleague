@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['age_group_id']))->filter(fn ($value) => filled($value))->count();
@endphp

@push('css')
<style>
    .lap-report-deferred {
        content-visibility: auto;
        contain-intrinsic-size: 1px 1080px;
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
                <li class="breadcrumb-item active" aria-current="page">Klasemen</li>
            </ol>
        </nav>
        <h4 class="mb-1">Klasemen Pertandingan</h4>
        <p class="text-muted mb-0">Tabel klasemen dipisah dari input hasil supaya pemantauan posisi klub lebih nyaman.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('reports.standings.pdf', array_filter(['age_group_id' => request('age_group_id')], fn ($value) => filled($value))) }}" target="_blank" class="btn btn-primary">Buka PDF</a>
        <a href="{{ route('reports.standings.pdf', array_merge(array_filter(['age_group_id' => request('age_group_id')], fn ($value) => filled($value)), ['download' => 1])) }}" class="btn btn-light">Unduh PDF</a>
        <button
            type="button"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="offcanvas"
            data-bs-target="#standingsFilterCanvas"
            aria-controls="standingsFilterCanvas"
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
<div class="lap-report-deferred">
@include('competition.matches.partials.standings-section', [
    'sectionTitle' => 'Klasemen',
    'sectionDescription' => 'Disusun otomatis dari pertandingan format liga yang sudah selesai.',
    'emptyMessage' => 'Belum ada klasemen yang bisa ditampilkan untuk filter ini.',
])
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="standingsFilterCanvas" aria-labelledby="standingsFilterCanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="standingsFilterCanvasLabel">Filter Klasemen</h5>
            <p class="text-muted mb-0 small">Gunakan filter kelompok usia untuk fokus ke tabel yang diperlukan.</p>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="d-flex flex-column gap-3">
            <div>
                <label for="standings-age-group-id" class="form-label">Kelompok usia</label>
                <select id="standings-age-group-id" name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ route('reports.standings') }}" class="btn btn-light">Reset Filter</a>
            </div>
        </form>
    </div>
</div>
@endsection

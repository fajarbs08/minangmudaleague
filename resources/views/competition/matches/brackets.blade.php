@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['age_group_id']))->filter(fn ($value) => filled($value))->count();
    $reportBrackets = $bracketryBrackets ?? collect();
    $ageSummaries = collect($ageGroupSummaries ?? []);
    $selectedAgeGroupId = $selectedAgeGroupId ?? null;
    $printQuery = array_filter([
        'age_group_id' => $selectedAgeGroupId,
    ], fn ($value) => filled($value));
@endphp

@section('content')
<style>
    .report-bracket-group + .report-bracket-group {
        margin-top: 1.5rem;
    }

    .report-bracket-group-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(148, 163, 184, .2);
        margin-bottom: 1.25rem;
    }

    .report-bracket-group-head h5 {
        margin: 0;
    }

    .report-bracket-group-head p {
        margin: .35rem 0 0;
    }

    .report-bracket-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .35rem .7rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, .1);
        color: #1d4ed8;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .report-bracket-age-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .report-bracket-age-pill {
        min-width: 132px;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .8rem 1rem;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, .24);
        background: #fff;
        color: #0f172a;
        text-decoration: none;
        transition: border-color .2s ease, background-color .2s ease, transform .2s ease;
    }

    .report-bracket-age-pill:hover,
    .report-bracket-age-pill:focus-visible {
        border-color: rgba(59, 130, 246, .45);
        background: rgba(59, 130, 246, .04);
        transform: translateY(-1px);
        outline: none;
    }

    .report-bracket-age-pill.is-active {
        border-color: rgba(37, 99, 235, .24);
        background: linear-gradient(135deg, rgba(37, 99, 235, .08), rgba(14, 165, 233, .12));
    }

    .report-bracket-age-pill-name {
        font-weight: 600;
        white-space: nowrap;
    }

    .report-bracket-age-pill-count {
        min-width: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: .15rem .55rem;
        background: rgba(37, 99, 235, .1);
        color: #1d4ed8;
        font-size: .75rem;
        font-weight: 700;
    }

    .report-bracket-shell {
        position: relative;
        min-height: var(--report-bracket-height, 720px);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }

    .lap-bracket-host {
        min-height: var(--report-bracket-height, 720px);
        min-width: 0;
    }

    .bracket-mobile-controls {
        display: none;
    }

    .lap-bracket-host[data-bracket-readonly="true"] .bt-match {
        cursor: default;
    }

    .lap-bracket-host .bracket-root {
        border: none;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
    }

    .lap-bracket-host .round-title {
        font-size: .82rem;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: none;
        color: #0f172a;
    }

    .lap-bracket-host .bt-match {
        position: relative;
        display: grid;
        gap: .4rem;
        width: 100%;
        padding: .75rem .8rem .8rem;
        border: 1px solid #e2e8f0;
        border-radius: .85rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        text-align: left;
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
    }

    .lap-bracket-host .bt-match.is-final {
        grid-template-columns: 1fr;
        justify-items: center;
        gap: .65rem;
    }

    .lap-bracket-host .bt-match.is-final .bt-match-main,
    .lap-bracket-host .bt-match-main {
        width: 100%;
        display: grid;
        gap: .4rem;
    }

    .lap-bracket-host .bt-match-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .75rem;
    }

    .lap-bracket-host .bt-match-ribbon {
        display: inline-flex;
        align-items: center;
        padding: .15rem .5rem;
        border-radius: 999px;
        background: #f2f6ff;
        color: #0d2f67;
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .08em;
    }

    .lap-bracket-host .bt-match-status {
        color: #64748b;
        font-size: .68rem;
        font-weight: 700;
        text-align: right;
        max-width: 12ch;
    }

    .lap-bracket-host .bt-side {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .65rem;
        align-items: center;
        padding-left: 0;
    }

    .lap-bracket-host .bt-side + .bt-side {
        padding-top: .45rem;
        border-top: 1px solid #edf1f6;
    }

    .lap-bracket-host .bt-side-name {
        color: #0f172a;
        font-size: .92rem;
        font-weight: 800;
        letter-spacing: -.01em;
        line-height: 1.2;
    }

    .lap-bracket-host .bt-side-score {
        min-width: 1.9rem;
        padding: .16rem .4rem;
        border-radius: .45rem;
        background: #f3f4f7;
        color: #0d2f67;
        text-align: center;
        font-size: .82rem;
        font-weight: 900;
    }

    .lap-bracket-host .bt-vs {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: .05rem 0;
        color: #0f172a;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .12em;
    }

    .lap-bracket-host .bracket-root .round-wrapper:first-of-type .match-lines-area {
        left: 50% !important;
    }

    .lap-bracket-host .match-wrapper.is-empty-slot .match-lines-area {
        visibility: hidden;
    }

    .lap-bracket-host .match-wrapper.no-incoming-connector .match-lines-area {
        left: 50%;
    }

    .lap-bracket-host .bracket-root .round-wrapper:last-of-type .match-lines-area {
        right: 50% !important;
    }

    .lap-bracket-host .match-lines-area .line-wrapper {
        position: relative;
        color: #b7c9e8;
        border-color: #b7c9e8;
        filter: drop-shadow(0 3px 8px rgba(13, 47, 103, .08));
    }

    .lap-bracket-host .match-wrapper.odd .line-wrapper.upper {
        border-bottom-right-radius: 18px;
    }

    .lap-bracket-host .match-wrapper.even .line-wrapper.lower {
        border-top-right-radius: 18px;
    }

    .lap-bracket-host .match-wrapper.odd .line-wrapper.upper::after,
    .lap-bracket-host .match-wrapper.even .line-wrapper.lower::after {
        content: '';
        position: absolute;
        right: -1px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, .96);
        transform: translate(50%, -50%);
    }

    .lap-bracket-host .match-wrapper.highlighted .line-wrapper {
        color: #0d2f67;
        border-color: #0d2f67;
    }

    .lap-bracket-host .match-body.live:not(:empty) {
        background: #fff8f8;
    }

    html[data-bs-theme="dark"] .report-bracket-age-pill {
        border-color: var(--lap-admin-border-soft);
        background: var(--lap-admin-surface-card);
        color: var(--lap-admin-text-strong);
    }

    html[data-bs-theme="dark"] .report-bracket-age-pill:hover,
    html[data-bs-theme="dark"] .report-bracket-age-pill:focus-visible,
    html[data-bs-theme="dark"] .report-bracket-age-pill.is-active {
        border-color: rgba(var(--bs-primary-rgb), 0.34);
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.14), rgba(14, 165, 233, 0.12));
    }

    html[data-bs-theme="dark"] .report-bracket-age-pill-count,
    html[data-bs-theme="dark"] .report-bracket-badge,
    html[data-bs-theme="dark"] .lap-bracket-host .bt-match-ribbon {
        background: rgba(var(--bs-primary-rgb), 0.16);
        color: #bfdbfe;
    }

    html[data-bs-theme="dark"] .report-bracket-shell {
        border-color: var(--lap-admin-border-soft);
        background: var(--lap-admin-surface-card);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .round-title,
    html[data-bs-theme="dark"] .lap-bracket-host .bt-side-name,
    html[data-bs-theme="dark"] .lap-bracket-host .bt-vs {
        color: var(--lap-admin-text-strong);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .bt-match {
        border-color: var(--lap-admin-border-soft);
        background: var(--lap-admin-surface-card);
        box-shadow: var(--lap-admin-shadow-card);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .bt-match-status {
        color: var(--lap-admin-text-muted);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .bt-side + .bt-side {
        border-top-color: var(--lap-admin-border-soft);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .bt-side-score {
        background: var(--lap-admin-surface-soft);
        color: #dbeafe;
    }

    html[data-bs-theme="dark"] .lap-bracket-host .match-lines-area .line-wrapper,
    html[data-bs-theme="dark"] .lap-bracket-host .match-wrapper.highlighted .line-wrapper {
        color: rgba(125, 162, 220, 0.78);
        border-color: rgba(125, 162, 220, 0.78);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .match-wrapper.odd .line-wrapper.upper::after,
    html[data-bs-theme="dark"] .lap-bracket-host .match-wrapper.even .line-wrapper.lower::after {
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.92);
    }

    html[data-bs-theme="dark"] .lap-bracket-host .match-body.live:not(:empty) {
        background: var(--lap-admin-live-surface);
    }

    @media (max-width: 767px) {
        .report-bracket-group .card-body {
            padding: 1rem;
        }

        .report-bracket-group-head {
            align-items: flex-start;
        }

        .report-bracket-badge {
            align-self: flex-start;
        }

        .report-bracket-shell,
        .lap-bracket-host {
            min-height: var(--report-bracket-height-mobile, 520px);
        }

        .report-bracket-shell {
            border-radius: .8rem;
        }

        .lap-bracket-host[data-bracket-mobile-controls="true"] .navigation-button,
        .lap-bracket-host[data-bracket-mobile-controls="true"] .scroll-button {
            display: none !important;
        }

        .lap-bracket-host[data-bracket-mobile-controls="true"] .match-lines-area,
        .lap-bracket-host[data-bracket-mobile-controls="true"] .line-wrapper {
            display: none !important;
        }

        .bracket-mobile-controls {
            display: grid;
            grid-template-columns: 3.4rem minmax(0, 1fr) 3.4rem;
            align-items: center;
            gap: .75rem;
            margin-top: .85rem;
        }

        .bracket-mobile-controls__button {
            min-width: 3.4rem;
            width: 3.4rem;
            height: 3.4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d5dbe7;
            border-radius: 999px;
            background: #fff;
            color: #0d2f67;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
            font-size: 1.55rem;
            font-weight: 900;
            line-height: 1;
        }

        .bracket-mobile-controls__button:disabled {
            opacity: .38;
            box-shadow: none;
        }

        .bracket-mobile-controls__label {
            min-width: 0;
            text-align: center;
        }

        .bracket-mobile-controls__eyebrow {
            color: #64748b;
            font-size: .67rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bracket-mobile-controls__title {
            margin-top: .2rem;
            color: #0f172a;
            font-size: .95rem;
            font-weight: 800;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lap-bracket-host .bt-match {
            gap: .3rem;
            padding: .65rem .72rem .72rem;
            border-radius: .7rem;
        }

        .lap-bracket-host .bt-match.is-final {
            gap: .45rem;
        }

        .lap-bracket-host .bt-match-main {
            gap: .3rem;
        }

        .lap-bracket-host .bt-match-head {
            gap: .5rem;
        }

        .lap-bracket-host .bt-match-ribbon {
            padding: .12rem .42rem;
            font-size: .6rem;
        }

        .lap-bracket-host .bt-match-status {
            max-width: 10ch;
            font-size: .62rem;
        }

        .lap-bracket-host .bt-side {
            gap: .55rem;
        }

        .lap-bracket-host .bt-side + .bt-side {
            padding-top: .34rem;
        }

        .lap-bracket-host .bt-side-name {
            font-size: .86rem;
            line-height: 1.16;
            word-break: break-word;
        }

        .lap-bracket-host .bt-side-score {
            min-width: 1.85rem;
            padding: .12rem .35rem;
            font-size: .78rem;
        }

        .lap-bracket-host .bt-vs {
            margin: 0;
            font-size: .64rem;
        }
    }

</style>

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
        <a href="{{ route('reports.brackets.print', $printQuery) }}" target="_blank" rel="noopener" class="btn btn-primary">Cetak Bracket</a>
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

@include('competition.partials.flash')

@if ($ageSummaries->isNotEmpty())
    <div class="card mb-4">
        <div class="card-body">
            <div class="small text-uppercase fw-semibold text-muted mb-3">Kelompok Usia</div>
            <div class="report-bracket-age-tabs" aria-label="Pilih kelompok usia bracket report">
                <a href="{{ route('reports.brackets') }}" class="report-bracket-age-pill {{ ! filled($selectedAgeGroupId) ? 'is-active' : '' }}">
                    <span class="report-bracket-age-pill-name">Semua</span>
                    <span class="report-bracket-age-pill-count">{{ $ageSummaries->sum('total_matches') }}</span>
                </a>
                @foreach ($ageSummaries as $ageGroup)
                    <a
                        href="{{ route('reports.brackets', ['age_group_id' => $ageGroup['id']]) }}"
                        class="report-bracket-age-pill {{ (string) $selectedAgeGroupId === (string) $ageGroup['id'] ? 'is-active' : '' }}"
                    >
                        <span class="report-bracket-age-pill-name">{{ $ageGroup['name'] }}</span>
                        <span class="report-bracket-age-pill-count">{{ $ageGroup['total_matches'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

@forelse ($reportBrackets as $bracket)
    <div class="card report-bracket-group">
        <div class="card-body">
            <div class="report-bracket-group-head">
                <div>
                    <h5>{{ $bracket['age_group']?->name ?: '-' }}</h5>
                    <p class="text-muted mb-0">Menampilkan alur babak gugur berdasarkan babak dan slot bracket yang tersimpan.</p>
                </div>
                <span class="report-bracket-badge">{{ $bracket['match_count'] }} match</span>
            </div>

            <div
                class="report-bracket-shell"
                style="--report-bracket-height: {{ $bracket['layout']['desktop_height'] ?? 720 }}px; --report-bracket-height-mobile: {{ $bracket['layout']['mobile_height'] ?? 520 }}px;"
            >
                <div class="lap-bracket-host" data-bracketry-host data-bracket-readonly="true">
                    <script type="application/json" data-bracketry-data>
                        @json($bracket['data'])
                    </script>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body py-5 text-center text-muted">Belum ada bracket knockout yang bisa ditampilkan untuk filter ini.</div>
    </div>
@endforelse

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

@push('scripts')
    @vite(['resources/js/public-brackets.js'])
@endpush

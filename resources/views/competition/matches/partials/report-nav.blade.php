@php
    $sharedQuery = array_filter([
        'age_group_id' => request('age_group_id'),
    ], fn ($value) => filled($value));
@endphp

<div class="card mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1">Navigasi Laporan</h5>
            <p class="text-muted mb-0">Klasemen, top skor, top assist, bracket knockout, dan rekap PDF sekarang dipisah per halaman.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.standings', $sharedQuery) }}" class="btn {{ request()->routeIs('reports.standings') ? 'btn-primary' : 'btn-light' }}">Klasemen</a>
            <a href="{{ route('reports.top-scorers', $sharedQuery) }}" class="btn {{ request()->routeIs('reports.top-scorers') ? 'btn-primary' : 'btn-light' }}">Top Skor</a>
            <a href="{{ route('reports.top-assists', $sharedQuery) }}" class="btn {{ request()->routeIs('reports.top-assists') ? 'btn-primary' : 'btn-light' }}">Top Assist</a>
            <a href="{{ route('reports.brackets', $sharedQuery) }}" class="btn {{ request()->routeIs('reports.brackets') ? 'btn-primary' : 'btn-light' }}">Bagan Knockout</a>
            <a href="{{ route('reports.overview', $sharedQuery) }}" class="btn {{ request()->routeIs('reports.overview') ? 'btn-primary' : 'btn-light' }}">Rekap PDF</a>
        </div>
    </div>
</div>

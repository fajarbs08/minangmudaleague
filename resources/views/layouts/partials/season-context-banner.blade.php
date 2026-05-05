@php
    $seasonContext = app(\App\Services\SeasonContext::class);
    $currentSeason = $seasonContext->current();
@endphp

@if ($currentSeason)
    <div class="alert {{ $seasonContext->isViewingHistory() ? 'alert-warning' : 'alert-info' }} d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4" role="alert">
        <div>
            <div class="fw-semibold mb-1">
                Season ditampilkan: {{ $currentSeason->name }}
                <span class="badge {{ $seasonContext->isViewingHistory() ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info' }} ms-2">
                    {{ $seasonContext->isViewingHistory() ? 'Histori Read-Only' : 'Season Aktif' }}
                </span>
            </div>
            <div class="small mb-0">
                @if ($seasonContext->isViewingHistory())
                    Kamu sedang melihat data histori. Semua data kompetisi pada season ini hanya bisa dibaca.
                @else
                    Semua data kompetisi baru akan masuk ke season aktif ini.
                @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($seasonContext->isViewingHistory())
                <form method="POST" action="{{ route('seasons.select') }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->getRequestUri() }}">
                    <button type="submit" class="btn btn-sm btn-light">Kembali ke Season Aktif</button>
                </form>
            @endif

            @if (auth()->user()?->isAdmin())
                <a href="{{ route('seasons.index') }}" class="btn btn-sm btn-outline-secondary">Kelola Season</a>
            @endif
        </div>
    </div>
@endif

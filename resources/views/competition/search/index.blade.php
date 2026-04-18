@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Pencarian</h4>
        <p class="text-muted mb-0">Hasil pencarian untuk: <strong>{{ $query ?: '-' }}</strong></p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('search.index') }}" class="row g-3" data-search-form>
            <div class="col-lg-8">
                <div class="position-relative">
                    <input
                        type="search"
                        name="q"
                        class="form-control"
                        placeholder="Cari klub, pemain, ofisial, atau DSP..."
                        value="{{ $query }}"
                        autocomplete="off"
                        data-search-autocomplete
                        data-search-suggest-url="{{ route('search.suggestions') }}"
                    >
                </div>
            </div>
            <div class="col-lg-2 d-grid">
                <a href="{{ route('search.index', ['q' => $query]) }}"
                   class="btn btn-outline-primary {{ $query ? '' : 'd-none' }}"
                   data-search-lucky>
                    Hasil Teratas
                </a>
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>
    </div>
</div>

@if (!$query)
    <div class="text-muted">Masukkan kata kunci di atas untuk memulai pencarian.</div>
@else
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Klub</h5>
                </div>
                <div class="card-body">
                    @forelse ($clubs as $club)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $club->name }}</div>
                                <div class="text-muted small">{{ $club->zone ?: '-' }}</div>
                            </div>
                            <a href="{{ route('clubs.edit', $club) }}" class="btn btn-sm btn-light">Lihat</a>
                        </div>
                    @empty
                        <div class="text-muted">Tidak ada klub ditemukan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Ofisial</h5>
                </div>
                <div class="card-body">
                    @forelse ($officials as $official)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $official->name }}</div>
                                <div class="text-muted small">{{ $official->role ?: '-' }}</div>
                            </div>
                            <a href="{{ route('officials.show', $official) }}" class="btn btn-sm btn-light">Lihat</a>
                        </div>
                    @empty
                        <div class="text-muted">Tidak ada ofisial ditemukan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Pemain</h5>
                </div>
                <div class="card-body">
                    @forelse ($players as $player)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $player->name }}</div>
                                <div class="text-muted small">{{ $player->school_name ?: '-' }}</div>
                            </div>
                            <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-light">Lihat</a>
                        </div>
                    @empty
                        <div class="text-muted">Tidak ada pemain ditemukan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">DSP</h5>
                </div>
                <div class="card-body">
                    @forelse ($lineups as $lineup)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $lineup->title }}</div>
                                <div class="text-muted small">{{ $lineup->match_day ?: '-' }}</div>
                            </div>
                            <a href="{{ route('lineup-lists.show', $lineup) }}" class="btn btn-sm btn-light">Lihat</a>
                        </div>
                    @empty
                        <div class="text-muted">Tidak ada DSP ditemukan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

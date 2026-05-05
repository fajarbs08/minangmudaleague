@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Season</h4>
        <p class="text-muted mb-0">Kelola season aktif dan histori agar dashboard, jadwal, hasil, dan laporan tidak tercampur antar musim.</p>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Total Season</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $seasons->count() }}</h3>
                    <span class="badge bg-primary-subtle text-primary">Musim</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Season Aktif</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $seasons->where('is_active', true)->count() }}</h3>
                    <span class="badge bg-success-subtle text-success">Aktif</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Season Arsip</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $seasons->where('status', \App\Models\Season::STATUS_ARCHIVED)->count() }}</h3>
                    <span class="badge bg-warning-subtle text-warning">Read-Only</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Season Dipilih</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ optional($seasons->firstWhere('id', $selectedSeasonId))->name ?: '-' }}</h3>
                    <span class="badge bg-info-subtle text-info">Context</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Buat Season Baru</h4>
                <p class="text-muted mb-0">Season baru dibuat sebagai draft agar aman disiapkan sebelum diaktifkan.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('seasons.store') }}" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Musim 2027" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="Kosongkan untuk auto-generate">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mulai</label>
                        <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selesai</label>
                        <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-primary">Simpan Season</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="row g-3">
            @forelse ($seasons as $season)
                @php
                    $isSelected = (int) $selectedSeasonId === (int) $season->id;
                    $isActive = (bool) $season->is_active;
                    $statusBadge = match ($season->status) {
                        \App\Models\Season::STATUS_ACTIVE => 'bg-success-subtle text-success',
                        \App\Models\Season::STATUS_ARCHIVED => 'bg-warning-subtle text-warning',
                        default => 'bg-secondary-subtle text-secondary',
                    };
                @endphp
                <div class="col-12">
                    <div class="card {{ $isSelected ? 'border-primary' : '' }}">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h4 class="card-title mb-0">{{ $season->name }}</h4>
                                    <span class="badge {{ $statusBadge }} text-uppercase">{{ $season->status }}</span>
                                    @if ($isActive)
                                        <span class="badge bg-primary-subtle text-primary">Aktif Sistem</span>
                                    @endif
                                    @if ($isSelected)
                                        <span class="badge bg-info-subtle text-info">Sedang Dilihat</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-0">
                                    {{ $season->slug }}
                                    <span class="mx-1">•</span>
                                    {{ $season->starts_at?->format('d M Y') ?: 'Tanggal mulai belum diatur' }}
                                    <span class="mx-1">-</span>
                                    {{ $season->ends_at?->format('d M Y') ?: 'Tanggal selesai belum diatur' }}
                                </p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @if (! $isSelected)
                                        <form method="POST" action="{{ route('seasons.select') }}">
                                            @csrf
                                            <input type="hidden" name="season_id" value="{{ $season->id }}">
                                        <input type="hidden" name="redirect_to" value="{{ route('seasons.index', [], false) }}">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Lihat</button>
                                        </form>
                                @endif

                                @if (! $isActive)
                                    <form method="POST" action="{{ route('seasons.activate', $season) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Aktifkan</button>
                                    </form>
                                @endif

                                @if (! $isActive && $season->status !== \App\Models\Season::STATUS_ARCHIVED)
                                    <form method="POST" action="{{ route('seasons.archive', $season) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Arsipkan</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4 col-xl-2">
                                    <div class="small text-muted">Klub</div>
                                    <div class="fw-semibold">{{ $season->season_clubs_count }}</div>
                                </div>
                                <div class="col-md-4 col-xl-2">
                                    <div class="small text-muted">Ofisial</div>
                                    <div class="fw-semibold">{{ $season->season_officials_count }}</div>
                                </div>
                                <div class="col-md-4 col-xl-2">
                                    <div class="small text-muted">Pemain</div>
                                    <div class="fw-semibold">{{ $season->season_players_count }}</div>
                                </div>
                                <div class="col-md-4 col-xl-3">
                                    <div class="small text-muted">Jadwal</div>
                                    <div class="fw-semibold">{{ $season->match_schedules_count }}</div>
                                </div>
                                <div class="col-md-4 col-xl-3">
                                    <div class="small text-muted">DSP</div>
                                    <div class="fw-semibold">{{ $season->lineup_lists_count }}</div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('seasons.update', $season) }}" class="row g-3">
                                @csrf
                                @method('PUT')
                                <div class="col-lg-4">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $season->name) }}" required>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $season->slug) }}">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Mulai</label>
                                    <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', optional($season->starts_at)->toDateString()) }}">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Selesai</label>
                                    <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at', optional($season->ends_at)->toDateString()) }}">
                                </div>
                                <div class="col-lg-1 d-grid">
                                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-light">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">Belum ada season.</div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

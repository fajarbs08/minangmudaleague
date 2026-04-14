@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['club_id', 'age_group_id', 'lineup_status']))->filter(fn ($value) => filled($value))->count();
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Jadwal Match</li>
            </ol>
        </nav>
        <h4 class="mb-1">Jadwal Pertandingan</h4>
        <p class="text-muted mb-0">Admin menentukan lawan, venue, tanggal, jam, dan matchday untuk dipakai di DSP.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a
            href="#match-filter-panel"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="{{ $filterCount ? 'true' : 'false' }}"
            aria-controls="match-filter-panel"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </a>
        <a href="{{ route('matches.create') }}" class="btn btn-primary">Tambah Match</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body border-bottom collapse {{ $filterCount ? 'show' : '' }}" id="match-filter-panel">
        <form class="row g-3">
            <div class="col-lg-4">
                <select name="club_id" class="form-select">
                    <option value="">Semua klub</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((string) request('club_id') === (string) $club->id)>{{ $club->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <select name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select name="lineup_status" class="form-select">
                    <option value="">Semua status DSP</option>
                    <option value="pending" @selected(request('lineup_status') === 'pending')>Belum lengkap DSP</option>
                    <option value="complete" @selected(request('lineup_status') === 'complete')>Lengkap DSP</option>
                </select>
            </div>
            <div class="col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('matches.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle text-nowrap">
                <thead>
                    <tr>
                        <th>Matchday</th>
                        <th>Pertandingan</th>
                        <th>Kelompok Usia</th>
                        <th>Venue</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Status DSP</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matches as $match)
                        @php
                            $lineupClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id);
                            $clubAReady = $lineupClubIds->contains((int) $match->club_a_id);
                            $clubBReady = $lineupClubIds->contains((int) $match->club_b_id);
                            $isComplete = $clubAReady && $clubBReady;
                        @endphp
                        <tr>
                            <td>{{ $match->match_day }}</td>
                            <td>{{ $match->clubA?->name }} vs {{ $match->clubB?->name }}</td>
                            <td>{{ $match->ageGroup?->name }}</td>
                            <td>{{ $match->venue }}</td>
                            <td>{{ optional($match->match_date)->format('d M Y') }}</td>
                            <td>{{ optional($match->kickoff_time)->format('H:i') }} WIB</td>
                            <td>
                                <span class="badge {{ $isComplete ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $isComplete ? 'Lengkap' : 'Belum lengkap' }}
                                </span>
                                <div class="small text-muted mt-1">
                                    {{ $match->clubA?->short_name ?: $match->clubA?->name }}: {{ $clubAReady ? 'siap' : 'belum' }} ·
                                    {{ $match->clubB?->short_name ?: $match->clubB?->name }}: {{ $clubBReady ? 'siap' : 'belum' }}
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('matches.edit', $match) }}" class="btn btn-sm btn-light">Edit</a>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger js-delete-match"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteMatchModal"
                                        data-action="{{ route('matches.destroy', $match) }}"
                                        data-name="{{ $match->clubA?->name }} vs {{ $match->clubB?->name }}"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="competition-table-empty">Belum ada jadwal pertandingan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 px-3 pb-3">{{ $matches->links() }}</div>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteMatchModal',
    'title' => 'Hapus Jadwal Match',
    'formId' => 'delete-match-form',
    'nameClass' => 'js-delete-match-name',
    'messagePrefix' => 'Jadwal',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])
@endsection

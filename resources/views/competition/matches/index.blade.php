@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['club_id', 'age_group_id', 'lineup_status', 'competition_format']))->filter(fn ($value) => filled($value))->count();
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
                <select name="competition_format" class="form-select">
                    <option value="">Semua format</option>
                    @foreach ($formatOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_format') === $value)>{{ $label }}</option>
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
            <div class="col-lg-12 d-flex gap-2">
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
                        @include('competition.partials.sortable-th', ['key' => 'match_day', 'label' => 'Matchday', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'matchup', 'label' => 'Pertandingan', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Kelompok Usia', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'competition_format', 'label' => 'Format', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'round_order', 'label' => 'Babak', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'venue', 'label' => 'Venue', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'match_date', 'label' => 'Tanggal', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        @include('competition.partials.sortable-th', ['key' => 'kickoff_time', 'label' => 'Jam', 'defaultSort' => 'match_date', 'defaultDirection' => 'asc'])
                        <th>Status DSP</th>
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matches as $match)
                        @php
                            $lineupClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id);
                            $clubALineup = $match->lineupLists->firstWhere('club_id', $match->club_a_id);
                            $clubBLineup = $match->lineupLists->firstWhere('club_id', $match->club_b_id);
                            $clubAReady = $lineupClubIds->contains((int) $match->club_a_id);
                            $clubBReady = $lineupClubIds->contains((int) $match->club_b_id);
                            $isComplete = $clubAReady && $clubBReady;
                        @endphp
                        <tr>
                            <td>{{ $match->match_day }}</td>
                            <td>{{ $match->clubA?->name }} vs {{ $match->clubB?->name }}</td>
                            <td>{{ $match->ageGroup?->name }}</td>
                            <td>
                                <span class="badge {{ $match->competition_format === 'knockout' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }}">
                                    {{ $match->competition_format_label }}
                                </span>
                            </td>
                            <td>{{ $match->round_display_label }}</td>
                            <td>{{ $match->venue }}</td>
                            <td>{{ optional($match->match_date)->format('d M Y') }}</td>
                            <td>{{ optional($match->kickoff_time)->format('H:i') }} WIB</td>
                            <td>
                                <span class="badge {{ $isComplete ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $isComplete ? 'Lengkap' : 'Belum lengkap' }}
                                </span>
                                <div class="small mt-2 d-flex flex-column gap-1">
                                    <div>
                                        <span class="text-muted">{{ $match->clubA?->short_name ?: $match->clubA?->name }}:</span>
                                        @if ($clubALineup)
                                            <a href="{{ route('lineup-lists.show', $clubALineup) }}" class="fw-semibold">Lihat DSP</a>
                                        @else
                                            <span class="text-muted">belum ada</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-muted">{{ $match->clubB?->short_name ?: $match->clubB?->name }}:</span>
                                        @if ($clubBLineup)
                                            <a href="{{ route('lineup-lists.show', $clubBLineup) }}" class="fw-semibold">Lihat DSP</a>
                                        @else
                                            <span class="text-muted">belum ada</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-end competition-table-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Tindakan</span>
                                        <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                        <div class="competition-action-section">
                                            <div class="competition-action-label px-2 pb-2">Jadwal Pertandingan</div>
                                            <div class="small text-muted px-2 pb-2 text-wrap">Kelola detail jadwal atau hapus jadwal yang belum dipakai DSP.</div>
                                            @include('competition.partials.action-item', [
                                                'href' => route('matches.edit', $match),
                                                'icon' => 'square-pen',
                                                'label' => 'Edit Jadwal',
                                            ])
                                            @include('competition.partials.action-item', [
                                                'icon' => 'trash-2',
                                                'label' => 'Hapus Jadwal',
                                                'class' => 'text-danger js-delete-match',
                                                'attributes' => [
                                                    'data-bs-toggle' => 'modal',
                                                    'data-bs-target' => '#deleteMatchModal',
                                                    'data-action' => route('matches.destroy', $match),
                                                    'data-name' => $match->clubA?->name.' vs '.$match->clubB?->name,
                                                ],
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="competition-table-empty">Belum ada jadwal pertandingan.</td>
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

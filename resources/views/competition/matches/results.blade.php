@extends('layouts.vertical', ['title' => $title])

@php
    $filterCount = collect(request()->only(['age_group_id', 'competition_format']))->filter(fn ($value) => filled($value))->count();
    $resultModalPayload = $matches->getCollection()->mapWithKeys(function ($match) {
        $playersByClub = $match->lineupLists
            ->groupBy(fn ($lineup) => (int) $lineup->club_id)
            ->map(function ($lineups) use ($match) {
                return $lineups
                    ->flatMap(function ($lineup) use ($match) {
                        return $lineup->players->map(function ($player) use ($match) {
                            $jerseyNumber = $player->pivot->jersey_number ?? $player->displayJerseyNumber($match->age_group_id);

                            return [
                                'id' => (int) $player->id,
                                'label' => trim(($jerseyNumber ? '#'.$jerseyNumber.' ' : '').$player->name),
                            ];
                        });
                    })
                    ->unique('id')
                    ->values();
            });

        return [
            $match->id => [
                'id' => (int) $match->id,
                'title' => $match->clubA?->name.' vs '.$match->clubB?->name,
                'route' => route('match-results.update', $match),
                'score_a' => $match->score_club_a,
                'score_b' => $match->score_club_b,
                'is_finished' => $match->is_finished,
                'clubs' => [
                    [
                        'id' => (int) $match->club_a_id,
                        'label' => $match->clubA?->name ?: 'Klub A',
                        'players' => $playersByClub->get((int) $match->club_a_id, collect())->all(),
                    ],
                    [
                        'id' => (int) $match->club_b_id,
                        'label' => $match->clubB?->name ?: 'Klub B',
                        'players' => $playersByClub->get((int) $match->club_b_id, collect())->all(),
                    ],
                ],
                'goal_events' => $match->goalEvents
                    ->map(fn ($goal) => [
                        'club_id' => (int) $goal->club_id,
                        'player_id' => (int) $goal->player_id,
                        'assist_player_id' => $goal->assist_player_id ? (int) $goal->assist_player_id : null,
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    });
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Hasil Pertandingan</li>
            </ol>
        </nav>
        <h4 class="mb-1">Hasil Pertandingan</h4>
        <p class="text-muted mb-0">Kelola hasil laga untuk pertandingan yang sudah terjadwal.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a
            href="#result-filter-panel"
            class="btn btn-outline-secondary position-relative d-inline-flex align-items-center gap-2"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="{{ $filterCount ? 'true' : 'false' }}"
            aria-controls="result-filter-panel"
        >
            <i data-lucide="filter" class="fs-14"></i>
            <span>Filter</span>
            @if ($filterCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $filterCount }}</span>
            @endif
        </a>
    </div>
</div>

@include('competition.partials.flash')

<style>
    .knockout-bracket {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(240px, 1fr);
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: .5rem;
    }

    .knockout-round {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .knockout-match {
        border: 1px solid #dee2e6;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        padding: 1rem;
    }

    .knockout-team {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .knockout-team + .knockout-team {
        margin-top: .65rem;
        padding-top: .65rem;
        border-top: 1px dashed rgba(100, 116, 139, 0.3);
    }

    .knockout-score {
        min-width: 24px;
        text-align: right;
        font-weight: 700;
    }

    .match-report-line + .match-report-line {
        margin-top: .25rem;
    }

    .goal-event-row {
        border: 1px solid rgba(100, 116, 139, 0.18);
        border-radius: .9rem;
        padding: .85rem;
        background: rgba(248, 250, 252, 0.7);
    }
</style>

@if ($standings->isNotEmpty())
    <div class="row g-4 mb-4">
        @foreach ($standings as $standing)
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Klasemen {{ $standing['age_group']?->name ?: '-' }}</h4>
                        <p class="text-muted mb-0">Disusun dari hasil pertandingan liga yang telah selesai.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive competition-table-wrap">
                            <table class="table competition-table align-middle text-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Pos</th>
                                        <th>Klub</th>
                                        <th>Main</th>
                                        <th>Menang</th>
                                        <th>Imbang</th>
                                        <th>Kalah</th>
                                        <th>GM</th>
                                        <th>GK</th>
                                        <th>SG</th>
                                        <th>Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($standing['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['position'] }}</td>
                                            <td class="fw-semibold">{{ $row['club_short_name'] }}</td>
                                            <td>{{ $row['played'] }}</td>
                                            <td>{{ $row['won'] }}</td>
                                            <td>{{ $row['drawn'] }}</td>
                                            <td>{{ $row['lost'] }}</td>
                                            <td>{{ $row['goals_for'] }}</td>
                                            <td>{{ $row['goals_against'] }}</td>
                                            <td>{{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}</td>
                                            <td><span class="fw-bold">{{ $row['points'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if ($brackets->isNotEmpty())
    <div class="row g-4 mb-4">
        @foreach ($brackets as $bracket)
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Bracket {{ $bracket['age_group']?->name ?: '-' }}</h4>
                        <p class="text-muted mb-0">Jalur pertandingan knockout berdasarkan babak dan urutan bracket.</p>
                    </div>
                    <div class="card-body">
                        <div class="knockout-bracket">
                            @foreach ($bracket['rounds'] as $round)
                                <div class="knockout-round">
                                    <div class="small fw-semibold text-uppercase text-muted">{{ $round['label'] }}</div>
                                    @foreach ($round['matches'] as $match)
                                        <div class="knockout-match">
                                            <div class="small text-muted mb-3">
                                                {{ $match->match_day }} • {{ optional($match->match_date)->format('d M Y') ?: '-' }}
                                            </div>
                                            <div class="knockout-team">
                                                <span>{{ $match->clubA?->name ?: 'TBD' }}</span>
                                                <span class="knockout-score">{{ $match->score_club_a ?? '-' }}</span>
                                            </div>
                                            <div class="knockout-team">
                                                <span>{{ $match->clubB?->name ?: 'TBD' }}</span>
                                                <span class="knockout-score">{{ $match->score_club_b ?? '-' }}</span>
                                            </div>
                                            <div class="small text-muted mt-3">
                                                {{ $match->is_finished ? $match->result_summary : 'Belum selesai' }}
                                            </div>
                                            @if ($match->goalEvents->isNotEmpty())
                                                <div class="small text-muted mt-2">
                                                    @foreach ([$match->clubA, $match->clubB] as $club)
                                                        @php
                                                            $goalReport = $match->goalReportForClub($club?->id);
                                                        @endphp
                                                        @if ($club && !empty($goalReport))
                                                            <div class="match-report-line">
                                                                <span class="fw-semibold">{{ $club->short_name ?: $club->name }}:</span>
                                                                {{ implode(', ', $goalReport) }}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">Daftar Hasil Pertandingan</h4>
            <p class="text-muted mb-0">Pantau hasil, status DSP, dan tindak lanjut admin dari panel dengan format yang seragam.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if (filled(request('competition_format')))
                <span class="badge bg-dark-subtle text-dark">Format: {{ $formatOptions[request('competition_format')] ?? request('competition_format') }}</span>
            @endif
            @if (filled(request('age_group_id')))
                <span class="badge bg-secondary-subtle text-secondary">Usia terfilter</span>
            @endif
            <span class="badge bg-light text-dark border">{{ $matches->total() }} data</span>
        </div>
    </div>
    <div class="card-body border-bottom collapse {{ $filterCount ? 'show' : '' }}" id="result-filter-panel">
        <form class="row g-3">
            <div class="col-lg-4">
                <select name="age_group_id" class="form-select">
                    <option value="">Semua kelompok usia</option>
                    @foreach ($ageGroups as $ageGroup)
                        <option value="{{ $ageGroup->id }}" @selected((string) request('age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <select name="competition_format" class="form-select">
                    <option value="">Semua format</option>
                    @foreach ($formatOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_format') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('match-results.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle text-nowrap">
                <thead>
                    <tr>
                        @include('competition.partials.sortable-th', ['key' => 'match_day', 'label' => 'Matchday', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'matchup', 'label' => 'Pertandingan', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'age_group', 'label' => 'Kelompok Usia', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'competition_format', 'label' => 'Format', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'round_order', 'label' => 'Babak', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'match_date', 'label' => 'Tanggal', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'venue', 'label' => 'Venue', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        <th>Status DSP</th>
                        @include('competition.partials.sortable-th', ['key' => 'is_finished', 'label' => 'Hasil', 'defaultSort' => 'match_date', 'defaultDirection' => 'desc'])
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matches as $match)
                        @php
                            $lineupClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id);
                            $isComplete = $lineupClubIds->contains((int) $match->club_a_id) && $lineupClubIds->contains((int) $match->club_b_id);
                        @endphp
                        <tr>
                            <td>{{ $match->match_day }}</td>
                            <td>{{ $match->clubA?->name }} vs {{ $match->clubB?->name }}</td>
                            <td>{{ $match->ageGroup?->name ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $match->competition_format === 'knockout' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }}">
                                    {{ $match->competition_format_label }}
                                </span>
                            </td>
                            <td>{{ $match->competition_format === 'knockout' ? $match->round_display_label : '-' }}</td>
                            <td>
                                <div>{{ optional($match->match_date)->format('d M Y') ?: '-' }}</div>
                                <div class="small text-muted">{{ optional($match->kickoff_time)->format('H:i') ?: '-' }} WIB</div>
                            </td>
                            <td>{{ $match->venue }}</td>
                            <td>
                                <span class="badge {{ $isComplete ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $isComplete ? 'Lengkap' : 'Belum lengkap' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $match->score_label }}</div>
                                <div class="small text-muted">{{ $match->result_summary }}</div>
                                @if ($match->goalEvents->isNotEmpty())
                                    <div class="small text-muted text-wrap mt-2">
                                        @foreach ([$match->clubA, $match->clubB] as $club)
                                            @php
                                                $goalReport = $match->goalReportForClub($club?->id);
                                            @endphp
                                            @if ($club && !empty($goalReport))
                                                <div class="match-report-line">
                                                    <span class="fw-semibold">{{ $club->short_name ?: $club->name }}:</span>
                                                    {{ implode(', ', $goalReport) }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-end competition-table-actions">
                                @if (auth()->user()->isAdmin())
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span>Tindakan</span>
                                            <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                            <div class="competition-action-section">
                                                <div class="competition-action-label px-2 pb-2">Hasil Pertandingan</div>
                                                <div class="small text-muted px-2 pb-2 text-wrap">
                                                    {{ $match->is_finished ? 'Perbarui skor dan status pertandingan ini.' : 'Input skor setelah pertandingan selesai.' }}
                                                </div>
                                                @include('competition.partials.action-item', [
                                                    'icon' => $match->is_finished ? 'square-pen' : 'clipboard-pen',
                                                    'label' => $match->is_finished ? 'Edit Hasil' : 'Input Hasil',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#matchResultModal',
                                                        'data-match-id' => $match->id,
                                                    ],
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">Lihat hasil</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="competition-table-empty">Belum ada data hasil pertandingan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 px-3 pb-3">{{ $matches->links() }}</div>
    </div>
</div>

@if (auth()->user()->isAdmin())
    <div class="modal fade" id="matchResultModal" tabindex="-1" aria-labelledby="matchResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" id="match-result-form">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="matchResultModalLabel">Input Hasil Pertandingan</h5>
                        <div class="small text-muted mt-1" id="match-result-title">-</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="match-score-a" class="form-label">Skor Tim A</label>
                            <input type="number" min="0" max="99" class="form-control" name="score_club_a" id="match-score-a">
                        </div>
                        <div class="col-6">
                            <label for="match-score-b" class="form-label">Skor Tim B</label>
                            <input type="number" min="0" max="99" class="form-control" name="score_club_b" id="match-score-b">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="match-is-finished" name="is_finished" value="1">
                                <label class="form-check-label" for="match-is-finished">Pertandingan selesai</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <label class="form-label mb-1">Report Gol</label>
                                    <div class="small text-muted">Ambil pencetak gol dan assist dari pemain yang ada di DSP pertandingan.</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-goal-event-row">Tambah Gol</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div id="goal-event-list" class="d-grid gap-2"></div>
                            <div class="small text-muted mt-2" id="goal-event-empty-state">Belum ada report gol.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Hasil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('matchResultModal');
        if (!modal) return;
        const payloadByMatch = @json($resultModalPayload);
        const goalEventList = document.getElementById('goal-event-list');
        const addGoalEventButton = document.getElementById('add-goal-event-row');
        const emptyState = document.getElementById('goal-event-empty-state');
        const clubOptionsMarkup = (clubs, selectedClubId) => {
            const options = ['<option value="">Tim</option>'];
            clubs.forEach((club) => {
                const selected = String(selectedClubId || '') === String(club.id) ? ' selected' : '';
                options.push(`<option value="${club.id}"${selected}>${club.label}</option>`);
            });

            return options.join('');
        };

        const playerOptionsMarkup = (clubs, clubId, selectedPlayerId, includeEmptyLabel) => {
            const selectedClub = clubs.find((club) => String(club.id) === String(clubId));
            const options = [`<option value="">${includeEmptyLabel}</option>`];

            (selectedClub?.players || []).forEach((player) => {
                const selected = String(selectedPlayerId || '') === String(player.id) ? ' selected' : '';
                options.push(`<option value="${player.id}"${selected}>${player.label}</option>`);
            });

            return options.join('');
        };

        const syncEmptyState = () => {
            emptyState.classList.toggle('d-none', goalEventList.children.length > 0);
        };

        const updateRowPlayerOptions = (row, clubs) => {
            const clubSelect = row.querySelector('[data-goal-club]');
            const scorerSelect = row.querySelector('[data-goal-player]');
            const assistSelect = row.querySelector('[data-goal-assist]');
            const currentScorer = scorerSelect.value;
            const currentAssist = assistSelect.value;

            scorerSelect.innerHTML = playerOptionsMarkup(clubs, clubSelect.value, currentScorer, 'Pencetak gol');
            assistSelect.innerHTML = playerOptionsMarkup(clubs, clubSelect.value, currentAssist, 'Tanpa assist');
        };

        const appendGoalEventRow = (matchData, eventData = {}) => {
            const index = goalEventList.children.length;
            const row = document.createElement('div');
            row.className = 'goal-event-row';
            row.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Tim</label>
                        <select class="form-select" name="goal_events[${index}][club_id]" data-goal-club>
                            ${clubOptionsMarkup(matchData.clubs, eventData.club_id)}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pencetak Gol</label>
                        <select class="form-select" name="goal_events[${index}][player_id]" data-goal-player></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assist</label>
                        <select class="form-select" name="goal_events[${index}][assist_player_id]" data-goal-assist></select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-light w-100" data-remove-goal-row>&times;</button>
                    </div>
                </div>
            `;

            goalEventList.appendChild(row);
            updateRowPlayerOptions(row, matchData.clubs);

            if (eventData.player_id) {
                row.querySelector('[data-goal-player]').value = String(eventData.player_id);
            }

            if (eventData.assist_player_id) {
                row.querySelector('[data-goal-assist]').value = String(eventData.assist_player_id);
            }

            row.querySelector('[data-goal-club]').addEventListener('change', () => updateRowPlayerOptions(row, matchData.clubs));
            row.querySelector('[data-remove-goal-row]').addEventListener('click', () => {
                row.remove();
                Array.from(goalEventList.children).forEach((item, newIndex) => {
                    item.querySelectorAll('select').forEach((select) => {
                        select.name = select.name.replace(/goal_events\[\d+\]/, `goal_events[${newIndex}]`);
                    });
                });
                syncEmptyState();
            });

            syncEmptyState();
        };

        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const matchId = trigger.getAttribute('data-match-id');
            const matchData = payloadByMatch[matchId];
            if (!matchData) return;

            const form = document.getElementById('match-result-form');
            const title = document.getElementById('match-result-title');
            const scoreA = document.getElementById('match-score-a');
            const scoreB = document.getElementById('match-score-b');
            const finished = document.getElementById('match-is-finished');

            form.action = matchData.route;
            title.textContent = matchData.title || '-';
            scoreA.value = matchData.score_a ?? '';
            scoreB.value = matchData.score_b ?? '';
            finished.checked = !!matchData.is_finished;
            goalEventList.innerHTML = '';

            if (Array.isArray(matchData.goal_events) && matchData.goal_events.length) {
                matchData.goal_events.forEach((goalEvent) => appendGoalEventRow(matchData, goalEvent));
            }

            syncEmptyState();

            addGoalEventButton.onclick = () => appendGoalEventRow(matchData);
        });
    });
</script>
@endif
@endsection

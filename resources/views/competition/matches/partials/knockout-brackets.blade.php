@php
    $sectionTitle = $sectionTitle ?? 'Bagan Knockout';
    $sectionDescription = $sectionDescription ?? 'Jalur pertandingan babak gugur berdasarkan urutan babak dan posisi laga.';
    $emptyMessage = $emptyMessage ?? 'Belum ada bagan knockout yang bisa ditampilkan.';
@endphp

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
</style>

<div class="row g-4">
    @forelse ($brackets as $bracket)
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-1">{{ $sectionTitle }} {{ $bracket['age_group']?->name ?: '-' }}</h4>
                    <p class="text-muted mb-0">{{ $sectionDescription }}</p>
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
                                            <span>{{ $match->clubA?->name ?: 'Menunggu' }}</span>
                                            <span class="knockout-score">{{ $match->score_club_a ?? '-' }}</span>
                                        </div>
                                        <div class="knockout-team">
                                            <span>{{ $match->clubB?->name ?: 'Menunggu' }}</span>
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
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body py-5 text-center text-muted">{{ $emptyMessage }}</div>
            </div>
        </div>
    @endforelse
</div>

@extends('layouts.vertical', ['title' => $title])

@php
    $isHistoryView = app(\App\Services\SeasonContext::class)->isViewingHistory();
@endphp

@section('content')
<style>
    .knockout-admin-feedback {
        display: none;
    }

    .knockout-admin-feedback.is-visible {
        display: block;
    }

    .knockout-admin-age-switcher {
        display: flex;
        gap: .75rem;
        overflow-x: auto;
        padding-bottom: .25rem;
    }

    .knockout-admin-age-pill {
        min-width: 148px;
        display: flex;
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

    .knockout-admin-age-pill:hover,
    .knockout-admin-age-pill:focus-visible {
        border-color: rgba(59, 130, 246, .45);
        background: rgba(59, 130, 246, .04);
        transform: translateY(-1px);
    }

    .knockout-admin-age-pill.is-active {
        border-color: rgba(37, 99, 235, .24);
        background: linear-gradient(135deg, rgba(37, 99, 235, .08), rgba(14, 165, 233, .12));
    }

    .knockout-admin-age-pill-name {
        font-weight: 600;
        white-space: nowrap;
    }

    .knockout-admin-age-pill-count {
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

    .knockout-admin-board-wrap {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: .5rem;
        align-items: flex-start;
    }

    .knockout-admin-round {
        min-width: 290px;
        display: flex;
        flex-direction: column;
        gap: .875rem;
    }

    .knockout-admin-round-header {
        padding: 1rem 1.125rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, rgba(37, 99, 235, .08), rgba(14, 165, 233, .12));
        border: 1px solid rgba(59, 130, 246, .12);
    }

    .knockout-admin-round-tabs {
        display: none;
        gap: .65rem;
        overflow-x: auto;
        padding-bottom: .35rem;
        margin-bottom: 1rem;
    }

    .knockout-admin-round-tab {
        min-width: 148px;
        text-align: left;
        padding: .85rem 1rem;
        border-radius: .9rem;
        border: 1px solid rgba(148, 163, 184, .25);
        background: #fff;
        color: #0f172a;
        transition: border-color .2s ease, background-color .2s ease;
    }

    .knockout-admin-round-tab.is-active {
        border-color: rgba(37, 99, 235, .24);
        background: linear-gradient(135deg, rgba(37, 99, 235, .08), rgba(14, 165, 233, .12));
    }

    .knockout-admin-slot {
        border: 1px dashed rgba(148, 163, 184, .8);
        border-radius: 1rem;
        padding: .75rem;
        background: #f8fafc;
        min-height: 198px;
        transition: border-color .2s ease, background-color .2s ease, transform .2s ease;
        position: relative;
        overflow: visible;
    }

    .knockout-admin-slot.is-over {
        border-color: #2563eb;
        background: rgba(37, 99, 235, .08);
        transform: translateY(-2px);
    }

    .knockout-admin-slot.is-occupied {
        border-style: solid;
        background: #fff;
    }

    .knockout-admin-slot.has-connector::after {
        content: "";
        position: absolute;
        top: 50%;
        right: -1rem;
        width: 1rem;
        height: 3px;
        border-radius: 999px;
        background: rgba(148, 163, 184, .45);
        transform: translateY(-50%);
    }

    .knockout-admin-slot.has-connector::before {
        content: "";
        position: absolute;
        top: 50%;
        right: -1.18rem;
        width: .42rem;
        height: .42rem;
        border-radius: 999px;
        background: rgba(148, 163, 184, .65);
        transform: translateY(-50%);
        box-shadow: 0 0 0 6px rgba(255, 255, 255, .88);
    }

    .knockout-admin-slot.slot-tone-winner {
        border-color: rgba(34, 197, 94, .4);
        background: linear-gradient(180deg, rgba(240, 253, 244, .95), #fff 65%);
    }

    .knockout-admin-slot.slot-tone-winner.has-connector::after,
    .knockout-admin-slot.slot-tone-winner.has-connector::before {
        background: linear-gradient(90deg, #16a34a, #22c55e);
    }

    .knockout-admin-slot.slot-tone-occupied {
        border-color: rgba(59, 130, 246, .24);
    }

    .knockout-admin-slot.slot-tone-occupied.has-connector::after,
    .knockout-admin-slot.slot-tone-occupied.has-connector::before {
        background: linear-gradient(90deg, rgba(59, 130, 246, .55), rgba(14, 165, 233, .6));
    }

    .knockout-admin-slot.is-saved {
        animation: knockoutSavedPulse .9s ease-out;
    }

    .knockout-admin-slot-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
    }

    .knockout-admin-slot-label {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .knockout-admin-dropzone {
        min-height: 138px;
        display: flex;
        flex-direction: column;
    }

    .knockout-admin-match {
        border-radius: .9rem;
        border: 1px solid rgba(15, 23, 42, .08);
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        padding: .9rem;
        cursor: grab;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background-color .2s ease;
        position: relative;
    }

    .knockout-admin-match:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .12);
        border-color: rgba(59, 130, 246, .2);
    }

    .knockout-admin-match.is-decided {
        border-color: rgba(34, 197, 94, .25);
        box-shadow: 0 16px 34px rgba(22, 163, 74, .11);
    }

    .knockout-admin-match.gu-transit {
        cursor: grabbing;
    }

    .knockout-admin-match.gu-mirror {
        cursor: grabbing;
        transform: rotate(1.5deg);
    }

    .knockout-admin-handle {
        border: 0;
        background: transparent;
        color: #64748b;
        padding: 0;
        display: inline-flex;
        align-items: center;
        cursor: grab;
    }

    .knockout-admin-handle:active {
        cursor: grabbing;
    }

    .knockout-admin-kicker {
        color: #64748b;
        font-size: .78rem;
        font-weight: 600;
        margin-top: .5rem;
    }

    .knockout-admin-result-chip {
        margin-top: .65rem;
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        border-radius: 999px;
        padding: .28rem .7rem;
        font-size: .74rem;
        font-weight: 700;
        color: #166534;
        background: rgba(34, 197, 94, .12);
    }

    .knockout-admin-source-pills {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .7rem;
    }

    .knockout-admin-source-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .26rem .6rem;
        background: rgba(59, 130, 246, .08);
        color: #1d4ed8;
        font-size: .72rem;
        font-weight: 700;
    }

    .knockout-admin-teams {
        display: flex;
        flex-direction: column;
        gap: .45rem;
        margin-top: .6rem;
    }

    .knockout-admin-team {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        font-weight: 600;
        border-radius: .8rem;
        padding: .42rem .55rem;
        transition: background-color .2s ease, color .2s ease, opacity .2s ease;
    }

    .knockout-admin-team.is-winner {
        background: rgba(34, 197, 94, .12);
        color: #166534;
    }

    .knockout-admin-team.is-loser {
        opacity: .76;
    }

    .knockout-admin-score {
        min-width: 24px;
        text-align: right;
        color: #0f172a;
    }

    .knockout-admin-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem .75rem;
        color: #64748b;
        font-size: .8125rem;
        margin-top: .85rem;
    }

    .knockout-admin-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .9rem;
    }

    .knockout-admin-actions .btn {
        flex: 1 1 auto;
    }

    .knockout-admin-empty {
        min-height: 138px;
        flex: 1 1 auto;
        border-radius: .85rem;
        background: rgba(148, 163, 184, .08);
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1rem;
    }

    .knockout-admin-empty-link {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        font-weight: 600;
        text-decoration: none;
    }

    .knockout-admin-empty-link.d-none {
        display: none !important;
    }

    [data-knockout-board-root].is-dragging .knockout-admin-slot.slot-tone-empty {
        background: linear-gradient(180deg, rgba(239, 246, 255, .88), rgba(248, 250, 252, 1));
        border-color: rgba(59, 130, 246, .38);
    }

    [data-knockout-board-root].is-dragging .knockout-admin-empty {
        background: rgba(59, 130, 246, .08);
    }

    [data-knockout-board-root].is-dragging .knockout-admin-empty-link {
        color: #1d4ed8;
    }

    @keyframes knockoutSavedPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, .26);
        }
        100% {
            box-shadow: 0 0 0 14px rgba(34, 197, 94, 0);
        }
    }

    html[data-bs-theme="dark"] .knockout-admin-age-pill,
    html[data-bs-theme="dark"] .knockout-admin-round-tab,
    html[data-bs-theme="dark"] .knockout-admin-slot.is-occupied,
    html[data-bs-theme="dark"] .knockout-admin-match {
        background: var(--lap-admin-surface-card);
        color: var(--lap-admin-text-strong);
        border-color: var(--lap-admin-border-soft);
        box-shadow: var(--lap-admin-shadow-card);
    }

    html[data-bs-theme="dark"] .knockout-admin-age-pill:hover,
    html[data-bs-theme="dark"] .knockout-admin-age-pill:focus-visible,
    html[data-bs-theme="dark"] .knockout-admin-age-pill.is-active,
    html[data-bs-theme="dark"] .knockout-admin-round-tab.is-active,
    html[data-bs-theme="dark"] [data-knockout-board-root].is-dragging .knockout-admin-slot.slot-tone-empty {
        border-color: rgba(var(--bs-primary-rgb), 0.34);
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.14), rgba(14, 165, 233, 0.12));
    }

    html[data-bs-theme="dark"] .knockout-admin-age-pill-count,
    html[data-bs-theme="dark"] .knockout-admin-source-pill {
        background: rgba(var(--bs-primary-rgb), 0.16);
        color: #bfdbfe;
    }

    html[data-bs-theme="dark"] .knockout-admin-round-header {
        border-color: rgba(var(--bs-primary-rgb), 0.22);
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.16), rgba(14, 165, 233, 0.12));
    }

    html[data-bs-theme="dark"] .knockout-admin-slot {
        border-color: var(--lap-admin-border-strong);
        background: var(--lap-admin-surface-soft);
    }

    html[data-bs-theme="dark"] .knockout-admin-slot.slot-tone-winner {
        border-color: rgba(34, 197, 94, 0.32);
        background: linear-gradient(180deg, rgba(22, 163, 74, 0.16), rgba(22, 33, 51, 0.96) 68%);
    }

    html[data-bs-theme="dark"] .knockout-admin-slot.slot-tone-occupied {
        border-color: rgba(var(--bs-primary-rgb), 0.30);
    }

    html[data-bs-theme="dark"] .knockout-admin-slot.has-connector::after {
        background: rgba(125, 162, 220, 0.58);
    }

    html[data-bs-theme="dark"] .knockout-admin-slot.has-connector::before {
        background: rgba(125, 162, 220, 0.82);
        box-shadow: 0 0 0 6px rgba(15, 23, 42, 0.92);
    }

    html[data-bs-theme="dark"] .knockout-admin-slot-label,
    html[data-bs-theme="dark"] .knockout-admin-handle,
    html[data-bs-theme="dark"] .knockout-admin-kicker,
    html[data-bs-theme="dark"] .knockout-admin-meta {
        color: var(--lap-admin-text-muted);
    }

    html[data-bs-theme="dark"] .knockout-admin-result-chip,
    html[data-bs-theme="dark"] .knockout-admin-team.is-winner {
        background: rgba(34, 197, 94, 0.16);
        color: #86efac;
    }

    html[data-bs-theme="dark"] .knockout-admin-score {
        color: var(--lap-admin-text-strong);
    }

    html[data-bs-theme="dark"] .knockout-admin-empty {
        background: rgba(148, 163, 184, 0.10);
        color: var(--lap-admin-text-soft);
    }

    html[data-bs-theme="dark"] [data-knockout-board-root].is-dragging .knockout-admin-empty {
        background: rgba(var(--bs-primary-rgb), 0.12);
    }

    html[data-bs-theme="dark"] [data-knockout-board-root].is-dragging .knockout-admin-empty-link {
        color: #bfdbfe;
    }

    @media (max-width: 991.98px) {
        .knockout-admin-slot.has-connector::after,
        .knockout-admin-slot.has-connector::before {
            display: none;
        }

        .knockout-admin-round-tabs {
            display: flex;
        }

        .knockout-admin-board-wrap {
            display: block;
            overflow: visible;
        }

        .knockout-admin-round {
            display: none;
            min-width: 100%;
        }

        .knockout-admin-round.is-active {
            display: flex;
        }
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Kompetisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pageHeading }}</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ $pageHeading }}</h4>
        <p class="text-muted mb-0">{{ $pageDescription }}</p>
    </div>
</div>

@if ($ageGroupSummaries->isNotEmpty())
    <div class="card mb-4">
        <div class="card-body">
            <div class="small text-uppercase fw-semibold text-muted mb-3">Kelompok Usia</div>
            <div class="knockout-admin-age-switcher" aria-label="Pilih kelompok usia knockout">
                @foreach ($ageGroupSummaries as $ageGroup)
                    <a
                        href="{{ route($indexRouteName, ['age_group_id' => $ageGroup['id']]) }}"
                        class="knockout-admin-age-pill {{ (string) $selectedAgeGroupId === (string) $ageGroup['id'] ? 'is-active' : '' }}"
                    >
                        <span class="knockout-admin-age-pill-name">{{ $ageGroup['name'] }}</span>
                        <span class="knockout-admin-age-pill-count">{{ $ageGroup['total_matches'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

@include('competition.partials.flash')

<div class="alert alert-danger knockout-admin-feedback mb-4" data-knockout-board-feedback role="alert"></div>

@if ($selectedBoard)
    <div class="card" data-knockout-round-board>
        <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="card-title mb-1">Bracket {{ $selectedBoard['age_group']->name }}</h4>
                <p class="text-muted mb-0">Fokus pada satu bracket aktif supaya susunan babak dan slot lebih cepat dipindai.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary">{{ $selectedBoard['total_matches'] }} match</span>
                <span class="badge bg-warning-subtle text-warning">Drag ke slot kosong untuk pindah posisi</span>
            </div>
        </div>
        <div class="card-body">
            <div class="knockout-admin-round-tabs" aria-label="Pilih babak di mobile">
                @foreach ($selectedBoard['rounds'] as $round)
                    <button
                        type="button"
                        class="knockout-admin-round-tab {{ $loop->first ? 'is-active' : '' }}"
                        data-knockout-round-tab
                        data-round-order="{{ $round['round_order'] }}"
                    >
                        <span class="d-block small text-uppercase text-muted fw-semibold mb-1">Babak {{ $round['round_order'] }}</span>
                        <span class="fw-semibold">{{ $round['round_display_label'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="knockout-admin-board-wrap knockout-admin-board-root" data-knockout-board-root data-csrf-token="{{ csrf_token() }}">
                @foreach ($selectedBoard['rounds'] as $round)
                    <section class="knockout-admin-round {{ $loop->first ? 'is-active' : '' }}" data-knockout-round-panel data-round-order="{{ $round['round_order'] }}">
                        <div class="knockout-admin-round-header">
                            <div class="small text-uppercase fw-semibold text-muted mb-2">Babak {{ $round['round_order'] }}</div>
                            <h5 class="mb-0">{{ $round['round_display_label'] }}</h5>
                            @if ($round['is_new_round'])
                                <p class="text-muted small mb-0 mt-2">Nama babak bisa Anda isi bebas saat membuat match pertama di kolom ini.</p>
                            @endif
                        </div>

                        @foreach ($round['slots'] as $slot)
                            @php
                                $match = $slot['match'];
                                $clubAIsWinner = $match && $match->winner_club_id === (int) $match->club_a_id;
                                $clubBIsWinner = $match && $match->winner_club_id === (int) $match->club_b_id;
                            @endphp
                            <div class="knockout-admin-slot {{ $match ? 'is-occupied' : '' }} {{ $slot['has_connector'] ? 'has-connector' : '' }} slot-tone-{{ $slot['connector_tone'] }}" data-slot-wrapper>
                                <div class="knockout-admin-slot-head">
                                    <span class="knockout-admin-slot-label">Slot {{ $slot['slot'] }}</span>
                                    @if ($match)
                                        <span class="badge {{ $match->is_finished ? 'lap-admin-chip lap-admin-chip-approved' : 'lap-admin-chip lap-admin-chip-draft' }}">
                                            {{ $match->is_finished ? 'Selesai' : 'Belum selesai' }}
                                        </span>
                                    @endif
                                </div>

                                <div
                                    class="knockout-admin-dropzone"
                                    data-knockout-dropzone
                                    data-age-group-id="{{ $selectedBoard['age_group']->id }}"
                                    data-round-label="{{ $round['round_label'] }}"
                                    data-round-order="{{ $round['round_order'] }}"
                                    data-bracket-slot="{{ $slot['slot'] }}"
                                >
                                    @if ($match)
                                        <article
                                            class="knockout-admin-match {{ $match->has_winner ? 'is-decided' : '' }}"
                                            data-knockout-card
                                            data-match-id="{{ $match->id }}"
                                            data-age-group-id="{{ $selectedBoard['age_group']->id }}"
                                            @unless($isHistoryView)
                                                data-update-url="{{ route('matches.knockout.position', $match) }}"
                                            @endunless
                                            data-slot-tone="{{ $slot['connector_tone'] === 'empty' ? 'occupied' : $slot['connector_tone'] }}"
                                        >
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <button type="button" class="knockout-admin-handle" data-knockout-drag-handle aria-label="Geser posisi match">
                                                    <i data-lucide="grip-vertical" class="fs-18"></i>
                                                </button>
                                                <div class="d-flex flex-wrap justify-content-end gap-1">
                                                    @if (($match->lineup_lists_count ?? 0) > 0)
                                                        <span class="badge bg-primary-subtle text-primary">DSP {{ $match->lineup_lists_count }}</span>
                                                    @endif
                                                    <span class="badge bg-light text-dark border">{{ $match->score_label }}</span>
                                                </div>
                                            </div>

                                            <div class="knockout-admin-kicker">{{ $match->match_day }}</div>

                                            @if ($match->winner_club_name)
                                                <div class="knockout-admin-result-chip">
                                                    <i data-lucide="badge-check" class="fs-14"></i>
                                                    <span>Pemenang: {{ $match->winner_club_name }}</span>
                                                </div>
                                            @endif

                                            @if ($match->source_match_a_label || $match->source_match_b_label)
                                                <div class="knockout-admin-source-pills">
                                                    @if ($match->source_match_a_label)
                                                        <span class="knockout-admin-source-pill">{{ $match->source_match_a_label }}</span>
                                                    @endif
                                                    @if ($match->source_match_b_label)
                                                        <span class="knockout-admin-source-pill">{{ $match->source_match_b_label }}</span>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="knockout-admin-teams">
                                                <div class="knockout-admin-team {{ $clubAIsWinner ? 'is-winner' : ($match->has_winner ? 'is-loser' : '') }}">
                                                    <span>{{ $match->clubA?->name ?: 'Menunggu' }}</span>
                                                    <span class="knockout-admin-score">{{ $match->score_club_a ?? '-' }}</span>
                                                </div>
                                                <div class="knockout-admin-team {{ $clubBIsWinner ? 'is-winner' : ($match->has_winner ? 'is-loser' : '') }}">
                                                    <span>{{ $match->clubB?->name ?: 'Menunggu' }}</span>
                                                    <span class="knockout-admin-score">{{ $match->score_club_b ?? '-' }}</span>
                                                </div>
                                            </div>

                                            <div class="knockout-admin-meta">
                                                <span>{{ optional($match->match_date)->format('d M Y') ?: '-' }}</span>
                                                <span>{{ optional($match->kickoff_time)->format('H:i') ?: '-' }} WIB</span>
                                                <span>{{ $match->venue }}</span>
                                            </div>

                                            @unless ($isHistoryView)
                                                <div class="knockout-admin-actions">
                                                    <a href="{{ route('matches.edit', ['match' => $match, 'redirect_route' => $indexRouteName]) }}" class="btn btn-sm btn-light">Edit</a>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger js-delete-match"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteMatchModal"
                                                        data-action="{{ route('matches.destroy', ['match' => $match, 'redirect_route' => $indexRouteName]) }}"
                                                        data-name="{{ ($match->clubA?->name ?: 'Klub A').' vs '.($match->clubB?->name ?: 'Klub B') }}"
                                                    >
                                                        Hapus
                                                    </button>
                                                </div>
                                            @endunless
                                        </article>
                                    @endif
                                    <div class="knockout-admin-empty {{ $match ? 'd-none' : '' }}" data-knockout-empty-action>
                                        @unless ($isHistoryView)
                                            <a href="{{ $slot['create_url'] }}" class="btn btn-outline-primary knockout-admin-empty-link">
                                                <i data-lucide="plus-circle" class="fs-18"></i>
                                                <span>Buat match di slot ini</span>
                                            </a>
                                        @else
                                            <div class="small text-muted text-center px-3 py-4">Slot histori ini read-only.</div>
                                        @endunless
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body py-5 text-center text-muted">Tidak ada kelompok usia aktif untuk menyusun bracket knockout.</div>
    </div>
@endif

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteMatchModal',
    'title' => 'Hapus Jadwal Pertandingan',
    'formId' => 'delete-match-form',
    'nameClass' => 'js-delete-match-name',
    'messagePrefix' => 'Jadwal',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
    document.querySelectorAll('[data-knockout-round-board]').forEach(function (board) {
        const tabs = Array.from(board.querySelectorAll('[data-knockout-round-tab]'));
        const panels = Array.from(board.querySelectorAll('[data-knockout-round-panel]'));

        if (!tabs.length || !panels.length) {
            return;
        }

        const activateRound = (roundOrder) => {
            tabs.forEach((tab) => {
                tab.classList.toggle('is-active', tab.dataset.roundOrder === roundOrder);
            });

            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.roundOrder === roundOrder);
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activateRound(tab.dataset.roundOrder));
        });

        activateRound(tabs[0].dataset.roundOrder);
    });

    const boardRoot = document.querySelector('[data-knockout-board-root]');
    if (!boardRoot) {
        return;
    }

    const feedback = document.querySelector('[data-knockout-board-feedback]');
    const csrfToken = boardRoot.dataset.csrfToken;
    const dropzones = Array.from(document.querySelectorAll('[data-knockout-dropzone]'));
    const setBoardDraggingState = (isDragging) => {
        boardRoot.classList.toggle('is-dragging', isDragging);
    };

    const showFeedback = (message, tone = 'danger') => {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.className = `alert alert-${tone} knockout-admin-feedback is-visible mb-4`;
    };

    const clearFeedback = () => {
        if (!feedback) {
            return;
        }

        feedback.textContent = '';
        feedback.className = 'alert alert-danger knockout-admin-feedback mb-4';
    };

    const syncSlotState = (dropzone) => {
        const slotWrapper = dropzone.closest('[data-slot-wrapper]');
        if (!slotWrapper) {
            return;
        }

        const emptyAction = slotWrapper.querySelector('[data-knockout-empty-action]');
        const card = dropzone.querySelector('[data-knockout-card]');
        const hasCard = Boolean(card);
        const slotTone = card?.dataset.slotTone || 'empty';

        slotWrapper.classList.toggle('is-occupied', hasCard);
        slotWrapper.classList.remove('slot-tone-empty', 'slot-tone-occupied', 'slot-tone-winner');
        slotWrapper.classList.add(`slot-tone-${slotTone}`);
        if (emptyAction) {
            emptyAction.classList.toggle('d-none', hasCard);
        }
    };

    const markSlotSaved = (dropzone) => {
        const slotWrapper = dropzone.closest('[data-slot-wrapper]');
        if (!slotWrapper) {
            return;
        }

        slotWrapper.classList.remove('is-saved');
        void slotWrapper.offsetWidth;
        slotWrapper.classList.add('is-saved');
        window.setTimeout(() => slotWrapper.classList.remove('is-saved'), 900);
    };

    dropzones.forEach(syncSlotState);

    if (@json($isHistoryView)) {
        return;
    }

    if (typeof window.dragula !== 'function') {
        if (typeof window.ensureDragula !== 'function') {
            return;
        }

        try {
            await window.ensureDragula();
        } catch (_error) {
            showFeedback('Fitur susun bracket belum bisa dimuat. Coba refresh halaman.', 'warning');
            return;
        }
    }

    const drake = window.dragula(dropzones, {
        revertOnSpill: true,
        direction: 'vertical',
        moves: function (_el, _container, handle) {
            if (!handle) {
                return false;
            }

            if (handle.closest('.knockout-admin-actions, a, button.btn, [data-bs-toggle="modal"]')) {
                return false;
            }

            return Boolean(handle.closest('[data-knockout-card]'));
        },
        accepts: function (el, target) {
            if (!target) {
                return false;
            }

            const targetAgeGroupId = target.dataset.ageGroupId;
            const cardAgeGroupId = el.dataset.ageGroupId;
            const existingCards = Array.from(target.querySelectorAll('[data-knockout-card]')).filter((card) => card !== el);

            return existingCards.length <= 1 && targetAgeGroupId === cardAgeGroupId;
        },
    });

    drake.on('drag', function () {
        clearFeedback();
        setBoardDraggingState(true);
    });

    drake.on('over', function (_el, container) {
        container.closest('[data-slot-wrapper]')?.classList.add('is-over');
    });

    drake.on('out', function (_el, container) {
        container.closest('[data-slot-wrapper]')?.classList.remove('is-over');
    });

    drake.on('drop', function (el, target, source) {
        source?.closest('[data-slot-wrapper]')?.classList.remove('is-over');
        target?.closest('[data-slot-wrapper]')?.classList.remove('is-over');

        if (!target || !source || target === source) {
            syncSlotState(source || target);
            syncSlotState(target || source);
            return;
        }

        const updateUrl = el.dataset.updateUrl;
        if (!updateUrl) {
            source.appendChild(el);
            syncSlotState(source);
            syncSlotState(target);
            showFeedback('Posisi bracket tidak bisa disimpan karena endpoint update tidak ditemukan.');
            return;
        }

        const sourcePosition = {
            round_label: source.dataset.roundLabel,
            round_order: Number(source.dataset.roundOrder),
            bracket_slot: Number(source.dataset.bracketSlot),
        };
        const targetExistingCard = Array.from(target.querySelectorAll('[data-knockout-card]')).find((card) => card !== el) || null;
        const isSwap = Boolean(targetExistingCard);

        if (isSwap && targetExistingCard) {
            source.appendChild(targetExistingCard);
        }

        syncSlotState(source);
        syncSlotState(target);

        const payload = {
            age_group_id: Number(target.dataset.ageGroupId),
            round_label: target.dataset.roundLabel,
            round_order: Number(target.dataset.roundOrder),
            bracket_slot: Number(target.dataset.bracketSlot),
        };

        if (isSwap && targetExistingCard) {
            payload.swap_match_id = Number(targetExistingCard.dataset.matchId);
            payload.source_round_label = sourcePosition.round_label;
            payload.source_round_order = sourcePosition.round_order;
            payload.source_bracket_slot = sourcePosition.bracket_slot;
        }

        const revertDrop = () => {
            if (isSwap && targetExistingCard && source.contains(targetExistingCard)) {
                target.appendChild(targetExistingCard);
            }

            source.appendChild(el);
            syncSlotState(source);
            syncSlotState(target);
        };

        fetch(updateUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                if (response.ok) {
                    showFeedback(isSwap ? 'Posisi bracket berhasil ditukar.' : 'Posisi bracket berhasil diperbarui.', 'success');
                    markSlotSaved(source);
                    markSlotSaved(target);
                    window.setTimeout(clearFeedback, 1800);
                    return;
                }

                const body = await response.json().catch(() => ({}));
                const message = body?.message || Object.values(body?.errors || {}).flat()[0] || 'Posisi bracket gagal diperbarui.';
                throw new Error(message);
            })
            .catch((error) => {
                revertDrop();
                showFeedback(error.message || 'Posisi bracket gagal diperbarui.');
            });
    });

    drake.on('cancel', function (_el, container, source) {
        syncSlotState(container || source);
        syncSlotState(source || container);
    });

    drake.on('dragend', function () {
        setBoardDraggingState(false);
    });
});
</script>
@endpush

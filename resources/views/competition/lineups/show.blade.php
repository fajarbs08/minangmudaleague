@extends('layouts.vertical', ['title' => $title])

@php
    $players = $starters->concat($substitutes)->values();
    $match = $lineupList->match;
    $opponent = $lineupList->opponent();
    $matchDay = $lineupList->match_day ?: $match?->match_day;
    $matchDate = $lineupList->match_date ?: $match?->match_date;
    $kickoffTime = $lineupList->played_time ?: $match?->kickoff_time;
    $venue = $lineupList->played_at ?: $match?->venue;
    $officialEntries = $officials->map(function ($official) use ($lineupList) {
        $registration = $official->registrationForAgeGroup($lineupList->age_group_id);
        $role = $registration?->role ?: $official->role;

        return trim($official->name.($role ? ' - '.$role : ''));
    })->values();
    $rosterRows = max($players->count(), 1);
    $officialRows = max($officialEntries->count(), 1);
    $isAdmin = auth()->user()->isAdmin();
@endphp

@section('content')
@php
    $isHistoryView = app(\App\Services\SeasonContext::class)->isViewingHistory();
@endphp
<style>
    .dsp-page {
        background: #fff;
    }

    .dsp-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .dsp-sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        color: #111;
        padding: 12mm 10mm;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
    }

    .dsp-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .dsp-table td,
    .dsp-table th {
        border: 1px solid #222;
        padding: 4px 6px;
        font-size: 12px;
        line-height: 1.15;
        vertical-align: middle;
    }

    .dsp-table th {
        font-weight: 700;
        text-transform: uppercase;
    }

    .dsp-center {
        text-align: center;
    }

    .dsp-right {
        text-align: right;
    }

    .dsp-bold {
        font-weight: 700;
    }

    .dsp-logo-box {
        height: 116px;
        text-align: center;
    }

    .dsp-logo-box img {
        max-width: 100%;
        max-height: 88px;
        object-fit: contain;
        display: block;
        margin: 0 auto 8px;
    }

    .dsp-head-title {
        font-size: 18px;
        font-weight: 800;
        text-transform: uppercase;
        line-height: 1.15;
    }

    .dsp-subtitle {
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .dsp-meta td:first-child {
        width: 20%;
        font-weight: 700;
        text-transform: uppercase;
    }

    .dsp-meta td:nth-child(2),
    .dsp-meta td:nth-child(4),
    .dsp-meta td:nth-child(6) {
        width: 2%;
    }

    .dsp-meta td:nth-child(3) {
        width: 29%;
    }

    .dsp-meta td:nth-child(5),
    .dsp-meta td:nth-child(7) {
        width: 23.5%;
    }

    .dsp-roster th:nth-child(1) { width: 8%; }
    .dsp-roster th:nth-child(2) { width: 48%; text-align: left; }
    .dsp-roster th:nth-child(3) { width: 8%; }
    .dsp-roster th:nth-child(4) { width: 20%; text-align: left; }
    .dsp-roster th:nth-child(5),
    .dsp-roster th:nth-child(6) { width: 8%; }

    .dsp-roster td {
        height: 22px;
        padding-top: 2px;
        padding-bottom: 2px;
    }

    .dsp-officials th:nth-child(1) { width: 8%; }
    .dsp-officials th:nth-child(2) { width: 92%; text-align: left; }

    .dsp-note {
        margin-top: 12px;
        font-size: 11px;
        color: #475569;
    }

    @media (max-width: 575.98px) {
        .page-content {
            padding: 0 !important;
        }

        .dsp-page {
            padding: 0;
        }

        .dsp-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
            margin-bottom: 0;
            padding: 1rem 1rem 0.75rem;
        }

        .dsp-toolbar .d-flex.gap-2 {
            display: grid !important;
            grid-template-columns: 1fr;
        }

        .dsp-toolbar .btn {
            width: 100%;
        }

        .dsp-print-card {
            margin-bottom: 0;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .dsp-sheet {
            width: 100%;
            min-height: auto;
            padding: 0.75rem;
            box-shadow: none;
        }

        .dsp-table td,
        .dsp-table th {
            padding: 3px 4px;
            font-size: 10px;
        }

        .dsp-logo-box {
            height: 78px;
        }

        .dsp-logo-box img {
            max-height: 58px;
            margin-bottom: 4px;
        }

        .dsp-head-title {
            font-size: 12px;
        }

        .dsp-subtitle {
            font-size: 11px;
        }

        .dsp-roster td {
            height: auto;
        }

        .dsp-note {
            font-size: 10px;
        }
    }

    @media print {
        .topbar, .main-nav, .footer, .btn, .alert, .card:not(.dsp-print-card), .workflow-panel {
            display: none !important;
        }

        .content-page, .container-fluid, .page-content {
            padding: 0 !important;
            margin: 0 !important;
        }

        body {
            background: #fff !important;
        }

        .dsp-toolbar {
            display: none !important;
        }

        .dsp-sheet {
            width: 100%;
            min-height: auto;
            padding: 0;
            box-shadow: none;
        }

        @page {
            size: A4;
            margin: 10mm;
        }
    }
</style>

<div class="dsp-page">
    <div class="dsp-toolbar">
        <div>
            <h4 class="mb-1">Lembar DSP</h4>
            <p class="text-muted mb-0">{{ $lineupList->title }}</p>
        </div>
        <div class="d-flex gap-2">
            @if (! $isHistoryView && ($isAdmin || $lineupList->canBeEditedByClub()))
                <a href="{{ route('lineup-lists.edit', $lineupList) }}" class="btn btn-light">{{ $isAdmin ? 'Edit oleh Admin' : 'Edit DSP' }}</a>
            @endif
            <button type="button" class="btn btn-primary" onclick="window.print()">Cetak DSP</button>
        </div>
    </div>

    <div class="card dsp-print-card border-0 bg-transparent">
        <div class="card-body p-0">
            <div class="dsp-sheet">
                <table class="dsp-table mb-3">
                    <tr>
                        <td rowspan="2" style="width: 22%;" class="dsp-logo-box">
                            <img src="{{ asset('images/logo-dark.png') }}" alt="Logo">
                        </td>
                        <td style="width: 56%;" class="dsp-center">
                            <div class="dsp-head-title">
                                {{ strtoupper($lineupList->club?->name ?: 'Liga Anak Piaman Laweh') }}
                                {{ $lineupList->ageGroup?->name ? ' '.$lineupList->ageGroup->name : '' }}
                            </div>
                            @if ($matchDay)
                                <div class="dsp-head-title">{{ strtoupper($matchDay) }}</div>
                            @endif
                            <div class="dsp-head-title">MUSIM {{ optional($matchDate)->format('Y') ?: now()->format('Y') }}</div>
                        </td>
                        <td rowspan="2" style="width: 22%;" class="dsp-logo-box">
                            <img src="{{ asset('images/logo-dark.png') }}" alt="Logo">
                        </td>
                    </tr>
                    <tr>
                        <td class="dsp-center">
                            <div class="dsp-subtitle">DAFTAR SUSUNAN PEMAIN</div>
                        </td>
                    </tr>
                </table>

                <table class="dsp-table dsp-meta mb-3">
                    <tr>
                        <td>TIM</td>
                        <td>:</td>
                        <td colspan="5">{{ strtoupper($lineupList->club?->name ?: '-') }}</td>
                    </tr>
                    <tr>
                        <td>LAWAN</td>
                        <td>:</td>
                        <td colspan="5">{{ strtoupper($opponent?->name ?: '-') }}</td>
                    </tr>
                    <tr>
                        <td>WARNA JERSEY</td>
                        <td>:</td>
                        <td colspan="5">{{ $lineupList->jersey_color ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td>WARNA JERSEY KIPER</td>
                        <td>:</td>
                        <td colspan="5">{{ $lineupList->goalkeeper_jersey_color ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td>LOKASI</td>
                        <td>:</td>
                        <td>{{ $venue ?: '-' }}</td>
                        <td class="dsp-bold">TANGGAL</td>
                        <td>: {{ optional($matchDate)->format('d-m-Y') ?: '-' }}</td>
                        <td class="dsp-bold">JAM</td>
                        <td>: {{ optional($kickoffTime)->format('H:i') ?: '-' }} WIB</td>
                    </tr>
                </table>

                <table class="dsp-table dsp-roster mb-3">
                    <thead>
                        <tr>
                            <th class="dsp-center">NO</th>
                            <th>NAMA PEMAIN</th>
                            <th class="dsp-center">NO JERSEY</th>
                            <th>POSISI</th>
                            <th class="dsp-center">P</th>
                            <th class="dsp-center">S</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < $rosterRows; $i++)
                            @php $player = $players[$i] ?? null; @endphp
                            <tr>
                                <td class="dsp-center">{{ $i + 1 }}</td>
                                <td>{{ $player?->name ?: '' }}</td>
                                <td class="dsp-center">
                                    {{ $player ? ($player->pivot->jersey_number ?: ($player->displayJerseyNumber($lineupList->age_group_id) ?: '')) : '' }}
                                </td>
                                <td>{{ $player ? ($player->displayPosition($lineupList->age_group_id) ?: '') : '' }}</td>
                                <td class="dsp-center">{{ $player && $player->pivot->role === \App\Models\LineupList::ROLE_STARTER ? 'P' : '' }}</td>
                                <td class="dsp-center">{{ $player && $player->pivot->role === \App\Models\LineupList::ROLE_SUBSTITUTE ? 'S' : '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <table class="dsp-table dsp-officials">
                    <thead>
                        <tr>
                            <th class="dsp-center">NO</th>
                            <th>NAMA OFFICIAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < $officialRows; $i++)
                            <tr>
                                <td class="dsp-center">{{ $i + 1 }}</td>
                                <td>{{ $officialEntries[$i] ?? '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <div class="dsp-note">
                    Keterangan: P = Pemain Inti (starter), S = Pemain Cadangan.
                    @if ($lineupList->notes)
                        <br>Catatan: {{ $lineupList->notes }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if ($starters->count() !== \App\Models\LineupList::REQUIRED_STARTERS || $substitutes->count() > \App\Models\LineupList::MAX_SUBSTITUTES)
    <div class="alert alert-warning mt-4">
        Struktur DSP belum ideal. Starter harus {{ \App\Models\LineupList::REQUIRED_STARTERS }} pemain dan cadangan maksimal {{ \App\Models\LineupList::MAX_SUBSTITUTES }} pemain.
    </div>
@endif

@unless ($isHistoryView)
    @include('competition.partials.workflow-panel', [
        'item' => $lineupList,
        'submitRoute' => route('lineup-lists.submit', $lineupList),
        'reviewRoute' => route('lineup-lists.review', $lineupList),
    ])
@endunless
@endsection

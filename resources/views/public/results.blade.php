@extends('public.layout')

@section('content')
    <div class="rts-match-recap-section section-gap">
        <div class="container">
            @if ($featuredResult)
                <div class="section-inner">
                    <div class="recap-picture"><img src="{{ asset('kester-assets/images/details/recap.jpg') }}" alt="recap-picture"></div>
                    <div class="contents">
                        <h1 class="section-title">{{ strtoupper($featuredResult->clubA?->short_name ?: $featuredResult->clubA?->name ?: 'TBD') }} <span>VS</span> {{ strtoupper($featuredResult->clubB?->short_name ?: $featuredResult->clubB?->name ?: 'TBD') }}</h1>
                        <p class="p1">{{ $featuredResult->result_summary }} · {{ $featuredResult->ageGroup?->name ?: '-' }} · {{ $featuredResult->venue ?: 'Venue belum diisi' }}</p>
                        <div class="match-result">
                            <div class="header">
                                <div class="date">{{ optional($featuredResult->match_date)->translatedFormat('d F Y') }} ({{ optional($featuredResult->kickoff_time)->format('H:i') }} WIB)</div>
                                <div class="full-time">(WAKTU PENUH)</div>
                            </div>
                            <div class="scoreboard">
                                <div class="team-logo">
                                    <div class="logo"><img src="{{ $featuredResult->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-3.svg') }}" alt=""></div>
                                    <span>VS</span>
                                    <div class="logo"><img src="{{ $featuredResult->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-4.svg') }}" alt=""></div>
                                </div>
                                <h3 class="score">{{ $featuredResult->score_label }}</h3>
                            </div>
                        </div>
                        <a href="{{ route('public.schedule') }}" class="highlight-btn">LIHAT JADWAL <i class="far fa-long-arrow-right"></i></a>
                    </div>
                </div>
            @else
                <div class="lap-summary-card">
                    <h3 class="section-title mb--20">Hasil belum tersedia</h3>
                    <p class="lap-copy mb-0">Belum ada hasil pertandingan yang tersedia saat ini.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="rts-match-results-section section-gap pt-0">
        <div class="container">
            <div class="section-title-area section-title-area-inner mb--50">
                <h1 class="section-title">HASIL PERTANDINGAN</h1>
            </div>
            <div class="row g-4">
                @foreach ($recentResults as $match)
                    <div class="col-xl-6">
                        <div class="lap-result-report">
                            <h4 class="mb--10">{{ ($match->clubA?->short_name ?: $match->clubA?->name ?: 'TBD') }} {{ $match->score_label }} {{ ($match->clubB?->short_name ?: $match->clubB?->name ?: 'TBD') }}</h4>
                            <p class="lap-copy mb--15">{{ optional($match->match_date)->translatedFormat('d F Y') }} · {{ $match->ageGroup?->name ?: '-' }} · {{ $match->venue ?: 'Venue belum diisi' }}</p>
                            <div class="table-area table-full">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr class="head-tr">
                                            <th>KLUB</th>
                                            <th>GOL</th>
                                            <th>STATUS</th>
                                        </tr>
                                        <tr>
                                            <td><span class="td name long-text">{{ $match->clubA?->name ?: 'TBD' }}</span></td>
                                            <td class="compact-td"><span class="td">{{ $match->score_club_a }}</span></td>
                                            <td class="compact-td"><span class="td pts-count">{{ $match->club_a_id && $match->score_club_a > $match->score_club_b ? 'MENANG' : ($match->score_club_a === $match->score_club_b ? 'IMBANG' : 'KALAH') }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><span class="td name long-text">{{ $match->clubB?->name ?: 'TBD' }}</span></td>
                                            <td class="compact-td"><span class="td">{{ $match->score_club_b }}</span></td>
                                            <td class="compact-td"><span class="td pts-count">{{ $match->club_b_id && $match->score_club_b > $match->score_club_a ? 'MENANG' : ($match->score_club_a === $match->score_club_b ? 'IMBANG' : 'KALAH') }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @if ($match->goalEvents->isNotEmpty())
                                <ul class="mt--20">
                                    @foreach ([$match->clubA, $match->clubB] as $club)
                                        @php
                                            $goalReport = $match->goalReportForClub($club?->id);
                                        @endphp
                                        @if ($club && !empty($goalReport))
                                            <li><strong>{{ $club->short_name ?: $club->name }}:</strong> {{ implode(', ', $goalReport) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

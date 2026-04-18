@extends('public.layout')

@push('styles')
    <style>
        .lap-public .rts-match-schedule-section .match-single .match-single-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 32px;
            width: 100%;
            padding: 26px 30px;
            text-align: left;
        }

        .lap-public .rts-match-schedule-section .match-single .match-scores {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) auto minmax(220px, 1fr);
            align-items: center;
            gap: 20px;
            margin-bottom: 0;
            width: 100%;
        }

        .lap-public .rts-match-schedule-section .match-single .club {
            width: 100%;
        }

        .lap-public .rts-match-schedule-section .match-single .club.club1 .club-logo {
            justify-content: flex-end;
            text-align: right;
        }

        .lap-public .rts-match-schedule-section .match-single .club.club2 .club-logo {
            justify-content: flex-start;
            text-align: left;
        }

        .lap-public .rts-match-schedule-section .match-single .club .club-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            flex: 0 0 70px;
        }

        .lap-public .rts-match-schedule-section .match-single .club .club-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 0;
            width: 100%;
        }

        .lap-public .rts-match-schedule-section .match-single .club .club-logo .club-name {
            margin: 0;
            max-width: 220px;
            font-size: 22px;
            line-height: 1.15;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .lap-public .rts-match-schedule-section .match-single.sm .club .club-logo .club-name {
            font-size: 18px;
        }

        .lap-public .rts-match-schedule-section .match-single .colon {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .lap-public .rts-match-schedule-section .match-single .block-wrap {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            min-width: 320px;
            text-align: left;
        }

        .lap-public .rts-match-schedule-section .match-single .block-wrap .match-date {
            margin: 0;
            white-space: nowrap;
        }

        .lap-public .rts-match-schedule-section .match-single .block-wrap .stadium-name {
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .lap-public .rts-match-schedule-section .match-single .match-single-content {
                grid-template-columns: 1fr;
                gap: 16px;
                padding: 22px 20px;
                text-align: center;
            }

            .lap-public .rts-match-schedule-section .match-single .match-scores {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .lap-public .rts-match-schedule-section .match-single .club.club1 .club-logo,
            .lap-public .rts-match-schedule-section .match-single .club.club2 .club-logo {
                justify-content: center;
                text-align: center;
            }

            .lap-public .rts-match-schedule-section .match-single .club .club-logo img {
                width: 56px;
                height: 56px;
                flex-basis: 56px;
            }

            .lap-public .rts-match-schedule-section .match-single .club .club-logo {
                gap: 10px;
            }

            .lap-public .rts-match-schedule-section .match-single .club .club-logo .club-name,
            .lap-public .rts-match-schedule-section .match-single.sm .club .club-logo .club-name {
                max-width: none;
                font-size: 18px;
            }

            .lap-public .rts-match-schedule-section .match-single .block-wrap {
                flex-direction: column;
                justify-content: center;
                min-width: 0;
                gap: 8px;
                text-align: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="rts-match-result-section rts-match-result-section2 rts-match-schedule-section inner section-gap">
        <div class="container">
            <div class="row justify-content-center">
                @forelse ($upcomingMatches as $match)
                    <div class="col-12">
                        <div class="match-single {{ $loop->first ? '' : 'sm' }}">
                            <div class="match-single-content">
                                <div class="match-scores">
                                    <div class="club club1">
                                        <div class="club-logo mr--20">
                                            <span class="club-name">{{ strtoupper($match->clubA?->name ?: $match->clubA?->short_name ?: 'TBD') }}</span>
                                            <img src="{{ $match->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-1.svg') }}" alt="club-logo">
                                        </div>
                                    </div>
                                    <div class="colon">VS</div>
                                    <div class="club club2">
                                        <div class="club-logo ml--20">
                                            <img src="{{ $match->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-2.svg') }}" alt="club-logo">
                                            <span class="club-name">{{ strtoupper($match->clubB?->name ?: $match->clubB?->short_name ?: 'TBD') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="block-wrap">
                                    <span class="match-date">{{ strtoupper(optional($match->match_date)->translatedFormat('d F Y')) }}{{ $match->kickoff_time ? ' · ' . optional($match->kickoff_time)->format('H:i') . ' WIB' : '' }}</span>
                                    <span class="stadium-name">{{ strtoupper($match->venue ?: 'VENUE MENYUSUL') }}</span>
                                </div>
                            </div>
                            @if ($loop->first)
                                <div class="match-bottom-action">
                                    <a href="{{ route('public.standings') }}" class="action-item first-child">{{ strtoupper($match->match_day ?: 'MATCHDAY') }}</a>
                                    <a href="#footer-kontak" class="action-item">KONTAK PANITIA</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="lap-summary-card">
                            <h3 class="section-title mb--20">Jadwal belum tersedia</h3>
                            <p class="lap-copy mb-0">Belum ada jadwal pertandingan yang tersedia saat ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

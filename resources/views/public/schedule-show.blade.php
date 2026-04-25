@extends('public.public-layout')

@php
    $templateAsset = fn (string $path) => asset('public-assets/'.$path);
@endphp

@push('styles')
    <style>
        .lap-schedule-template .lineup-bg-wrapper {
            margin-top: 0;
            padding: 48px 48px 56px;
            background-color: #f8f9fb;
            background-image: linear-gradient(rgba(248, 249, 251, .92), rgba(248, 249, 251, .92)), url('{{ $templateAsset('img/inner/lineup/lineup-bg.jpg') }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: #030523;
        }

        .lap-schedule-template .lineup-bg-wrapper .lineup-content h3,
        .lap-schedule-template .lineup-bg-wrapper .lineup-result-items .thumb h3,
        .lap-schedule-template .lineup-bg-wrapper .lineup-result-items .left-item p,
        .lap-schedule-template .lineup-bg-wrapper .text-item p,
        .lap-schedule-template .lineup-bg-wrapper .lineup-result-items .center-item p {
            color: #030523;
        }

        .lap-schedule-template .lineup-bg-wrapper .lineup-content h3,
        .lap-schedule-template .lineup-bg-wrapper .lineup-result-items .thumb h3 {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-schedule-template .lineup-bg-wrapper .text-item p {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
            opacity: .95;
        }

        .lap-schedule-template .lineup-bg-wrapper .lineup-result-items .left-item p {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            opacity: 1;
        }

        .lap-schedule-template .lineup-bg-wrapper .score-result {
            box-shadow: none;
        }

        .lap-schedule-template .lineup-bg-wrapper .lineup-result-items .score-result {
            color: #ffffff;
        }

        .lap-schedule-template .lineup-bg-wrapper .thumb img {
            filter: none;
        }

        .lap-schedule-template .lineup-bg-wrapper .thumb img,
        .lap-schedule-template .club-lineup-wrapper .club-lineup-head span img,
        .lap-schedule-template .club-lineup-wrapper .match-head-title span img {
            object-fit: contain;
            flex: 0 0 auto;
        }

        .lap-schedule-template .lineup-bg-wrapper .thumb img {
            width: 78px;
            height: 78px;
        }

        .lap-schedule-template .club-lineup-wrapper .club-lineup-head span img,
        .lap-schedule-template .club-lineup-wrapper .match-head-title span img {
            width: 58px;
            height: 58px;
            padding: 4px;
            background: #eef3ff;
            border: 1px solid rgba(11, 36, 84, 0.08);
            border-radius: 14px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .lap-schedule-template .lap-schedule-club-mark,
        .lap-schedule-template .lap-schedule-player-mark {
            align-items: center;
            color: #030523;
            display: inline-flex;
            flex: 0 0 auto;
            font-family: 'Big Shoulders', sans-serif;
            font-weight: 800;
            justify-content: center;
            letter-spacing: .08em;
            line-height: 1;
            text-transform: uppercase;
        }

        .lap-schedule-template .lap-schedule-club-mark.is-hero {
            width: 78px;
            height: 78px;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid rgba(11, 36, 84, 0.08);
            font-size: 24px;
        }

        .lap-schedule-template .lap-schedule-club-mark.is-head {
            width: 58px;
            height: 58px;
            padding: 4px;
            background: #eef3ff;
            border: 1px solid rgba(11, 36, 84, 0.08);
            border-radius: 14px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
            font-size: 16px;
        }

        .lap-schedule-template .lap-schedule-player-mark {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef3ff;
            font-size: 14px;
        }

        .lap-schedule-template .latest-world-ranking-section {
            background: #ffffff;
            padding-top: 80px;
            padding-bottom: 96px;
        }

        .lap-schedule-template .latest-world-ranking-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-schedule-template .latest-world-ranking-section .container {
                max-width: 1680px;
            }
        }

        .lap-schedule-template .club-lineup-wrapper .club-lineup-left .club-lineup-head h3,
        .lap-schedule-template .club-lineup-wrapper .match-information-box .match-head-title h3 {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-schedule-template .club-lineup-wrapper .club-lineup-left .club-lineup-head span,
        .lap-schedule-template .club-lineup-wrapper .match-information-box .match-head-title span,
        .lap-schedule-template .club-lineup-wrapper .club-lineup-left ul li .lineup-item .content p,
        .lap-schedule-template .club-lineup-wrapper .match-information-box ul li .sub-text,
        .lap-schedule-template .schedule-info-card p,
        .lap-schedule-template .schedule-info-card .detail-value {
            color: #5a6274;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-schedule-template .club-lineup-wrapper .club-lineup-left .club-lineup-head span,
        .lap-schedule-template .club-lineup-wrapper .match-information-box .match-head-title span {
            font-size: 15px;
            gap: 10px;
            white-space: nowrap;
        }

        .lap-schedule-template .club-lineup-wrapper .club-lineup-left ul li .lineup-item .thumb img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            flex: 0 0 auto;
        }

        .lap-schedule-template .club-lineup-wrapper .club-lineup-left ul li .lineup-item .content h3,
        .lap-schedule-template .club-lineup-wrapper .match-information-box ul li .color-text-1,
        .lap-schedule-template .club-lineup-wrapper .match-information-box ul li .color-text-2,
        .lap-schedule-template .schedule-info-card h3 {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-schedule-template .club-lineup-wrapper .match-information-box ul li .color-text-1,
        .lap-schedule-template .club-lineup-wrapper .match-information-box ul li .color-text-2 {
            font-size: 32px;
        }

        .lap-schedule-template .club-lineup-wrapper .match-information-box {
            border-left-color: rgba(199, 201, 206, 0.35);
        }

        .lap-schedule-template .lap-result-columns > [class*='col-'] {
            position: relative;
            padding-inline: 20px;
        }

        .lap-schedule-template .lap-result-columns > [class*='col-']:nth-child(2) {
            display: flex;
            justify-content: center;
        }

        .lap-schedule-template .lap-result-columns > [class*='col-']:nth-child(2) > * {
            width: min(100%, 380px);
            margin-inline: auto;
        }

        .lap-schedule-template .lap-result-columns > [class*='col-']:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 0;
            right: -12px;
            width: 1px;
            height: 100%;
            bottom: 0;
            background: rgba(199, 201, 206, 0.35);
        }

        .lap-schedule-template .lap-result-columns > [class*='col-'] > * {
            height: 100%;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }

        .lap-schedule-template .lap-result-columns .club-lineup-left .club-lineup-head,
        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title {
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(199, 201, 206, 0.22);
        }

        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            text-align: center;
            gap: 6px;
            width: 100%;
        }

        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title > span {
            min-width: 0;
            width: auto;
            max-width: clamp(92px, 38%, 136px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            overflow: visible;
            white-space: normal;
            line-height: 1;
            text-align: center;
            padding-inline: 2px;
            padding-top: 6px;
        }

        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title > span:first-child {
            justify-self: end;
        }

        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title > span:last-child {
            justify-self: start;
        }

        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title > span img {
            width: 40px;
            height: 40px;
            padding: 2px;
            border-radius: 10px;
            background: #eef3ff;
            box-shadow: none;
            flex: 0 0 auto;
        }

        .lap-schedule-template .lap-result-columns .match-information-box .match-head-title .club-name {
            display: block;
            max-width: 88px;
            min-width: 0;
            color: #5a6274;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
            white-space: normal;
            overflow-wrap: anywhere;
            line-height: 1.05;
        }

        .lap-schedule-template .lap-result-columns .match-information-box ul li {
            align-items: center;
        }

        .lap-schedule-template .lap-result-columns .match-information-box ul li .sub-text {
            min-width: 132px;
            text-align: center;
        }

        .lap-schedule-template .lap-result-columns .match-information-box ul li .color-text-1,
        .lap-schedule-template .lap-result-columns .match-information-box ul li .color-text-2 {
            min-width: 40px;
            text-align: center;
        }

        .lap-schedule-template .lap-result-columns .club-lineup-left .club-lineup-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }

        .lap-schedule-template .lap-right-lineup ul li,
        .lap-schedule-template .club-lineup-left ul li {
            border-bottom: 1px solid rgba(199, 201, 206, 0.28);
            padding-bottom: 18px;
        }

        .lap-schedule-template .lap-right-lineup ul li:not(:last-child),
        .lap-schedule-template .club-lineup-left ul li:not(:last-child) {
            margin-bottom: 18px;
        }

        .lap-schedule-template .lap-result-columns {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1fr);
            gap: 32px;
            align-items: stretch;
        }

        .lap-schedule-template .lap-result-columns > [class*='col-'] {
            max-width: none;
            width: auto;
        }

        .lap-schedule-template .lap-result-columns .club-lineup-left,
        .lap-schedule-template .lap-result-columns .match-information-box {
            height: 100%;
        }

        .lap-schedule-template .schedule-info-card {
            border: 1px solid rgba(199, 201, 206, 0.35);
            border-radius: 24px;
            padding: 28px;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
        }

        .lap-schedule-template .schedule-info-card h3 {
            font-size: 28px;
            margin-bottom: 16px;
        }

        .lap-schedule-template .schedule-info-list {
            display: grid;
            gap: 14px;
        }

        .lap-schedule-template .schedule-info-list .schedule-info-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(199, 201, 206, 0.28);
        }

        .lap-schedule-template .schedule-info-list .schedule-info-row .detail-value {
            color: #030523;
            text-align: right;
            overflow-wrap: anywhere;
        }

        @media (max-width: 991px) {
            .lap-schedule-template .lineup-bg-wrapper {
                padding: 32px 24px 40px;
            }

            .lap-schedule-template .latest-world-ranking-section {
                padding-top: 56px;
                padding-bottom: 64px;
            }

            .lap-schedule-template .lap-result-columns {
                display: block;
            }

            .lap-schedule-template .lap-result-columns > [class*='col-'] {
                max-width: 100%;
                width: 100%;
                padding-inline: 0;
                margin-bottom: 28px;
            }

            .lap-schedule-template .lap-result-columns > [class*='col-']:not(:last-child)::after {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .lap-schedule-template .latest-world-ranking-section .container {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="lap-schedule-template">
        @php
            $clubLogoUrl = function ($club): ?string {
                if (! $club) {
                    return null;
                }

                if (filled($club->logo_file_url)) {
                    return $club->logo_file_url;
                }

                if (filled($club->logo_url)) {
                    return str_starts_with($club->logo_url, 'http')
                        ? $club->logo_url
                        : url('/storage/'.ltrim($club->logo_url, '/'));
                }

                return null;
            };

            $homeClub = $matchSchedule?->clubA;
            $awayClub = $matchSchedule?->clubB;
            $homeClubName = $homeClub?->name ?: $homeClub?->short_name ?: 'Klub A';
            $awayClubName = $awayClub?->name ?: $awayClub?->short_name ?: 'Klub B';
            $homeClubLogo = $clubLogoUrl($homeClub);
            $awayClubLogo = $clubLogoUrl($awayClub);
            $detailStatusLabel = $matchSchedule?->is_finished ? 'Selesai' : 'Terjadwal';
            $statusBadgeClass = $matchSchedule?->is_finished ? 'color-text-1' : 'color-text-2';
            $lineupLists = $matchSchedule?->lineupLists ?? collect();
            $homeLineup = $lineupLists->firstWhere('club_id', $matchSchedule?->club_a_id);
            $awayLineup = $lineupLists->firstWhere('club_id', $matchSchedule?->club_b_id);
            $starterPlayers = fn ($lineup) => collect($lineup?->players ?? [])->filter(fn ($player) => data_get($player, 'pivot.role') === 'starter')->values();
            $homePlayers = $starterPlayers($homeLineup);
            $awayPlayers = $starterPlayers($awayLineup);
            $detailRows = collect([
                ['label' => 'Kategori', 'value' => $matchSchedule?->ageGroup?->name ?: '-'],
                ['label' => 'Format', 'value' => $matchSchedule?->competition_format_label ?: '-'],
                ['label' => 'Tanggal', 'value' => $matchSchedule?->match_date?->translatedFormat('l, d M Y') ?: '-'],
                ['label' => 'Kickoff', 'value' => $matchSchedule?->kickoff_time?->format('H:i').' WIB' ?: '-'],
                ['label' => 'Venue', 'value' => $matchSchedule?->venue ?: '-'],
                ['label' => 'Status', 'value' => $detailStatusLabel],
            ]);
        @endphp

        <div class="lineup-bg-wrapper bg-cover">
            <div class="lineup-content">
                <h3>{{ strtoupper(trim(($matchSchedule?->ageGroup?->name ?? '').' '.($matchSchedule?->competition_format_label ?? 'Detail Jadwal'))) }}</h3>
                <div class="text-item">
                    <p><b>VENUE:</b> {{ $matchSchedule?->venue ?: '-' }}</p>
                    <p><b>TANGGAL:</b> {{ $matchSchedule?->match_date?->translatedFormat('l, d M Y') ?: '-' }}</p>
                </div>
            </div>
            <div class="lineup-result-items">
                <div class="left-item">
                    <div class="thumb">
                        <h3>{{ $homeClubName }}</h3>
                        @include('public.partials.identity-mark', ['imageUrl' => $homeClubLogo, 'label' => $homeClubName, 'badgeClass' => 'lap-schedule-club-mark is-hero'])
                    </div>
                </div>
                <span class="score-result {{ $statusBadgeClass }}">{{ $matchSchedule?->is_finished ? $matchSchedule?->score_label : ($matchSchedule?->kickoff_time?->format('H:i') ?: 'VS') }}</span>
                <div class="left-item">
                    <div class="thumb">
                        @include('public.partials.identity-mark', ['imageUrl' => $awayClubLogo, 'label' => $awayClubName, 'badgeClass' => 'lap-schedule-club-mark is-hero'])
                        <h3>{{ $awayClubName }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <section class="latest-world-ranking-section section-padding">
            <div class="container">
                <div class="club-lineup-wrapper">
                    <div class="row g-4 lap-result-columns">
                        <div class="col-xl-4 col-lg-4 col-md-8">
                            <div class="club-lineup-left">
                                <div class="club-lineup-head">
                                    <h3>LINE UPS</h3>
                                    <span>
                                        @include('public.partials.identity-mark', ['imageUrl' => $homeClubLogo, 'label' => $homeClubName, 'badgeClass' => 'lap-schedule-club-mark is-head'])
                                        {{ $homeClubName }}
                                    </span>
                                </div>
                                <ul>
                                    @forelse ($homePlayers as $player)
                                        @php
                                            $playerName = $player->name ?: 'Pemain belum tersedia';
                                            $playerPosition = $player->displayPosition($matchSchedule?->age_group_id) ?: 'Pemain';
                                            $playerNumber = data_get($player, 'pivot.jersey_number') ?: $player->displayJerseyNumber($matchSchedule?->age_group_id) ?: '--';
                                            $playerPhoto = $player->photo_file_url;
                                        @endphp
                                        <li>
                                            @if ($loop->odd)
                                                <div class="lineup-item">
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-schedule-player-mark'])</div>
                                                    <div class="content">
                                                        <h3>{{ $playerName }}</h3>
                                                        <p>{{ $playerPosition }}</p>
                                                    </div>
                                                </div>
                                                <span class="number">{{ $playerNumber }}</span>
                                            @else
                                                <span class="number">{{ $playerNumber }}</span>
                                                <div class="lineup-item">
                                                    <div class="content text-end">
                                                        <h3>{{ $playerName }}</h3>
                                                        <p>{{ $playerPosition }}</p>
                                                    </div>
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-schedule-player-mark'])</div>
                                                </div>
                                            @endif
                                        </li>
                                    @empty
                                        <li>
                                            <div class="lineup-item">
                                                <div class="content">
                                                    <h3>Belum ada lineup</h3>
                                                    <p>Data starter belum tersedia.</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-8">
                            <div class="match-information-box schedule-info-card">
                                <div class="match-head-title">
                                    <span>
                                        @include('public.partials.identity-mark', ['imageUrl' => $homeClubLogo, 'label' => $homeClubName, 'badgeClass' => 'lap-schedule-club-mark is-head'])
                                        <span class="club-name">{{ $homeClubName }}</span>
                                    </span>
                                    <h3>DETAIL JADWAL</h3>
                                    <span>
                                        @include('public.partials.identity-mark', ['imageUrl' => $awayClubLogo, 'label' => $awayClubName, 'badgeClass' => 'lap-schedule-club-mark is-head'])
                                        <span class="club-name">{{ $awayClubName }}</span>
                                    </span>
                                </div>
                                <div class="schedule-info-list">
                                    @foreach ($detailRows as $row)
                                        <div class="schedule-info-row">
                                            <span class="sub-text">{{ $row['label'] }}</span>
                                            <span class="detail-value">{{ $row['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('public.schedule') }}" class="theme-btn">Kembali ke Jadwal <i class="fa-solid fa-arrow-up-right"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-8">
                            <div class="club-lineup-left lap-right-lineup">
                                <div class="club-lineup-head">
                                    <h3>LINE UPS</h3>
                                    <span>
                                        @include('public.partials.identity-mark', ['imageUrl' => $awayClubLogo, 'label' => $awayClubName, 'badgeClass' => 'lap-schedule-club-mark is-head'])
                                        {{ $awayClubName }}
                                    </span>
                                </div>
                                <ul>
                                    @forelse ($awayPlayers as $player)
                                        @php
                                            $playerName = $player->name ?: 'Pemain belum tersedia';
                                            $playerPosition = $player->displayPosition($matchSchedule?->age_group_id) ?: 'Pemain';
                                            $playerNumber = data_get($player, 'pivot.jersey_number') ?: $player->displayJerseyNumber($matchSchedule?->age_group_id) ?: '--';
                                            $playerPhoto = $player->photo_file_url;
                                        @endphp
                                        <li>
                                            @if ($loop->odd)
                                                <div class="lineup-item">
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-schedule-player-mark'])</div>
                                                    <div class="content">
                                                        <h3>{{ $playerName }}</h3>
                                                        <p>{{ $playerPosition }}</p>
                                                    </div>
                                                </div>
                                                <span class="number">{{ $playerNumber }}</span>
                                            @else
                                                <span class="number">{{ $playerNumber }}</span>
                                                <div class="lineup-item">
                                                    <div class="content text-end">
                                                        <h3>{{ $playerName }}</h3>
                                                        <p>{{ $playerPosition }}</p>
                                                    </div>
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-schedule-player-mark'])</div>
                                                </div>
                                            @endif
                                        </li>
                                    @empty
                                        <li>
                                            <div class="lineup-item">
                                                <div class="content">
                                                    <h3>Belum ada lineup</h3>
                                                    <p>Data starter belum tersedia.</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

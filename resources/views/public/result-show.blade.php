@extends('public.public-layout')

@php
    $templateAsset = fn (string $path) => asset('public-assets/'.$path);
    $publicSeasonQuery = $publicSeasonQuery ?? [];
@endphp

@push('styles')
    <style>
        .lap-result-template .lineup-bg-wrapper {
            margin-top: 0;
            padding: 48px 48px 56px;
            background-color: #f8f9fb;
            background-image: linear-gradient(rgba(248, 249, 251, .92), rgba(248, 249, 251, .92)), url('{{ $templateAsset('img/inner/lineup/lineup-bg.jpg') }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: #030523;
        }

        .lap-result-template .lineup-bg-wrapper .lineup-content h3,
        .lap-result-template .lineup-bg-wrapper .lineup-result-items .thumb h3,
        .lap-result-template .lineup-bg-wrapper .lineup-result-items .left-item p,
        .lap-result-template .lineup-bg-wrapper .text-item p {
            color: #030523;
        }

        .lap-result-template .lineup-bg-wrapper .lineup-content h3,
        .lap-result-template .lineup-bg-wrapper .lineup-result-items .thumb h3 {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-result-template .lineup-bg-wrapper .text-item p {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
            opacity: .95;
        }

        .lap-result-template .lineup-bg-wrapper .lineup-result-items .left-item p {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            opacity: 1;
        }

        .lap-result-template .lineup-bg-wrapper .score-result {
            box-shadow: none;
        }

        .lap-result-template .lineup-bg-wrapper .lineup-result-items .score-result {
            color: #ffffff;
            --lap-match-score-winner: #ffffff;
        }

        .lap-result-template .lineup-bg-wrapper .lineup-result-items .score-result .lap-match-score-value.is-winner {
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.18);
        }

        .lap-result-template .lineup-bg-wrapper .lineup-result-items .score-result .lap-match-score-value.is-loser {
            opacity: .62;
            font-weight: 700;
        }

        .lap-result-template .lineup-bg-wrapper .thumb img {
            filter: none;
        }

        .lap-result-template .lineup-bg-wrapper .thumb img,
        .lap-result-template .club-lineup-wrapper .club-lineup-head span img,
        .lap-result-template .club-lineup-wrapper .match-head-title span img {
            object-fit: cover;
            flex: 0 0 auto;
        }

        .lap-result-template .lineup-bg-wrapper .thumb img {
            width: 78px;
            height: 78px;
            border-radius: 50%;
        }

        .lap-result-template .club-lineup-wrapper .club-lineup-head span img,
        .lap-result-template .club-lineup-wrapper .match-head-title span img {
            width: 58px;
            height: 58px;
            padding: 4px;
            background: #eef3ff;
            border: 1px solid rgba(11, 36, 84, 0.08);
            border-radius: 50%;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .lap-result-template .lap-result-club-mark,
        .lap-result-template .lap-result-player-mark {
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

        .lap-result-template .lap-result-club-mark.is-hero {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid rgba(11, 36, 84, 0.08);
            font-size: 24px;
        }

        .lap-result-template .lap-result-club-mark.is-head {
            width: 58px;
            height: 58px;
            padding: 4px;
            background: #eef3ff;
            border: 1px solid rgba(11, 36, 84, 0.08);
            border-radius: 50%;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
            font-size: 16px;
        }

        .lap-result-template .lap-result-player-mark {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef3ff;
            font-size: 14px;
        }

        .lap-result-template .club-lineup-wrapper .club-lineup-left ul li .lineup-item .thumb img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .lap-result-template .latest-world-ranking-section {
            background: #ffffff;
            padding-top: 80px;
            padding-bottom: 96px;
        }

        .lap-result-template .latest-world-ranking-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-result-template .latest-world-ranking-section .container {
                max-width: 1680px;
            }
        }

        .lap-result-template .club-lineup-wrapper .club-lineup-left .club-lineup-head h3,
        .lap-result-template .club-lineup-wrapper .match-information-box .match-head-title h3 {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-result-template .club-lineup-wrapper .club-lineup-left .club-lineup-head span,
        .lap-result-template .club-lineup-wrapper .match-information-box .match-head-title span,
        .lap-result-template .club-lineup-wrapper .club-lineup-left ul li .lineup-item .content p,
        .lap-result-template .club-lineup-wrapper .match-information-box ul li .sub-text {
            color: #5a6274;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-result-template .club-lineup-wrapper .club-lineup-left .club-lineup-head span,
        .lap-result-template .club-lineup-wrapper .match-information-box .match-head-title span {
            font-size: 15px;
            gap: 10px;
            white-space: nowrap;
        }

        .lap-result-template .club-lineup-wrapper .club-lineup-left ul li .lineup-item .content h3,
        .lap-result-template .club-lineup-wrapper .match-information-box ul li .color-text-1,
        .lap-result-template .club-lineup-wrapper .match-information-box ul li .color-text-2 {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-result-template .club-lineup-wrapper .match-information-box ul li .color-text-1,
        .lap-result-template .club-lineup-wrapper .match-information-box ul li .color-text-2 {
            font-size: 32px;
        }

        .lap-result-template .club-lineup-wrapper .match-information-box ul li .color-text-1 {
            color: #ff6f14;
        }

        .lap-result-template .club-lineup-wrapper .match-information-box {
            border-left-color: rgba(199, 201, 206, 0.35);
        }

        .lap-result-template .lap-result-columns > [class*='col-'] {
            position: relative;
            padding-inline: 20px;
        }

        .lap-result-template .lap-result-columns > [class*='col-']:nth-child(2) {
            display: flex;
            justify-content: center;
        }

        .lap-result-template .lap-result-columns > [class*='col-']:nth-child(2) > * {
            width: min(100%, 380px);
            margin-inline: auto;
        }

        .lap-result-template .lap-result-columns > [class*='col-']:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 0;
            right: -12px;
            width: 1px;
            height: 100%;
            bottom: 0;
            background: rgba(199, 201, 206, 0.35);
        }

        .lap-result-template .lap-result-columns > [class*='col-'] > * {
            height: 100%;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }

        .lap-result-template .lap-result-columns .club-lineup-left .club-lineup-head,
        .lap-result-template .lap-result-columns .match-information-box .match-head-title {
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(199, 201, 206, 0.22);
        }

        .lap-result-template .lap-result-columns .match-information-box .match-head-title {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            text-align: center;
            gap: 6px;
            width: 100%;
        }

        .lap-result-template .lap-result-columns .match-information-box .match-head-title > span {
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

        .lap-result-template .lap-result-columns .match-information-box .match-head-title > span:first-child {
            justify-self: end;
        }

        .lap-result-template .lap-result-columns .match-information-box .match-head-title > span:last-child {
            justify-self: start;
        }

        .lap-result-template .lap-result-columns .match-information-box .match-head-title > span img {
            width: 40px;
            height: 40px;
            padding: 2px;
            border-radius: 10px;
            background: #eef3ff;
            box-shadow: none;
            flex: 0 0 auto;
        }

        .lap-result-template .lap-result-columns .match-information-box .match-head-title .club-name {
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

        .lap-result-template .lap-result-columns .match-information-box .match-head-title h3 {
            white-space: nowrap;
        }

        .lap-result-template .lap-result-columns .match-information-box ul li {
            align-items: center;
        }

        .lap-result-template .lap-result-columns .match-information-box ul li .sub-text {
            min-width: 132px;
            text-align: center;
        }

        .lap-result-template .lap-result-columns .match-information-box ul li .color-text-1,
        .lap-result-template .lap-result-columns .match-information-box ul li .color-text-2 {
            min-width: 40px;
            text-align: center;
        }

        .lap-result-template .lap-result-columns .club-lineup-left .club-lineup-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }

        .lap-result-template .lap-right-lineup ul li {
            border-bottom: 1px solid rgba(199, 201, 206, 0.28);
            padding-bottom: 18px;
        }

        .lap-result-template .lap-right-lineup ul li:not(:last-child) {
            margin-bottom: 18px;
        }

        .lap-result-template .club-lineup-left ul li {
            border-bottom: 1px solid rgba(199, 201, 206, 0.28);
            padding-bottom: 18px;
        }

        .lap-result-template .club-lineup-left ul li:not(:last-child) {
            margin-bottom: 18px;
        }

        .lap-result-template .lap-result-columns {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1fr);
            gap: 32px;
            align-items: stretch;
        }

        .lap-result-template .lap-result-columns > [class*='col-'] {
            max-width: none;
            width: auto;
        }

        .lap-result-template .lap-result-columns .club-lineup-left,
        .lap-result-template .lap-result-columns .match-information-box {
            height: 100%;
        }

        @media (max-width: 991px) {
            .lap-result-template .lineup-bg-wrapper {
                padding: 32px 24px 40px;
            }

            .lap-result-template .latest-world-ranking-section {
                padding-top: 56px;
                padding-bottom: 64px;
            }

            .lap-result-template .lap-result-columns {
                display: block;
            }

            .lap-result-template .lap-result-columns > [class*='col-'] {
                max-width: 100%;
                width: 100%;
                padding-inline: 0;
                margin-bottom: 28px;
            }

            .lap-result-template .lap-result-columns > [class*='col-']:not(:last-child)::after {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .lap-result-template .latest-world-ranking-section .container {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="lap-result-template">
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

            $homeClub = $matchResult?->clubA;
            $awayClub = $matchResult?->clubB;
            $homeClubName = $matchResult?->club_a_display_name ?: $homeClub?->name ?: $homeClub?->short_name ?: 'Klub A';
            $awayClubName = $matchResult?->club_b_display_name ?: $awayClub?->name ?: $awayClub?->short_name ?: 'Klub B';
            $homeClubLogo = $matchResult?->club_a_logo_file_url ?: $clubLogoUrl($homeClub);
            $awayClubLogo = $matchResult?->club_b_logo_file_url ?: $clubLogoUrl($awayClub);
            $homeClubStats = $matchResultClubStats['club_a'] ?? [];
            $awayClubStats = $matchResultClubStats['club_b'] ?? [];
            $lineupLists = $matchResult?->lineupLists ?? collect();
            $homeLineup = $lineupLists->firstWhere('club_id', $matchResult?->club_a_id);
            $awayLineup = $lineupLists->firstWhere('club_id', $matchResult?->club_b_id);
            $starterPlayers = fn ($lineup) => collect($lineup?->players ?? [])->filter(fn ($player) => data_get($player, 'pivot.role') === 'starter')->values();
            $homePlayers = $starterPlayers($homeLineup);
            $awayPlayers = $starterPlayers($awayLineup);
            $homeGoals = collect($matchResult?->goalEvents ?? [])->filter(fn ($goal) => (int) $goal->club_id === (int) $matchResult?->club_a_id)->values();
            $awayGoals = collect($matchResult?->goalEvents ?? [])->filter(fn ($goal) => (int) $goal->club_id === (int) $matchResult?->club_b_id)->values();
            $statRows = collect([
                ['label' => 'Pertandingan', 'home' => $homeClubStats['played'] ?? 0, 'away' => $awayClubStats['played'] ?? 0],
                ['label' => 'Menang', 'home' => $homeClubStats['wins'] ?? 0, 'away' => $awayClubStats['wins'] ?? 0],
                ['label' => 'Seri', 'home' => $homeClubStats['draws'] ?? 0, 'away' => $awayClubStats['draws'] ?? 0],
                ['label' => 'Kalah', 'home' => $homeClubStats['losses'] ?? 0, 'away' => $awayClubStats['losses'] ?? 0],
                ['label' => 'Gol masuk', 'home' => $homeClubStats['goals_for'] ?? 0, 'away' => $awayClubStats['goals_for'] ?? 0],
                ['label' => 'Gol kebobolan', 'home' => $homeClubStats['goals_against'] ?? 0, 'away' => $awayClubStats['goals_against'] ?? 0],
                ['label' => 'Clean sheet', 'home' => $homeClubStats['clean_sheets'] ?? 0, 'away' => $awayClubStats['clean_sheets'] ?? 0],
            ]);
        @endphp

        <div class="lineup-bg-wrapper bg-cover">
            <div class="lineup-content">
                <h3>{{ strtoupper(trim(($matchResult?->ageGroup?->name ?? '').' '.($matchResult?->competition_format_label ?? 'Detail Hasil'))) }}</h3>
                <div class="text-item">
                    <p><b>VENU:</b> {{ $matchResult?->venue ?: '-' }}</p>
                    <p><b>date:</b> {{ $matchResult?->match_date?->translatedFormat('l, d M Y') ?: '-' }}</p>
                </div>
            </div>
            <div class="lineup-result-items">
                <div class="left-item">
                    <div class="thumb">
                        <h3>{{ $homeClubName }}</h3>
                        @include('public.partials.identity-mark', ['imageUrl' => $homeClubLogo, 'label' => $homeClubName, 'badgeClass' => 'lap-result-club-mark is-hero'])
                    </div>
                    @forelse ($homeGoals as $goal)
                        <p class="text-end">{{ strtoupper($goal->scorer?->name ?: 'Pemain tidak ditemukan') }}</p>
                    @empty
                        <p class="text-end">Belum ada gol</p>
                    @endforelse
                </div>
                <span class="score-result">@include('public.partials.match-score', ['homeScore' => $matchResult?->score_club_a, 'awayScore' => $matchResult?->score_club_b, 'separator' => ' - '])</span>
                <div class="left-item">
                    <div class="thumb">
                        @include('public.partials.identity-mark', ['imageUrl' => $awayClubLogo, 'label' => $awayClubName, 'badgeClass' => 'lap-result-club-mark is-hero'])
                        <h3>{{ $awayClubName }}</h3>
                    </div>
                    @forelse ($awayGoals as $goal)
                        <p>{{ strtoupper($goal->scorer?->name ?: 'Pemain tidak ditemukan') }}</p>
                    @empty
                        <p>Belum ada gol</p>
                    @endforelse
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
                                        @include('public.partials.identity-mark', ['imageUrl' => $homeClubLogo, 'label' => $homeClubName, 'badgeClass' => 'lap-result-club-mark is-head'])
                                        {{ $homeClubName }}
                                    </span>
                                </div>
                                <ul>
                                    @forelse ($homePlayers as $player)
                                        @php
                                            $playerName = $player->name ?: 'Pemain belum tersedia';
                                            $playerPosition = $player->displayPosition($matchResult?->age_group_id) ?: 'Pemain';
                                            $playerNumber = data_get($player, 'pivot.jersey_number') ?: $player->displayJerseyNumber($matchResult?->age_group_id) ?: '--';
                                            $playerPhoto = $player->photo_file_url;
                                        @endphp
                                        <li>
                                            @if ($loop->odd)
                                                <div class="lineup-item">
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-result-player-mark'])</div>
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
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-result-player-mark'])</div>
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
                                <div class="match-information-box">
                                    <div class="match-head-title">
                                        <span>
                                            @include('public.partials.identity-mark', ['imageUrl' => $homeClubLogo, 'label' => $homeClubName, 'badgeClass' => 'lap-result-club-mark is-head'])
                                            <span class="club-name">{{ $homeClubName }}</span>
                                        </span>
                                        <h3>Statistics</h3>
                                        <span>
                                            @include('public.partials.identity-mark', ['imageUrl' => $awayClubLogo, 'label' => $awayClubName, 'badgeClass' => 'lap-result-club-mark is-head'])
                                            <span class="club-name">{{ $awayClubName }}</span>
                                        </span>
                                    </div>
                                <ul>
                                    @foreach ($statRows as $row)
                                        <li>
                                            <span class="color-text-1">{{ is_numeric($row['home']) ? str_pad((string) $row['home'], 2, '0', STR_PAD_LEFT) : $row['home'] }}</span>
                                            <span class="sub-text">{{ $row['label'] }}</span>
                                            <span class="color-text-2">{{ is_numeric($row['away']) ? str_pad((string) $row['away'], 2, '0', STR_PAD_LEFT) : $row['away'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-8">
                            <div class="club-lineup-left lap-right-lineup">
                                <div class="club-lineup-head">
                                    <h3>LINE UPS</h3>
                                    <span>
                                        @include('public.partials.identity-mark', ['imageUrl' => $awayClubLogo, 'label' => $awayClubName, 'badgeClass' => 'lap-result-club-mark is-head'])
                                        {{ $awayClubName }}
                                    </span>
                                </div>
                                <ul>
                                    @forelse ($awayPlayers as $player)
                                        @php
                                            $playerName = $player->name ?: 'Pemain belum tersedia';
                                            $playerPosition = $player->displayPosition($matchResult?->age_group_id) ?: 'Pemain';
                                            $playerNumber = data_get($player, 'pivot.jersey_number') ?: $player->displayJerseyNumber($matchResult?->age_group_id) ?: '--';
                                            $playerPhoto = $player->photo_file_url;
                                        @endphp
                                        <li>
                                            @if ($loop->odd)
                                                <div class="lineup-item">
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-result-player-mark'])</div>
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
                                                    <div class="thumb">@include('public.partials.identity-mark', ['imageUrl' => $playerPhoto, 'label' => $playerName, 'badgeClass' => 'lap-result-player-mark'])</div>
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

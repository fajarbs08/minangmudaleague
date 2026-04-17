@extends('public.layout')

@push('styles')
    <style>
        .lap-public .inner-page-banner .banner-heading {
            max-width: 780px;
        }

        .lap-public .rts-team-section.inner.section-gap {
            padding-bottom: 70px;
        }

        .lap-public .lap-club-intro {
            margin-bottom: 44px;
        }

        .lap-public .lap-club-intro .section-title {
            margin-bottom: 16px;
        }

        .lap-public .lap-club-intro .desc {
            color: #5b5f64;
            font-size: 16px;
            line-height: 1.8;
            max-width: 760px;
            margin: 0;
        }

        .lap-public .lap-club-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .lap-public .lap-club-meta .item {
            background: #f5f5f5;
            border-radius: 999px;
            color: #111111;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            padding: 10px 14px;
            text-transform: uppercase;
        }

        .lap-public .rts-team-section .team-section-inner .team-wraper .player-card {
            min-height: 360px;
        }

        .lap-public .rts-team-section .team-section-inner .team-wraper .player-card .image,
        .lap-public .lap-player-fallback {
            display: block;
            width: 100%;
        }

        .lap-public .rts-team-section .team-section-inner .team-wraper .player-card .image img {
            width: 100%;
            height: 305px;
            object-fit: cover;
        }

        .lap-public .lap-player-fallback {
            align-items: center;
            background: #ececec;
            display: flex;
            height: 305px;
            justify-content: center;
            overflow: hidden;
        }

        .lap-public .lap-player-fallback span {
            align-items: center;
            background: #d9d9d9;
            border-radius: 50%;
            color: #6a6a6a;
            display: inline-flex;
            font-size: 46px;
            font-weight: 700;
            height: 108px;
            justify-content: center;
            width: 108px;
        }

        .lap-public .rts-team-section .team-section-inner .team-wraper .player-card .number {
            z-index: 0;
        }

        .lap-public .rts-team-section .team-section-inner .team-wraper .profile .name {
            display: inline-block;
            line-height: 1.2;
        }

        .lap-public .lap-player-extra {
            color: #777777;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 8px;
        }

        .lap-public .rts-team-member-details .team-details-single .team-picture img,
        .lap-public .lap-official-fallback {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .lap-public .lap-official-fallback {
            align-items: center;
            background: #f3f3f3;
            color: #8d8d8d;
            display: flex;
            font-size: 52px;
            font-weight: 700;
            justify-content: center;
        }

        .lap-public .lap-official-fallback span {
            display: inline-block;
            letter-spacing: .06em;
        }

        .lap-public .lap-status-copy {
            color: #999999;
            font-size: 16px;
            line-height: 1.8;
            margin-top: 18px;
        }

        .lap-public .lap-match-list {
            display: grid;
            gap: 0;
        }

        .lap-public .lap-match-item {
            align-items: center;
            border-bottom: 1px solid #e2e6e8;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 19px 0;
        }

        .lap-public .lap-match-item:first-child {
            padding-top: 0;
        }

        .lap-public .lap-match-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .lap-public .lap-match-item .teams {
            color: #5b5f64;
            font-size: 18px;
            font-weight: 600;
            line-height: 1.5;
        }

        .lap-public .lap-match-item .meta {
            color: #999999;
            font-family: "Roboto";
            font-size: 16px;
            text-align: right;
        }

        @media (max-width: 991px) {
            .lap-public .rts-team-section .team-section-inner .team-wraper .player-card {
                min-height: 0;
            }

            .lap-public .lap-match-item {
                align-items: flex-start;
                flex-direction: column;
            }

            .lap-public .lap-match-item .meta {
                text-align: left;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $clubMark = strtoupper(substr($club->short_name ?: $club->name, 0, 2));
    @endphp

    <div class="rts-team-section inner section-gap">
        <div class="container">
            <div class="lap-club-intro">
                <div class="section-title-area section-title-area1">
                    <span class="pretitle">TEAM PROFILE</span>
                    <h1 class="section-title">{{ strtoupper($club->name) }}</h1>
                    <p class="desc">
                        {{ $club->notes ?: 'Profil resmi klub beserta pemain terverifikasi, official aktif, dan pertandingan terbaru.' }}
                    </p>
                </div>
                <div class="lap-club-meta">
                    <span class="item">{{ strtoupper($club->short_name ?: $club->name) }}</span>
                    <span class="item">{{ strtoupper($club->zone ?: 'ZONA BELUM DIISI') }}</span>
                    <span class="item">{{ $club->founded_year ? 'FOUNDED '.$club->founded_year : 'FOUNDED YEAR TBD' }}</span>
                    <span class="item">{{ $clubPlayers->count() }} PLAYERS</span>
                </div>
            </div>

            <div class="team-section-inner inner">
                <div class="row">
                    @forelse ($clubPlayers as $player)
                        @php
                            $playerMark = strtoupper(substr($player->name, 0, 2));
                        @endphp
                        <div class="col-xl-3 col-md-4 col-sm-6">
                            <div class="team-wraper">
                                <div class="player-card">
                                    @if ($player->photo_file_url)
                                        <span class="image">
                                            <img src="{{ $player->photo_file_url }}" alt="{{ $player->name }}">
                                        </span>
                                    @else
                                        <span class="lap-player-fallback"><span>{{ $playerMark }}</span></span>
                                    @endif
                                    <div class="number">{{ $player->displayJerseyNumber($player->primary_age_group_id) ?: '-' }}</div>
                                    <ul class="social-area">
                                        <li><a href="#0"><i class="fas fa-futbol"></i></a></li>
                                        <li><a href="#0"><i class="fas fa-users"></i></a></li>
                                        <li><a href="#0"><i class="fas fa-shield-alt"></i></a></li>
                                    </ul>
                                </div>
                                <div class="profile">
                                    <p class="position">{{ strtoupper($player->displayPosition($player->primary_age_group_id) ?: 'PLAYER') }}</p>
                                    <span class="name">{{ strtoupper($player->name) }}</span>
                                    <div class="lap-player-extra">
                                        {{ strtoupper($player->primaryAgeGroup?->name ?: 'KELOMPOK USIA BELUM DIISI') }}
                                        @if ($player->birth_date)
                                            <br>{{ strtoupper($player->birth_date->translatedFormat('d F Y')) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="lap-summary-card">
                                <h3 class="section-title mb--20">Pemain belum tersedia</h3>
                                <p class="lap-copy mb-0">Belum ada pemain terverifikasi yang bisa ditampilkan untuk klub ini.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="rts-team-member-details">
        <div class="container">
            <div class="team-details-single">
                <h3 class="title">ABOUT {{ strtoupper($club->short_name ?: $club->name) }}</h3>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="team-picture">
                            @if ($club->logo_file_url)
                                <img src="{{ $club->logo_file_url }}" alt="{{ $club->name }}">
                            @else
                                <div class="lap-official-fallback"><span>{{ $clubMark }}</span></div>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="team-status-area">
                            <div class="status-box">
                                <div class="status-item">
                                    <span class="status-name">MANAGER</span>
                                    <span class="status-number">{{ strtoupper($club->manager_name ?: '-') }}</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-name">TITLE</span>
                                    <span class="status-number">{{ strtoupper($club->manager_title ?: '-') }}</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-name">ZONE</span>
                                    <span class="status-number">{{ strtoupper($club->zone ?: '-') }}</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-name">FOUNDED</span>
                                    <span class="status-number">{{ $club->founded_year ?: '-' }}</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-name">TRAINING BASE</span>
                                    <span class="status-number">{{ strtoupper($club->training_address ?: 'BELUM DIISI') }}</span>
                                </div>
                            </div>
                            <p class="lap-status-copy">{{ $club->address ?: 'Alamat klub belum diisi pada data verifikasi.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($clubOfficials->take(3) as $official)
                @php
                    $officialMark = strtoupper(substr($official->name, 0, 2));
                @endphp
                <div class="team-details-single">
                    <h3 class="title">{{ strtoupper(($official->role ?: 'OFFICIAL') . ' ' . $official->name) }}</h3>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="team-picture">
                                @if ($official->photo_file_url)
                                    <img src="{{ $official->photo_file_url }}" alt="{{ $official->name }}">
                                @else
                                    <div class="lap-official-fallback"><span>{{ $officialMark }}</span></div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="team-status-area">
                                <div class="status-box">
                                    <div class="status-item">
                                        <span class="status-name">ROLE</span>
                                        <span class="status-number">{{ strtoupper($official->role ?: '-') }}</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-name">CURRENT TEAM</span>
                                        <span class="status-number club">{{ strtoupper($club->name) }}</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-name">AGE GROUP</span>
                                        <span class="status-number">{{ strtoupper($official->ageGroup?->name ?: '-') }}</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-name">NATIONALITY</span>
                                        <span class="status-number">{{ strtoupper($official->citizenship ?: '-') }}</span>
                                    </div>
                                </div>
                                <p class="lap-status-copy">{{ $official->notes ?: 'Official ini terdaftar aktif pada sistem kompetisi untuk klub dan kelompok usia terkait.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="team-details-single">
                <h3 class="title">LATEST MATCHES</h3>
                @if ($clubRecentMatches->isNotEmpty())
                    <div class="lap-match-list">
                        @foreach ($clubRecentMatches as $match)
                            <div class="lap-match-item">
                                <div class="teams">
                                    {{ strtoupper($match->clubA?->name ?: 'TBD') }} VS {{ strtoupper($match->clubB?->name ?: 'TBD') }}
                                </div>
                                <div class="meta">
                                    {{ strtoupper(optional($match->match_date)->translatedFormat('d F Y')) }}
                                    @if ($match->kickoff_time)
                                        · {{ $match->kickoff_time->format('H:i') }} WIB
                                    @endif
                                    <br>
                                    {{ strtoupper($match->venue ?: 'VENUE MENYUSUL') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="lap-status-copy mb-0">Belum ada pertandingan yang tercatat untuk klub ini.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

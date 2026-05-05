@extends('public.public-layout')

@php
    use Illuminate\Support\Str;

    $selectedPublicSeason = $selectedPublicSeason ?? null;
    $publicSeasonQuery = $publicSeasonQuery ?? [];
    $isHistoricalPublicSeason = $isHistoricalPublicSeason ?? false;
    $clubModel = $isHistoricalPublicSeason ? $player->seasonClub : $player->club;
    $primaryAgeGroupName = $player->primaryAgeGroup?->name ?: 'Kelompok usia belum diatur';
    $positionLabel = $player->displayPosition($player->primary_age_group_id) ?: 'Pemain';
    $jerseyNumber = $player->displayJerseyNumber($player->primary_age_group_id);
    $playerAge = $player->birth_date?->age;
    $playerInitial = Str::of($player->name)->trim()->substr(0, 2)->upper();
    $playerSummary = trim($player->name.' adalah pemain terverifikasi '.($clubModel?->name ? 'dari '.$clubModel->name.' ' : '').'yang tercatat pada roster publik Liga Anak Piaman Laweh. Halaman ini menampilkan detail profil kompetisi dan registrasi aktif tanpa membuka data administrasi pribadi.');
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Menunggu Review',
        'revision' => 'Perlu Revisi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
    $playerStats = [
        ['label' => 'Nomor', 'value' => $jerseyNumber ? '#'.$jerseyNumber : '-'],
        ['label' => 'Usia', 'value' => $playerAge ? $playerAge.' tahun' : '-'],
        ['label' => 'Tinggi', 'value' => $player->height_cm ? $player->height_cm.' cm' : '-'],
        ['label' => 'Berat', 'value' => $player->weight_kg ? $player->weight_kg.' kg' : '-'],
    ];
    $playerFacts = [
        ['label' => 'Klub', 'value' => $clubModel?->name ?: '-'],
        ['label' => 'Kelompok usia utama', 'value' => $primaryAgeGroupName],
        ['label' => 'Posisi', 'value' => $positionLabel],
        ['label' => 'Nomor punggung', 'value' => $jerseyNumber ? '#'.$jerseyNumber : '-'],
        ['label' => 'Kewarganegaraan', 'value' => $player->citizenship ?: '-'],
        ['label' => 'Dominan kaki', 'value' => $player->dominant_foot ?: '-'],
        ['label' => 'Kapten', 'value' => $player->is_captain ? 'Ya' : 'Tidak'],
        ['label' => 'Status publik', 'value' => 'Terverifikasi'],
        ['label' => 'Season', 'value' => $selectedPublicSeason?->name ?: 'Musim aktif'],
    ];
    $playerRegistrations = $player->ageRegistrations
        ->filter(fn ($registration) => $registration->ageGroup)
        ->map(function ($registration) use ($statusLabels, $player) {
            return [
                'ageGroup' => $registration->ageGroup->name,
                'season' => $registration->season ?: 'Musim aktif',
                'position' => $registration->position ?: 'Belum diatur',
                'jersey' => $registration->jersey_number ? '#'.$registration->jersey_number : '-',
                'status' => $statusLabels[$registration->registration_status ?: $player->verification_status] ?? 'Terverifikasi',
                'roles' => collect([
                    $registration->is_starter ? 'Starter' : null,
                    $registration->is_substitute ? 'Cadangan' : null,
                ])->filter()->values(),
            ];
        })
        ->values();
    $clubUrl = $clubModel ? route('public.clubs.show', ['clubSlug' => $clubModel->public_slug] + $publicSeasonQuery) : route('public.clubs', $publicSeasonQuery);
    $clubMark = Str::upper(Str::substr($clubModel?->short_name ?: $clubModel?->name ?: 'KL', 0, 2));
@endphp

@push('styles')
    <style>
        .lap-player-detail-section {
            padding: 80px 0 96px;
            background: #ffffff;
            color: #030523;
        }

        .lap-player-detail-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-player-detail-section .container {
                max-width: 1680px;
            }
        }

        .lap-player-title,
        .lap-player-role,
        .lap-player-panel h3,
        .lap-player-aside-card h4,
        .lap-player-registration-card h4,
        .lap-player-stat-card strong,
        .lap-player-jersey,
        .lap-player-club-name {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-player-lead,
        .lap-player-meta,
        .lap-player-panel p,
        .lap-player-fact-value,
        .lap-player-registration-meta,
        .lap-player-empty,
        .lap-player-aside-copy,
        .lap-player-club-meta {
            color: #667085;
        }

        .lap-player-shell,
        .lap-player-main-column,
        .lap-player-aside {
            display: grid;
            gap: 24px;
        }

        .lap-player-hero-card {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 32px;
            padding: 32px;
            border: 1px solid #e7e9f0;
            border-radius: 28px;
            background: linear-gradient(145deg, #ffffff 0%, #f7f9fc 100%);
            box-shadow: 0 28px 72px rgba(3, 5, 35, 0.08);
            align-items: center;
        }

        .lap-player-photo-panel {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
            min-height: 430px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .lap-player-photo-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lap-player-photo-fallback {
            min-height: 430px;
            display: grid;
            place-items: center;
            color: rgba(255, 255, 255, 0.94);
            font-family: 'Big Shoulders', sans-serif;
            font-size: 108px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .lap-player-photo-badge {
            position: absolute;
            left: 18px;
            bottom: 18px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.94);
            color: #030523;
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .lap-player-summary {
            display: grid;
            gap: 18px;
        }

        .lap-player-headline {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: start;
        }

        .lap-player-title {
            margin: 0;
            font-size: 3rem;
            font-weight: 700;
            line-height: .94;
        }

        .lap-player-role {
            margin: 8px 0 0;
            font-size: 1.35rem;
            font-weight: 700;
            color: #e41b23;
        }

        .lap-player-jersey {
            flex-shrink: 0;
            font-size: clamp(3.2rem, 9vw, 6rem);
            font-weight: 700;
            line-height: .85;
            color: rgba(3, 5, 35, 0.1);
        }

        .lap-player-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .lap-player-chip-list,
        .lap-player-registration-role-list,
        .lap-player-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .lap-player-chip,
        .lap-player-registration-role {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: .35rem .8rem;
            border-radius: 999px;
            border: 1px solid #e3e9f4;
            background: #fff;
            color: #0d2f67;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .lap-player-chip.is-accent,
        .lap-player-btn.is-primary {
            background: #e41b23;
            border-color: #e41b23;
            color: #fff;
        }

        .lap-player-lead {
            margin: 0;
            max-width: 70ch;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.7;
        }

        .lap-player-stat-grid,
        .lap-player-facts,
        .lap-player-registration-grid,
        .lap-player-registration-facts,
        .lap-player-content-grid {
            display: grid;
            gap: 14px;
        }

        .lap-player-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .lap-player-stat-card,
        .lap-player-panel,
        .lap-player-aside-card,
        .lap-player-registration-card {
            border: 1px solid #e7e9f0;
            border-radius: 22px;
            background: #fff;
        }

        .lap-player-stat-card {
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
        }

        .lap-player-stat-card span,
        .lap-player-fact-label,
        .lap-player-registration-label,
        .lap-player-aside-kicker {
            display: block;
            color: #667085;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-player-stat-card strong {
            display: block;
            margin-top: 8px;
            font-size: 2rem;
            font-weight: 700;
            line-height: .9;
        }

        .lap-player-content-grid {
            grid-template-columns: minmax(0, 1.6fr) minmax(300px, .78fr);
            align-items: start;
            gap: 28px;
        }

        .lap-player-panel,
        .lap-player-aside-card,
        .lap-player-registration-card {
            padding: 28px;
        }

        .lap-player-panel h3,
        .lap-player-aside-card h4 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 700;
        }

        .lap-player-panel p,
        .lap-player-aside-copy {
            margin: 10px 0 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: .98rem;
            font-weight: 600;
            line-height: 1.7;
        }

        .lap-player-facts {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 20px;
        }

        .lap-player-fact {
            padding: 16px 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #edf1f7;
        }

        .lap-player-fact-value,
        .lap-player-registration-value {
            margin-top: 8px;
            color: #030523;
            font-size: 1rem;
            font-weight: 700;
        }

        .lap-player-registration-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 20px;
        }

        .lap-player-registration-card {
            background: linear-gradient(180deg, #fff 0%, #f9fbfd 100%);
        }

        .lap-player-registration-card h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .lap-player-registration-meta {
            margin-top: 6px;
            font-size: .9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lap-player-registration-facts {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 18px;
            gap: 12px;
        }

        .lap-player-registration-fact {
            padding: 14px 16px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid #edf1f7;
        }

        .lap-player-registration-role-list {
            margin-top: 18px;
        }

        .lap-player-empty {
            margin-top: 20px;
            padding: 18px 20px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px dashed #d8deea;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 600;
        }

        .lap-player-club-head {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .lap-player-club-mark {
            width: 66px;
            height: 66px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            color: rgba(255, 255, 255, 0.94);
            font-family: 'Big Shoulders', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .lap-player-club-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lap-player-club-name {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .lap-player-club-meta {
            margin-top: 6px;
            font-size: .92rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lap-player-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: .8rem 1rem;
            border-radius: 999px;
            border: 1px solid #d8deea;
            background: #fff;
            color: #030523;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            transition: .2s ease;
        }

        .lap-player-btn:hover {
            transform: translateY(-1px);
            color: #030523;
        }

        .lap-player-btn.is-primary:hover {
            color: #fff;
        }

        @media (max-width: 1199px) {
            .lap-player-hero-card,
            .lap-player-content-grid {
                grid-template-columns: 1fr;
            }

            .lap-player-stat-grid,
            .lap-player-registration-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .lap-player-detail-section {
                padding: 64px 0 80px;
            }

            .lap-player-hero-card,
            .lap-player-panel,
            .lap-player-aside-card,
            .lap-player-registration-card {
                padding: 22px;
            }

            .lap-player-headline {
                flex-direction: column;
            }

            .lap-player-jersey {
                font-size: 4.2rem;
            }

            .lap-player-title {
                font-size: 2.3rem;
            }

            .lap-player-stat-grid,
            .lap-player-facts,
            .lap-player-registration-grid,
            .lap-player-registration-facts {
                grid-template-columns: 1fr;
            }

            .lap-player-photo-panel,
            .lap-player-photo-fallback {
                min-height: 340px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="lap-player-detail-section">
        <div class="container">
            <div class="lap-player-shell">
                <article class="lap-player-hero-card">
                    <div class="lap-player-photo-panel">
                        @if ($player->photo_file_url)
                            <img src="{{ $player->photo_file_url }}" alt="{{ $player->name }}">
                        @else
                            <div class="lap-player-photo-fallback">{{ $playerInitial ?: 'PL' }}</div>
                        @endif

                        <span class="lap-player-photo-badge">
                            <i class="fa-solid fa-circle-check"></i>
                            Roster publik terverifikasi
                        </span>
                    </div>

                    <div class="lap-player-summary">
                        <span class="lap-section-kicker">Profil Pemain</span>

                        <div class="lap-player-headline">
                            <div>
                                <h2 class="lap-player-title">{{ $player->name }}</h2>
                                <div class="lap-player-role">{{ $positionLabel }}</div>
                            </div>
                            <div class="lap-player-jersey">{{ $jerseyNumber ? str_pad((string) $jerseyNumber, 2, '0', STR_PAD_LEFT) : '00' }}</div>
                        </div>

                        <div class="lap-player-meta">
                            <span>{{ $clubModel?->name ?: 'Klub belum tersedia' }}</span>
                            <span>{{ $primaryAgeGroupName }}</span>
                            <span>{{ $player->is_captain ? 'Kapten Tim' : 'Pemain Aktif' }}</span>
                        </div>

                        <div class="lap-player-chip-list">
                            <span class="lap-player-chip is-accent">Terverifikasi</span>
                            @if ($selectedPublicSeason)
                                <span class="lap-player-chip">{{ $selectedPublicSeason->name }}{{ $isHistoricalPublicSeason ? ' · histori' : '' }}</span>
                            @endif
                            <span class="lap-player-chip">{{ $positionLabel }}</span>
                            <span class="lap-player-chip">{{ $primaryAgeGroupName }}</span>
                            @if ($jerseyNumber)
                                <span class="lap-player-chip">#{{ $jerseyNumber }}</span>
                            @endif
                        </div>

                        <p class="lap-player-lead">{{ $playerSummary }}</p>

                        <div class="lap-player-stat-grid">
                            @foreach ($playerStats as $stat)
                                <div class="lap-player-stat-card">
                                    <span>{{ $stat['label'] }}</span>
                                    <strong>{{ $stat['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <div class="lap-player-content-grid">
                    <div class="lap-player-main-column">
                        <article class="lap-player-panel">
                            <span class="lap-section-kicker">Identitas Kompetisi</span>
                            <h3>Profil Publik Pemain</h3>
                            <p>Informasi di bawah ini ditampilkan sebagai profil web publik untuk kebutuhan roster, pengenalan pemain, dan navigasi dari halaman klub. Data administratif pribadi tetap tidak ditampilkan.</p>

                            <div class="lap-player-facts">
                                @foreach ($playerFacts as $fact)
                                    <div class="lap-player-fact">
                                        <span class="lap-player-fact-label">{{ $fact['label'] }}</span>
                                        <div class="lap-player-fact-value">{{ $fact['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </article>

                        <article class="lap-player-panel">
                            <span class="lap-section-kicker">Registrasi</span>
                            <h3>Registrasi Kompetisi</h3>
                            <p>Daftar ini merangkum kelompok usia aktif pemain, posisi yang digunakan pada roster, nomor punggung, dan peran pertandingan yang sudah tercatat.</p>

                            @if ($playerRegistrations->isNotEmpty())
                                <div class="lap-player-registration-grid">
                                    @foreach ($playerRegistrations as $registration)
                                        <article class="lap-player-registration-card">
                                            <h4>{{ $registration['ageGroup'] }}</h4>
                                            <div class="lap-player-registration-meta">{{ $registration['season'] }}</div>

                                            <div class="lap-player-registration-facts">
                                                <div class="lap-player-registration-fact">
                                                    <span class="lap-player-registration-label">Posisi</span>
                                                    <span class="lap-player-registration-value">{{ $registration['position'] }}</span>
                                                </div>
                                                <div class="lap-player-registration-fact">
                                                    <span class="lap-player-registration-label">Nomor</span>
                                                    <span class="lap-player-registration-value">{{ $registration['jersey'] }}</span>
                                                </div>
                                                <div class="lap-player-registration-fact">
                                                    <span class="lap-player-registration-label">Status</span>
                                                    <span class="lap-player-registration-value">{{ $registration['status'] }}</span>
                                                </div>
                                            </div>

                                            @if ($registration['roles']->isNotEmpty())
                                                <div class="lap-player-registration-role-list">
                                                    @foreach ($registration['roles'] as $role)
                                                        <span class="lap-player-registration-role">{{ $role }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="lap-player-empty">Belum ada detail kelompok usia yang ditampilkan pada profil publik ini.</div>
                            @endif
                        </article>
                    </div>

                    <aside class="lap-player-aside">
                        <article class="lap-player-aside-card">
                            <span class="lap-player-aside-kicker">Klub</span>
                            <h4>Terhubung ke Tim</h4>

                            <div class="lap-player-club-head">
                                <div class="lap-player-club-mark">
                                    @if ($clubModel?->logo_file_url)
                                        <img src="{{ $clubModel->logo_file_url }}" alt="{{ $clubModel->name }}">
                                    @else
                                        <span>{{ $clubMark }}</span>
                                    @endif
                                </div>

                                <div>
                                    <h5 class="lap-player-club-name">{{ $clubModel?->short_name ?: $clubModel?->name ?: 'Klub' }}</h5>
                                    <div class="lap-player-club-meta">{{ $clubModel?->name ?: 'Profil klub belum tersedia' }}</div>
                                </div>
                            </div>

                            <p class="lap-player-aside-copy">Buka halaman klub untuk melihat roster lengkap, ofisial aktif, dan ringkasan pertandingan terbaru dari tim pemain ini.</p>

                            <div class="lap-player-actions">
                                <a href="{{ $clubUrl }}" class="lap-player-btn is-primary">
                                    <i class="fa-solid fa-users"></i>
                                    Profil Klub
                                </a>
                            </div>
                        </article>

                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('public.public-layout')

@php
    use Illuminate\Support\Str;

    $primaryAgeGroupName = $official->ageGroup?->name
        ?: $official->ageRegistrations->pluck('ageGroup.name')->filter()->first()
        ?: 'Kelompok usia belum diatur';
    $roleLabel = $official->role ?: 'Ofisial';
    $licenseLabel = $official->license_levels ?: $official->license_number ?: 'Terverifikasi panitia';
    $officialAge = $official->birth_date?->age;
    $officialInitial = Str::of($official->name)->trim()->substr(0, 2)->upper();
    $officialSummary = trim($official->name.' adalah ofisial terverifikasi '.($official->club?->name ? 'dari '.$official->club->name.' ' : '').'yang ditampilkan pada portal publik Liga Anak Piaman Laweh. Halaman ini memuat peran kompetisi dan registrasi aktif tanpa menampilkan kontak pribadi atau identitas sensitif.');
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Menunggu Review',
        'revision' => 'Perlu Revisi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
    $officialStats = [
        ['label' => 'Peran', 'value' => $roleLabel],
        ['label' => 'Lisensi', 'value' => $licenseLabel],
        ['label' => 'Status', 'value' => $official->is_active ? 'Aktif' : 'Nonaktif'],
        ['label' => 'Usia', 'value' => $officialAge ? $officialAge.' tahun' : '-'],
    ];
    $officialFacts = [
        ['label' => 'Klub', 'value' => $official->club?->name ?: '-'],
        ['label' => 'Kelompok usia utama', 'value' => $primaryAgeGroupName],
        ['label' => 'Peran utama', 'value' => $roleLabel],
        ['label' => 'Lisensi', 'value' => $licenseLabel],
        ['label' => 'Kewarganegaraan', 'value' => $official->citizenship ?: '-'],
        ['label' => 'Ketersediaan', 'value' => $official->is_active ? 'Aktif mendampingi tim' : 'Tidak aktif'],
        ['label' => 'Status publik', 'value' => 'Terverifikasi'],
        ['label' => 'Akses', 'value' => 'Profil web publik'],
    ];
    $officialRegistrations = $official->ageRegistrations
        ->filter(fn ($registration) => $registration->ageGroup)
        ->map(function ($registration) use ($statusLabels, $official) {
            return [
                'ageGroup' => $registration->ageGroup->name,
                'season' => $registration->season ?: 'Musim aktif',
                'role' => $registration->role ?: $official->role ?: 'Ofisial',
                'license' => $registration->license_levels ?: $official->license_levels ?: $official->license_number ?: 'Terverifikasi panitia',
                'status' => $statusLabels[$registration->registration_status ?: $official->verification_status] ?? 'Terverifikasi',
            ];
        })
        ->values();
    $clubUrl = $official->club ? route('public.clubs.show', ['clubSlug' => $official->club->public_slug]) : route('public.clubs');
    $clubMark = Str::upper(Str::substr($official->club?->short_name ?: $official->club?->name ?: 'KL', 0, 2));
@endphp

@push('styles')
    <style>
        .lap-official-detail-section {
            padding: 80px 0 96px;
            background: #ffffff;
            color: #030523;
        }

        .lap-official-detail-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-official-detail-section .container {
                max-width: 1680px;
            }
        }

        .lap-official-title,
        .lap-official-role,
        .lap-official-panel h3,
        .lap-official-aside-card h4,
        .lap-official-registration-card h4,
        .lap-official-stat-card strong,
        .lap-official-badge-mark,
        .lap-official-club-name {
            color: #030523;
            font-family: 'Big Shoulders', sans-serif;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-official-lead,
        .lap-official-meta,
        .lap-official-panel p,
        .lap-official-fact-value,
        .lap-official-registration-meta,
        .lap-official-empty,
        .lap-official-aside-copy,
        .lap-official-club-meta,

        .lap-official-shell,
        .lap-official-main-column,
        .lap-official-aside {
            display: grid;
            gap: 24px;
        }

        .lap-official-hero-card {
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

        .lap-official-photo-panel {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
            min-height: 430px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .lap-official-photo-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lap-official-photo-fallback {
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

        .lap-official-photo-badge {
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

        .lap-official-summary {
            display: grid;
            gap: 18px;
        }

        .lap-official-headline {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: start;
        }

        .lap-official-title {
            margin: 0;
            font-size: 3rem;
            font-weight: 700;
            line-height: .94;
        }

        .lap-official-role {
            margin: 8px 0 0;
            font-size: 1.35rem;
            font-weight: 700;
            color: #e41b23;
        }

        .lap-official-badge-mark {
            flex-shrink: 0;
            font-size: clamp(2.8rem, 8vw, 5rem);
            font-weight: 700;
            line-height: .85;
            color: rgba(3, 5, 35, 0.1);
        }

        .lap-official-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .lap-official-chip-list,
        .lap-official-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .lap-official-chip {
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

        .lap-official-chip.is-accent,
        .lap-official-btn.is-primary {
            background: #e41b23;
            border-color: #e41b23;
            color: #fff;
        }

        .lap-official-lead {
            margin: 0;
            max-width: 70ch;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.7;
        }

        .lap-official-stat-grid,
        .lap-official-facts,
        .lap-official-registration-grid,
        .lap-official-registration-facts,
        .lap-official-content-grid {
            display: grid;
            gap: 14px;
        }

        .lap-official-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .lap-official-stat-card,
        .lap-official-panel,
        .lap-official-aside-card,
        .lap-official-registration-card {
            border: 1px solid #e7e9f0;
            border-radius: 22px;
            background: #fff;
        }

        .lap-official-stat-card {
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
        }

        .lap-official-stat-card span,
        .lap-official-fact-label,
        .lap-official-registration-label,
        .lap-official-aside-kicker {
            display: block;
            color: #667085;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-official-stat-card strong {
            display: block;
            margin-top: 8px;
            font-size: 2rem;
            font-weight: 700;
            line-height: .9;
        }

        .lap-official-content-grid {
            grid-template-columns: minmax(0, 1.6fr) minmax(300px, .78fr);
            align-items: start;
            gap: 28px;
        }

        .lap-official-panel,
        .lap-official-aside-card,
        .lap-official-registration-card {
            padding: 28px;
        }

        .lap-official-panel h3,
        .lap-official-aside-card h4 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 700;
        }

        .lap-official-panel p,
        .lap-official-aside-copy {
            margin: 10px 0 0;
            font-family: 'Chakra Petch', sans-serif;
            font-size: .98rem;
            font-weight: 600;
            line-height: 1.7;
        }

        .lap-official-facts {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 20px;
        }

        .lap-official-fact {
            padding: 16px 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #edf1f7;
        }

        .lap-official-fact-value,
        .lap-official-registration-value {
            margin-top: 8px;
            color: #030523;
            font-size: 1rem;
            font-weight: 700;
        }

        .lap-official-registration-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 20px;
        }

        .lap-official-registration-card {
            background: linear-gradient(180deg, #fff 0%, #f9fbfd 100%);
        }

        .lap-official-registration-card h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .lap-official-registration-meta {
            margin-top: 6px;
            font-size: .9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lap-official-registration-facts {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 18px;
            gap: 12px;
        }

        .lap-official-registration-fact {
            padding: 14px 16px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid #edf1f7;
        }

        .lap-official-empty {
            margin-top: 20px;
            padding: 18px 20px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px dashed #d8deea;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 600;
        }

        .lap-official-club-head {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .lap-official-club-mark {
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

        .lap-official-club-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lap-official-club-name {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .lap-official-club-meta {
            margin-top: 6px;
            font-size: .92rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lap-official-btn {
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

        .lap-official-btn:hover {
            transform: translateY(-1px);
            color: #030523;
        }

        .lap-official-btn.is-primary:hover {
            color: #fff;
        }

        @media (max-width: 1199px) {
            .lap-official-hero-card,
            .lap-official-content-grid {
                grid-template-columns: 1fr;
            }

            .lap-official-stat-grid,
            .lap-official-registration-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .lap-official-detail-section {
                padding: 64px 0 80px;
            }

            .lap-official-hero-card,
            .lap-official-panel,
            .lap-official-aside-card,
            .lap-official-registration-card {
                padding: 22px;
            }

            .lap-official-headline {
                flex-direction: column;
            }

            .lap-official-badge-mark {
                font-size: 4rem;
            }

            .lap-official-title {
                font-size: 2.3rem;
            }

            .lap-official-stat-grid,
            .lap-official-facts,
            .lap-official-registration-grid,
            .lap-official-registration-facts {
                grid-template-columns: 1fr;
            }

            .lap-official-photo-panel,
            .lap-official-photo-fallback {
                min-height: 340px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="lap-official-detail-section">
        <div class="container">
            <div class="lap-official-shell">
                <article class="lap-official-hero-card">
                    <div class="lap-official-photo-panel">
                        @if ($official->photo_file_url)
                            <img src="{{ $official->photo_file_url }}" alt="{{ $official->name }}">
                        @else
                            <div class="lap-official-photo-fallback">{{ $officialInitial ?: 'OF' }}</div>
                        @endif

                        <span class="lap-official-photo-badge">
                            <i class="fa-solid fa-circle-check"></i>
                            Ofisial publik terverifikasi
                        </span>
                    </div>

                    <div class="lap-official-summary">
                        <span class="lap-section-kicker">Profil Ofisial</span>

                        <div class="lap-official-headline">
                            <div>
                                <h2 class="lap-official-title">{{ $official->name }}</h2>
                                <div class="lap-official-role">{{ $roleLabel }}</div>
                            </div>
                            <div class="lap-official-badge-mark">{{ Str::upper(Str::substr($roleLabel, 0, 3)) }}</div>
                        </div>

                        <div class="lap-official-meta">
                            <span>{{ $official->club?->name ?: 'Klub belum tersedia' }}</span>
                            <span>{{ $primaryAgeGroupName }}</span>
                            <span>{{ $official->is_active ? 'Aktif mendampingi tim' : 'Status nonaktif' }}</span>
                        </div>

                        <div class="lap-official-chip-list">
                            <span class="lap-official-chip is-accent">Terverifikasi</span>
                            <span class="lap-official-chip">{{ $roleLabel }}</span>
                            <span class="lap-official-chip">{{ $licenseLabel }}</span>
                            <span class="lap-official-chip">{{ $primaryAgeGroupName }}</span>
                        </div>

                        <p class="lap-official-lead">{{ $officialSummary }}</p>

                        <div class="lap-official-stat-grid">
                            @foreach ($officialStats as $stat)
                                <div class="lap-official-stat-card">
                                    <span>{{ $stat['label'] }}</span>
                                    <strong>{{ $stat['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <div class="lap-official-content-grid">
                    <div class="lap-official-main-column">
                        <article class="lap-official-panel">
                            <span class="lap-section-kicker">Identitas Kompetisi</span>
                            <h3>Profil Publik Ofisial</h3>
                            <p>Halaman ini dipakai sebagai profil web publik untuk memperlihatkan peran ofisial dalam kompetisi. Kontak, nomor identitas, dan detail administrasi lain tetap disembunyikan dari tampilan publik.</p>

                            <div class="lap-official-facts">
                                @foreach ($officialFacts as $fact)
                                    <div class="lap-official-fact">
                                        <span class="lap-official-fact-label">{{ $fact['label'] }}</span>
                                        <div class="lap-official-fact-value">{{ $fact['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </article>

                        <article class="lap-official-panel">
                            <span class="lap-section-kicker">Registrasi</span>
                            <h3>Registrasi Kompetisi</h3>
                            <p>Ringkasan ini menunjukkan kelompok usia yang diikuti, jabatan yang digunakan pada roster, lisensi, dan status verifikasi per registrasi aktif.</p>

                            @if ($officialRegistrations->isNotEmpty())
                                <div class="lap-official-registration-grid">
                                    @foreach ($officialRegistrations as $registration)
                                        <article class="lap-official-registration-card">
                                            <h4>{{ $registration['ageGroup'] }}</h4>
                                            <div class="lap-official-registration-meta">{{ $registration['season'] }}</div>

                                            <div class="lap-official-registration-facts">
                                                <div class="lap-official-registration-fact">
                                                    <span class="lap-official-registration-label">Jabatan</span>
                                                    <span class="lap-official-registration-value">{{ $registration['role'] }}</span>
                                                </div>
                                                <div class="lap-official-registration-fact">
                                                    <span class="lap-official-registration-label">Lisensi</span>
                                                    <span class="lap-official-registration-value">{{ $registration['license'] }}</span>
                                                </div>
                                                <div class="lap-official-registration-fact">
                                                    <span class="lap-official-registration-label">Status</span>
                                                    <span class="lap-official-registration-value">{{ $registration['status'] }}</span>
                                                </div>
                                            </div>

                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="lap-official-empty">Belum ada detail kelompok usia yang ditampilkan pada profil publik ini.</div>
                            @endif
                        </article>
                    </div>

                    <aside class="lap-official-aside">
                        <article class="lap-official-aside-card">
                            <span class="lap-official-aside-kicker">Klub</span>
                            <h4>Terhubung ke Tim</h4>

                            <div class="lap-official-club-head">
                                <div class="lap-official-club-mark">
                                    @if ($official->club?->logo_file_url)
                                        <img src="{{ $official->club->logo_file_url }}" alt="{{ $official->club->name }}">
                                    @else
                                        <span>{{ $clubMark }}</span>
                                    @endif
                                </div>

                                <div>
                                    <h5 class="lap-official-club-name">{{ $official->club?->short_name ?: $official->club?->name ?: 'Klub' }}</h5>
                                    <div class="lap-official-club-meta">{{ $official->club?->name ?: 'Profil klub belum tersedia' }}</div>
                                </div>
                            </div>

                            <p class="lap-official-aside-copy">Buka halaman klub untuk melihat roster pemain, ofisial aktif lain, dan ringkasan pertandingan dari tim yang sama.</p>

                            <div class="lap-official-actions">
                                <a href="{{ $clubUrl }}" class="lap-official-btn is-primary">
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

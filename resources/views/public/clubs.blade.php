@extends('public.public-layout')

@php
    $clubCount = $featuredClubs->count();
    $zoneCount = $featuredClubs->pluck('zone')->filter()->unique()->count();
    $managerCount = $featuredClubs->filter(fn ($club) => filled($club->manager_name))->count();
@endphp

@push('styles')
    <style>
        .lap-public .latest-world-ranking-section {
            background: #ffffff;
            padding-top: 80px;
            padding-bottom: 96px;
            color: #030523;
        }

        .lap-public .latest-world-ranking-section .container {
            max-width: 1620px;
        }

        .lap-public .latest-world-ranking-wrapper .content h3,
        .lap-public .latest-world-ranking-wrapper .text-item p,
        .lap-public .latest-world-ranking-wrapper table th,
        .lap-public .latest-world-ranking-wrapper table td {
            color: #030523;
        }

        .lap-public .latest-world-ranking-wrapper .content h3 {
            font-family: 'Big Shoulders', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-wrapper .text-item p {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-wrapper .club-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 14px 24px;
            margin-top: 18px;
            color: #667085;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-wrapper .club-summary span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .lap-public .latest-world-ranking-table {
            margin-top: 48px;
        }

        .lap-public .latest-world-ranking-table .table-responsive {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .lap-public .latest-world-ranking-table table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .85), 0 8px 18px rgba(15, 23, 42, .06);
        }

        .lap-public .latest-world-ranking-table table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background: #ffffff;
            text-align: left;
        }

        .lap-public .latest-world-ranking-table tbody tr {
            transition: background-color .18s ease, transform .18s ease;
        }

        .lap-public .latest-world-ranking-table tbody tr:nth-child(odd) td {
            background: #ffffff;
        }

        .lap-public .latest-world-ranking-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .lap-public .latest-world-ranking-table tbody tr:hover {
            transform: translateY(-1px);
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td {
            background: #eef4ff;
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td:first-child {
            border-left: 3px solid #e41b23;
            padding-left: 12px;
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td:last-child .lap-table-detail-link {
            transform: translateY(-1px) scale(1.08);
            color: #e41b23;
            box-shadow: 0 10px 18px rgba(228, 27, 35, .14);
        }

        .lap-public .latest-world-ranking-table tbody tr:hover td:last-child .lap-table-detail-link i {
            transform: scale(1.08) rotate(-10deg);
        }

        .lap-public .latest-world-ranking-table tbody tr td:first-child {
            border-left: 3px solid transparent;
            transition: background-color .18s ease, border-color .18s ease, padding-left .18s ease;
        }

        .lap-public .latest-world-ranking-table tbody tr td {
            transition: background-color .18s ease, color .18s ease;
        }

        .lap-public .latest-world-ranking-table thead th {
            background: #f7f7f7;
        }

        .lap-public .latest-world-ranking-table table th,
        .lap-public .latest-world-ranking-table table td {
            padding: 14px 15px;
            border-bottom: 1px solid #eee;
            font-size: 18px;
            font-weight: 700;
            color: #030523;
        }

        .lap-public .latest-world-ranking-table table th {
            font-size: 16px;
            text-transform: uppercase;
        }

        .lap-public .latest-world-ranking-table .club-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lap-public .latest-world-ranking-table .club-name-cell img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .lap-public .latest-world-ranking-table .club-name-cell strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }

        .lap-public .latest-world-ranking-table .club-name-cell span {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #667085;
            text-transform: none;
            letter-spacing: 0;
            margin-top: 2px;
        }

        .lap-public .latest-world-ranking-table .text-center a {
            color: #030523;
        }

        .lap-public .latest-world-ranking-table .lap-table-detail-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            background: linear-gradient(180deg, #ffffff 0%, #f2f6ff 100%);
            border: 1px solid rgba(3, 5, 35, 0.08);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
            transition: transform .18s ease, box-shadow .18s ease, color .18s ease, background-color .18s ease;
        }

        .lap-public .latest-world-ranking-table .lap-table-detail-link i {
            font-size: 15px;
            transition: transform .18s ease;
        }

        .lap-public .latest-world-ranking-table .lap-table-detail-link:hover,
        .lap-public .latest-world-ranking-table .lap-table-detail-link:focus-visible {
            color: #e41b23;
            transform: translateY(-1px) scale(1.06);
            background: linear-gradient(180deg, #ffffff 0%, #fff1f1 100%);
            box-shadow: 0 12px 20px rgba(228, 27, 35, .12);
            outline: none;
        }

        @media (max-width: 767px) {
            .lap-public .latest-world-ranking-table {
                margin-top: 32px;
            }

            .lap-public .latest-world-ranking-wrapper .text-item p {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="latest-world-ranking-section wow fadeInUp" data-wow-delay=".15s">
        <div class="container">
            <div class="latest-world-ranking-wrapper">
                <div class="content wow fadeInUp" data-wow-delay=".2s">
                    <h3 class="lap-brand-heading"><span class="lap-outline-word">Klub</span> Peserta</h3>
                    <div class="text-item">
                        <p>Daftar klub yang sudah dipublikasikan resmi. Buka detail untuk melihat profil, pemain, ofisial, dan riwayat laga klub.</p>
                    </div>
                    <div class="club-summary">
                        <span>{{ $clubCount }} klub tampil</span>
                        <span>{{ $zoneCount }} zona</span>
                        <span>{{ $managerCount }} manajer diisi</span>
                    </div>
                </div>

                <div class="latest-world-ranking-table wow fadeInUp" data-wow-delay=".25s">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Klub</th>
                                    <th>Zona</th>
                                    <th>Tahun</th>
                                    <th>Manajer</th>
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($featuredClubs as $club)
                                    @php
                                        $clubMark = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($club->short_name ?: $club->name, 0, 2));
                                    @endphp
                                    <tr class="wow fadeInUp" data-wow-delay=".{{ 2 + ($loop->index * 1) }}s">
                                        <td>
                                            <div class="club-name-cell">
                                                @if ($club->logo_file_url)
                                                    <img src="{{ $club->logo_file_url }}" alt="{{ $club->name }}">
                                                @else
                                                    <span class="lap-logo-mark" style="width:40px;height:40px;font-size:14px;">{{ $clubMark }}</span>
                                                @endif
                                                <div>
                                                    <strong>{{ $club->name }}</strong>
                                                    <span>{{ $club->short_name ?: 'Klub peserta' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $club->zone ?: '-' }}</td>
                                        <td>{{ $club->founded_year ?: '-' }}</td>
                                        <td>{{ $club->manager_name ?: '-' }}</td>
                                        <td class="text-center">
                                            @include('public.partials.table-detail-link', ['href' => route('public.clubs.show', ['clubSlug' => $club->public_slug])])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">Belum ada klub yang dipublikasikan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

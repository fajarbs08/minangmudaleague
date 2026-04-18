<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pertandingan</title>
    <style>
        @page {
            margin: 26px 24px 30px 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 10px;
            line-height: 1.45;
            background: #ffffff;
        }

        .cover {
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            padding: 18px 20px;
            margin-bottom: 18px;
        }

        .eyebrow {
            margin: 0 0 8px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #475569;
        }

        .title {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
        }

        .subtitle {
            margin: 8px 0 0;
            color: #475569;
        }

        .meta {
            margin-top: 12px;
            color: #64748b;
        }

        .summary {
            width: 100%;
            margin-top: 16px;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .summary-card {
            width: 25%;
            border: 1px solid #dbe2ea;
            background: #ffffff;
            padding: 10px 12px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        .summary-value {
            margin: 6px 0 4px;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .summary-copy {
            color: #64748b;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .section-copy {
            margin: 0 0 10px;
            color: #64748b;
        }

        .group-card {
            border: 1px solid #dbe2ea;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .group-head {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .group-title {
            margin: 0;
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        .report-table th {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            background: #ffffff;
        }

        .report-table tr:last-child td {
            border-bottom: 0;
        }

        .text-right {
            text-align: right;
        }

        .empty {
            padding: 18px 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    @php
        $filterLabel = $selectedAgeGroup?->name ?: 'Semua kelompok usia';
    @endphp

    <div class="cover">
        <p class="eyebrow">Laporan Pertandingan</p>
        <h1 class="title">Top Skor, Top Assist, dan Klasemen</h1>
        <p class="subtitle">Rekap resmi hasil pertandingan untuk kebutuhan monitoring dan distribusi laporan PDF.</p>
        <div class="meta">
            Filter kelompok usia: <strong>{{ $filterLabel }}</strong><br>
            Digenerate pada {{ $generatedAt->format('d M Y H:i') }} WIB
        </div>

        <table class="summary">
            <tr>
                @foreach ($reportSummary as $item)
                    <td class="summary-card">
                        <div class="summary-label">{{ $item['label'] }}</div>
                        <div class="summary-value">{{ $item['value'] }}</div>
                        <div class="summary-copy">{{ $item['hint'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Top Skor</h2>
        <p class="section-copy">Peringkat pencetak gol terbanyak dari pertandingan yang sudah selesai.</p>
        @forelse ($topScorers as $leaderboard)
            <div class="group-card">
                <div class="group-head">
                    <h3 class="group-title">{{ $leaderboard['age_group']?->name ?: '-' }}</h3>
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 64px;">Pos</th>
                            <th>Pemain</th>
                            <th>Klub</th>
                            <th class="text-right" style="width: 90px;">Gol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaderboard['rows'] as $row)
                            <tr>
                                <td>{{ $row['position'] }}</td>
                                <td>{{ $row['player_name'] }}</td>
                                <td>{{ $row['club_name'] }}</td>
                                <td class="text-right"><strong>{{ $row['total'] }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="group-card">
                <div class="empty">Belum ada data top skor untuk filter ini.</div>
            </div>
        @endforelse
    </div>

    <div class="section">
        <h2 class="section-title">Top Assist</h2>
        <p class="section-copy">Peringkat assist terbanyak dari report gol yang sudah tercatat.</p>
        @forelse ($topAssists as $leaderboard)
            <div class="group-card">
                <div class="group-head">
                    <h3 class="group-title">{{ $leaderboard['age_group']?->name ?: '-' }}</h3>
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 64px;">Pos</th>
                            <th>Pemain</th>
                            <th>Klub</th>
                            <th class="text-right" style="width: 90px;">Assist</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaderboard['rows'] as $row)
                            <tr>
                                <td>{{ $row['position'] }}</td>
                                <td>{{ $row['player_name'] }}</td>
                                <td>{{ $row['club_name'] }}</td>
                                <td class="text-right"><strong>{{ $row['total'] }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="group-card">
                <div class="empty">Belum ada data top assist untuk filter ini.</div>
            </div>
        @endforelse
    </div>

    <div class="section">
        <h2 class="section-title">Klasemen Liga</h2>
        <p class="section-copy">Klasemen hanya dihitung dari pertandingan format liga yang telah selesai.</p>
        @forelse ($standings as $standing)
            <div class="group-card">
                <div class="group-head">
                    <h3 class="group-title">{{ $standing['age_group']?->name ?: '-' }}</h3>
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">Pos</th>
                            <th>Klub</th>
                            <th style="width: 56px;">Main</th>
                            <th style="width: 56px;">M</th>
                            <th style="width: 56px;">S</th>
                            <th style="width: 56px;">K</th>
                            <th style="width: 56px;">GM</th>
                            <th style="width: 56px;">GK</th>
                            <th style="width: 56px;">SG</th>
                            <th style="width: 56px;">Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standing['rows'] as $row)
                            <tr>
                                <td>{{ $row['position'] }}</td>
                                <td>{{ $row['club_short_name'] }}</td>
                                <td>{{ $row['played'] }}</td>
                                <td>{{ $row['won'] }}</td>
                                <td>{{ $row['drawn'] }}</td>
                                <td>{{ $row['lost'] }}</td>
                                <td>{{ $row['goals_for'] }}</td>
                                <td>{{ $row['goals_against'] }}</td>
                                <td>{{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}</td>
                                <td><strong>{{ $row['points'] }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="group-card">
                <div class="empty">Belum ada klasemen liga untuk filter ini.</div>
            </div>
        @endforelse
    </div>
</body>
</html>

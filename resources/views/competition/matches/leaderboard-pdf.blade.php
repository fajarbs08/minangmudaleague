<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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

        .cover,
        .group-card {
            border: 1px solid #dbe2ea;
            background: #ffffff;
        }

        .cover {
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

        .subtitle,
        .meta,
        .section-copy,
        .empty {
            color: #64748b;
        }

        .subtitle {
            margin: 8px 0 0;
        }

        .meta {
            margin-top: 12px;
        }

        .section-title {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .section-copy {
            margin: 0 0 10px;
        }

        .group-card {
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
        }

        .report-table tr:last-child td {
            border-bottom: 0;
        }

        .text-right {
            text-align: right;
        }

        .empty {
            padding: 18px 12px;
        }
    </style>
</head>
<body>
    @php
        $filterLabel = $selectedAgeGroup?->name ?: 'Semua kelompok usia';
    @endphp

    <div class="cover">
        <p class="eyebrow">Laporan Pertandingan</p>
        <h1 class="title">{{ $title }}</h1>
        <p class="subtitle">{{ $description }}</p>
        <div class="meta">
            Filter kelompok usia: <strong>{{ $filterLabel }}</strong><br>
            Digenerate pada {{ $generatedAt->format('d M Y H:i') }} WIB
        </div>
    </div>

    <div>
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-copy">{{ $description }}</p>
        @forelse ($leaderboards as $leaderboard)
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
                            <th class="text-right" style="width: 90px;">{{ $metricLabel }}</th>
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
                <div class="empty">{{ $emptyMessage }}</div>
            </div>
        @endforelse
    </div>
</body>
</html>

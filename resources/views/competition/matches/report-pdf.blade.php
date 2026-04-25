<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pertandingan</title>
    @include('competition.matches.partials.report-pdf-theme')
</head>
<body>
    <div class="report-sheet">
        @include('competition.matches.partials.report-pdf-cover', [
            'documentTitle' => 'Laporan Pertandingan',
            'documentSubtitle' => 'Top Skor, Top Assist, dan Klasemen',
            'documentSource' => 'Dashboard Laporan Pertandingan',
        ])

        <div class="section-banner">
            <p class="section-banner-title">Top Skor</p>
            <p class="section-banner-copy">Peringkat pencetak gol terbanyak dari pertandingan yang sudah selesai.</p>
        </div>
        @include('competition.matches.partials.report-pdf-leaderboard-table', [
            'leaderboards' => $topScorers,
            'metricLabel' => 'Gol',
            'emptyMessage' => 'Belum ada data top skor untuk filter ini.',
        ])

        <div class="section-banner">
            <p class="section-banner-title">Top Assist</p>
            <p class="section-banner-copy">Peringkat assist terbanyak dari report gol yang sudah tercatat.</p>
        </div>
        @include('competition.matches.partials.report-pdf-leaderboard-table', [
            'leaderboards' => $topAssists,
            'metricLabel' => 'Assist',
            'emptyMessage' => 'Belum ada data top assist untuk filter ini.',
        ])

        <div class="section-banner">
            <p class="section-banner-title">Klasemen Liga</p>
            <p class="section-banner-copy">Klasemen hanya dihitung dari pertandingan format liga yang telah selesai.</p>
        </div>
        @include('competition.matches.partials.report-pdf-standings-table', [
            'standings' => $standings,
        ])
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @include('competition.matches.partials.report-pdf-theme')
</head>
<body>
    <div class="report-sheet">
        @include('competition.matches.partials.report-pdf-cover', [
            'documentTitle' => $title,
            'documentSubtitle' => $description,
            'documentSource' => 'Dashboard Laporan Pertandingan',
        ])

        <div class="section-banner">
            <p class="section-banner-title">{{ $title }}</p>
            <p class="section-banner-copy">{{ $description }}</p>
        </div>

        @include('competition.matches.partials.report-pdf-leaderboard-table', [
            'leaderboards' => $leaderboards,
            'metricLabel' => $metricLabel,
            'emptyMessage' => $emptyMessage,
        ])
    </div>
</body>
</html>

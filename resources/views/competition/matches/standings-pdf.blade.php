<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Klasemen</title>
    @include('competition.matches.partials.report-pdf-theme')
</head>
<body>
    <div class="report-sheet">
        @include('competition.matches.partials.report-pdf-cover', [
            'documentTitle' => 'Klasemen',
            'documentSubtitle' => 'Klasemen pertandingan format liga yang telah selesai',
            'documentSource' => 'Dashboard Laporan Pertandingan',
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bagan Knockout</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }

        .print-shell {
            display: grid;
            gap: 20px;
        }

        .print-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding: 12px 14px;
            border: 1px solid rgba(148, 163, 184, .22);
            border-radius: 14px;
            background: #eef4fb;
        }

        .print-head h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            color: #102a43;
        }

        .print-head p {
            margin: 4px 0 0;
            font-size: 10px;
            color: #486581;
        }

        .print-meta {
            display: grid;
            gap: 4px;
            text-align: right;
            font-size: 9px;
            color: #486581;
            text-transform: uppercase;
        }

        .print-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            margin-left: auto;
            padding: 4px 10px;
            border-radius: 999px;
            background: #d9e7f6;
            color: #102a43;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .print-group {
            page-break-inside: avoid;
        }

        .print-group + .print-group {
            page-break-before: always;
        }

        .print-group-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .print-group-head h2 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            color: #102a43;
        }

        .print-group-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(37, 99, 235, .1);
            color: #1d4ed8;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .report-bracket-shell {
            position: relative;
            min-height: var(--report-bracket-height, 680px);
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 14px;
            background: #fff;
            overflow: hidden;
        }

        .lap-bracket-host {
            min-height: var(--report-bracket-height, 680px);
            min-width: 0;
        }

        .lap-bracket-host[data-bracket-readonly="true"] .bt-match {
            cursor: default;
        }

        .lap-bracket-host .bracket-root {
            border: none;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        .lap-bracket-host .round-title {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: none;
            color: #0f172a;
        }

        .lap-bracket-host .bt-match {
            position: relative;
            display: grid;
            gap: 5px;
            width: 100%;
            padding: 7px 8px;
            border: 1.5px solid #c8d5e7;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .04);
            text-align: left;
        }

        .lap-bracket-host .bt-match.is-final,
        .lap-bracket-host .bt-match-main {
            width: 100%;
            display: grid;
            gap: 5px;
        }

        .lap-bracket-host .bt-match-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .lap-bracket-host .bt-match-ribbon {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            border-radius: 999px;
            background: #edf3ff;
            color: #0d2f67;
            border: 1px solid #c8d8f8;
            font-size: 7px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-bracket-host .bt-match-status {
            color: #64748b;
            font-size: 7px;
            font-weight: 700;
            text-align: right;
            max-width: 13ch;
        }

        .lap-bracket-host .bt-side {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 6px;
            align-items: center;
            padding-left: 0;
        }

        .lap-bracket-host .bt-side + .bt-side {
            padding-top: 4px;
            border-top: 1px solid #edf1f6;
        }

        .lap-bracket-host .bt-side-name {
            color: #0f172a;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: -.01em;
            line-height: 1.2;
        }

        .lap-bracket-host .bt-side-score {
            min-width: 20px;
            padding: 2px 5px;
            border-radius: 5px;
            background: #0d2f67;
            color: #ffffff;
            text-align: center;
            font-size: 8px;
            font-weight: 900;
        }

        .lap-bracket-host .bt-vs {
            display: flex;
            justify-content: center;
            align-items: center;
            color: #0f172a;
            font-size: 6px;
            font-weight: 900;
            letter-spacing: .12em;
        }

        .lap-bracket-host .bracket-root .round-wrapper:first-of-type .match-lines-area {
            left: 50% !important;
        }

        .lap-bracket-host .match-wrapper.is-empty-slot .match-lines-area {
            visibility: hidden;
        }

        .lap-bracket-host .match-wrapper.no-incoming-connector .match-lines-area {
            left: 50%;
        }

        .lap-bracket-host .bracket-root .round-wrapper:last-of-type .match-lines-area {
            right: 50% !important;
        }

        .lap-bracket-host .match-lines-area .line-wrapper {
            position: relative;
            color: #b7c9e8;
            border-color: #b7c9e8;
            filter: drop-shadow(0 3px 8px rgba(13, 47, 103, .08));
        }

        .lap-bracket-host .match-wrapper.odd .line-wrapper.upper {
            border-bottom-right-radius: 18px;
        }

        .lap-bracket-host .match-wrapper.even .line-wrapper.lower {
            border-top-right-radius: 18px;
        }

        .lap-bracket-host .match-wrapper.odd .line-wrapper.upper::after,
        .lap-bracket-host .match-wrapper.even .line-wrapper.lower::after {
            content: '';
            position: absolute;
            right: -1px;
            top: 50%;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, .96);
            transform: translate(50%, -50%);
        }

        .lap-bracket-host .match-wrapper.highlighted .line-wrapper {
            color: #0d2f67;
            border-color: #0d2f67;
        }

        .lap-bracket-host .match-body.live:not(:empty) {
            background: #fff8f8;
        }

        .print-empty {
            padding: 40px 20px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 14px;
        }
    </style>
    @vite(['resources/js/public-brackets.js'])
</head>
<body>
    <script>
        window.__BRACKET_PDF_READY = false;
        document.addEventListener('DOMContentLoaded', () => {
            const markReady = () => {
                const hosts = Array.from(document.querySelectorAll('[data-bracketry-host]'));
                if (!hosts.length) {
                    window.__BRACKET_PDF_READY = true;
                    return true;
                }

                const rendered = hosts.every((host) => host.querySelector('.bt-match'));
                if (rendered) {
                    window.__BRACKET_PDF_READY = true;
                }

                return rendered;
            };

            if (markReady()) {
                return;
            }

            const timer = window.setInterval(() => {
                if (markReady()) {
                    window.clearInterval(timer);
                }
            }, 120);
        }, { once: true });
    </script>

    <div class="print-shell">
        <header class="print-head">
            <div>
                <h1>Bagan Knockout</h1>
                <p>Versi cetak bracket knockout dari dashboard laporan pertandingan.</p>
            </div>
            <div class="print-meta">
                <span class="print-chip">{{ $selectedAgeGroup?->name ?: 'Semua kelompok usia' }}</span>
                <span>Digenerate: {{ $generatedAt->format('d M Y H:i') }} WIB</span>
                <span>Sumber: Dashboard Laporan Pertandingan</span>
            </div>
        </header>

        @forelse ($bracketryBrackets as $bracket)
            <section class="print-group">
                <div class="print-group-head">
                    <h2>{{ $bracket['age_group']?->name ?: '-' }}</h2>
                    <span class="print-group-badge">{{ $bracket['match_count'] }} Match</span>
                </div>

                <div
                    class="report-bracket-shell"
                    style="--report-bracket-height: {{ $bracket['layout']['desktop_height'] ?? 720 }}px;"
                >
                    <div class="lap-bracket-host" data-bracketry-host data-bracket-readonly="true">
                        <script type="application/json" data-bracketry-data>
                            @json($bracket['data'])
                        </script>
                    </div>
                </div>
            </section>
        @empty
            <div class="print-empty">Belum ada bracket knockout yang bisa ditampilkan untuk filter ini.</div>
        @endforelse
    </div>
</body>
</html>

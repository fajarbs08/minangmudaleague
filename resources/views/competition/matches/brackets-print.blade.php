@php
    $reportBrackets = $bracketryBrackets ?? collect();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bagan Knockout</title>
    @vite(['resources/js/public-brackets.js'])
    <style>
        @page {
            margin: 6mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #f8fafc;
            color: #0f172a;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: rgba(15, 23, 42, .94);
            color: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .18);
        }

        .print-toolbar strong {
            display: block;
            font-size: .98rem;
        }

        .print-toolbar p {
            margin: .2rem 0 0;
            color: rgba(255, 255, 255, .72);
            font-size: .84rem;
        }

        .print-toolbar-actions {
            display: flex;
            gap: .75rem;
        }

        .print-toolbar button {
            border: 0;
            border-radius: .8rem;
            padding: .8rem 1rem;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .print-toolbar button:last-child {
            background: rgba(255, 255, 255, .12);
        }

        .report-bracket-document {
            padding: 1rem;
        }

        .report-bracket-group + .report-bracket-group {
            margin-top: 1rem;
        }

        .report-bracket-sheet {
            width: min(100%, 1480px);
            margin: 0 auto;
            padding: 1rem;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 14px 40px rgba(15, 23, 42, .08);
        }

        .report-bracket-group-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            padding-bottom: .75rem;
            border-bottom: 1px solid rgba(148, 163, 184, .2);
            margin-bottom: 1rem;
        }

        .report-bracket-group-head h1 {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.1;
        }

        .report-bracket-group-head p {
            margin: .35rem 0 0;
            color: #64748b;
            font-size: .92rem;
        }

        .report-bracket-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, .1);
            color: #1d4ed8;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .report-bracket-shell {
            position: relative;
            min-height: var(--report-bracket-height, 560px);
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
        }

        .lap-bracket-host {
            min-height: var(--report-bracket-height, 560px);
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
            font-size: .82rem;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: none;
            color: #0f172a;
        }

        .lap-bracket-host .bt-match {
            position: relative;
            display: grid;
            gap: .3rem;
            width: 100%;
            padding: .58rem .62rem .62rem;
            border: 1px solid #e2e8f0;
            border-radius: .7rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
            text-align: left;
            transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .lap-bracket-host .bt-match.is-final {
            grid-template-columns: 1fr;
            justify-items: center;
            gap: .45rem;
        }

        .lap-bracket-host .bt-match.is-final .bt-match-main,
        .lap-bracket-host .bt-match-main {
            width: 100%;
            display: grid;
            gap: .3rem;
        }

        .lap-bracket-host .bt-match-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .55rem;
        }

        .lap-bracket-host .bt-match-ribbon {
            display: inline-flex;
            align-items: center;
            padding: .12rem .42rem;
            border-radius: 999px;
            background: #f2f6ff;
            color: #0d2f67;
            font-size: .6rem;
            font-weight: 800;
            letter-spacing: .08em;
        }

        .lap-bracket-host .bt-match-status {
            color: #64748b;
            font-size: .62rem;
            font-weight: 700;
            text-align: right;
            max-width: 12ch;
        }

        .lap-bracket-host .bt-side {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .5rem;
            align-items: center;
            padding-left: 0;
        }

        .lap-bracket-host .bt-side + .bt-side {
            padding-top: .32rem;
            border-top: 1px solid #edf1f6;
        }

        .lap-bracket-host .bt-side-name {
            color: #0f172a;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: -.01em;
            line-height: 1.15;
            word-break: break-word;
        }

        .lap-bracket-host .bt-side-score {
            min-width: 1.9rem;
            padding: .12rem .35rem;
            border-radius: .45rem;
            background: #f3f4f7;
            color: #0d2f67;
            text-align: center;
            font-size: .74rem;
            font-weight: 900;
        }

        .lap-bracket-host .bt-vs {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            color: #0f172a;
            font-size: .62rem;
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

        .report-bracket-empty {
            width: min(100%, 960px);
            margin: 2rem auto;
            padding: 3rem 1.5rem;
            border: 1px dashed #cbd5e1;
            border-radius: 1rem;
            background: #fff;
            text-align: center;
            color: #64748b;
        }

        @media print {
            html,
            body {
                background: #fff;
            }

            .print-toolbar {
                display: none !important;
            }

            .report-bracket-document {
                padding: 0;
            }

            .report-bracket-group + .report-bracket-group {
                margin-top: 0;
                break-before: page;
                page-break-before: always;
            }

            .report-bracket-sheet {
                width: 100%;
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                background: #fff;
                zoom: var(--group-print-scale, 1);
            }

            .report-bracket-shell {
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .report-bracket-group,
            .report-bracket-group-head,
            .report-bracket-shell,
            .lap-bracket-host,
            .bt-match,
            .match-wrapper,
            .round-wrapper {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <div>
            <strong>Bagan Knockout</strong>
            <p>Halaman ini khusus print browser dan tetap merender bracket asli dari bracketry. Rekomendasi: pilih A4 landscape dari dialog print.</p>
        </div>
        <div class="print-toolbar-actions">
            <button type="button" data-print-trigger>Cetak</button>
            <button type="button" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <main class="report-bracket-document">
        @forelse ($reportBrackets as $bracket)
            <section class="report-bracket-group">
                <div class="report-bracket-sheet" data-print-sheet>
                    <div class="report-bracket-group-head">
                        <div>
                            <h1>{{ $bracket['age_group']?->name ?: '-' }}</h1>
                            <p>Jalur knockout berdasarkan ronde, slot bracket, dan hasil pertandingan yang tersimpan.</p>
                        </div>
                        <span class="report-bracket-badge">{{ $bracket['match_count'] }} match</span>
                    </div>

                    <div
                        class="report-bracket-shell"
                        style="--report-bracket-height: {{ max(460, min(640, (int) round(($bracket['layout']['desktop_height'] ?? 720) * 0.72))) }}px;"
                    >
                        <div class="lap-bracket-host" data-bracketry-host data-bracket-readonly="true" data-bracket-profile="print">
                            <script type="application/json" data-bracketry-data>
                                @json($bracket['data'])
                            </script>
                        </div>
                    </div>
                </div>
            </section>
        @empty
            <div class="report-bracket-empty">Belum ada bracket knockout yang bisa ditampilkan untuk filter ini.</div>
        @endforelse
    </main>

    <script>
        (() => {
            const sheets = Array.from(document.querySelectorAll('[data-print-sheet]'));
            const trigger = document.querySelector('[data-print-trigger]');
            const mmToPx = (mm) => (mm * 96) / 25.4;
            const printableWidth = mmToPx(297 - 12);
            const printableHeight = mmToPx(210 - 12);

            const applyPrintScale = () => {
                sheets.forEach((sheet) => {
                    sheet.style.removeProperty('--group-print-scale');

                    const width = sheet.scrollWidth || sheet.offsetWidth;
                    const height = sheet.scrollHeight || sheet.offsetHeight;

                    if (!width || !height) {
                        sheet.style.setProperty('--group-print-scale', '1');
                        return;
                    }

                    const widthScale = printableWidth / width;
                    const heightScale = printableHeight / height;
                    const scale = Math.max(0.42, Math.min(1, widthScale, heightScale));

                    sheet.style.setProperty('--group-print-scale', String(scale));
                });
            };

            const queuePrint = () => {
                applyPrintScale();
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        window.setTimeout(() => window.print(), 180);
                    });
                });
            };

            window.addEventListener('beforeprint', applyPrintScale);
            window.addEventListener('resize', applyPrintScale, { passive: true });
            window.addEventListener('load', queuePrint, { once: true });

            trigger?.addEventListener('click', () => {
                applyPrintScale();
                window.print();
            });
        })();
    </script>
</body>
</html>

@php
    $reportBrackets = collect($bracketryBrackets ?? []);
    $bracketryScriptPath = base_path('node_modules/bracketry/dist/esm/index.js');
    $bracketryScript = is_file($bracketryScriptPath)
        ? file_get_contents($bracketryScriptPath)
        : null;

    if (is_string($bracketryScript)) {
        $bracketryScript = str_replace('export{rt as createBracket};', 'window.createBracket = rt;', $bracketryScript);
    }

    $filterLabel = $selectedAgeGroup?->name ?: 'Semua kelompok usia';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bagan Knockout</title>
    @include('competition.matches.partials.report-pdf-theme')
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }

        .bracket-pdf-group + .bracket-pdf-group {
            page-break-before: always;
        }

        .bracket-pdf-master {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            pointer-events: none;
            z-index: -1;
        }

        .bracket-pdf-slice + .bracket-pdf-slice {
            page-break-before: always;
        }

        .bracket-pdf-fallback + .bracket-pdf-fallback {
            page-break-before: always;
        }

        .bracket-pdf-frame {
            border: 1px solid #334e68;
            background: #ffffff;
        }

        .bracket-pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding: 10px 12px 8px;
            border-bottom: 1px solid #cbd5e1;
            background: #eef4fb;
        }

        .bracket-pdf-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            color: #102a43;
            line-height: 1.1;
        }

        .bracket-pdf-copy {
            margin: 4px 0 0;
            font-size: 9px;
            line-height: 1.35;
            color: #486581;
        }

        .bracket-pdf-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 170px;
            text-align: right;
            color: #486581;
            font-size: 8px;
            text-transform: uppercase;
        }

        .bracket-pdf-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            margin-left: auto;
            padding: 4px 8px;
            border-radius: 999px;
            background: #d9e7f6;
            color: #102a43;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .bracket-pdf-viewport {
            position: relative;
            height: 146mm;
            overflow: hidden;
            background: #ffffff;
        }

        .bracket-pdf-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transform-origin: top left;
        }

        .lap-bracket-host {
            min-height: var(--report-bracket-height, 520px);
            min-width: 0;
            width: 100%;
        }

        .lap-bracket-host .bracket-root {
            border: none;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        .lap-bracket-host .round-title {
            font-size: 8px;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: none;
            color: #0f172a;
        }

        .lap-bracket-host .bt-match {
            position: relative;
            display: grid;
            gap: 4px;
            width: 100%;
            padding: 6px 7px;
            border: 2px solid #b7c9e8;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
            text-align: left;
        }

        .lap-bracket-host .bt-match-main {
            width: 100%;
            display: grid;
            gap: 4px;
        }

        .lap-bracket-host .bt-match-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
        }

        .lap-bracket-host .bt-match-ribbon {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            border-radius: 999px;
            border: 1px solid #0d2f67;
            background: #0d2f67;
            color: #ffffff;
            font-size: 6.4px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .lap-bracket-host .bt-match-status {
            padding: 2px 5px;
            border-radius: 999px;
            background: #edf3ff;
            color: #0d2f67;
            font-size: 6px;
            font-weight: 700;
            text-align: right;
            max-width: none;
        }

        .lap-bracket-host .bt-side {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 5px;
            align-items: center;
        }

        .lap-bracket-host .bt-side + .bt-side {
            padding-top: 4px;
            border-top: 1px solid #eef2f7;
        }

        .lap-bracket-host .bt-side-name {
            color: #0f172a;
            font-size: 7.2px;
            font-weight: 800;
            line-height: 1.2;
        }

        .lap-bracket-host .bt-side-score {
            min-width: 20px;
            padding: 2px 5px;
            border-radius: 5px;
            background: #fe5900;
            color: #ffffff;
            text-align: center;
            font-size: 7px;
            font-weight: 900;
        }

        .lap-bracket-host .bt-vs {
            display: flex;
            justify-content: center;
            align-items: center;
            color: #0f172a;
            font-size: 5px;
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
        }

        .lap-bracket-host .match-wrapper.odd .line-wrapper.upper {
            border-bottom-right-radius: 14px;
        }

        .lap-bracket-host .match-wrapper.even .line-wrapper.lower {
            border-top-right-radius: 14px;
        }

        .lap-bracket-host .match-wrapper.odd .line-wrapper.upper::after,
        .lap-bracket-host .match-wrapper.even .line-wrapper.lower::after {
            content: '';
            position: absolute;
            right: -1px;
            top: 50%;
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .96);
            transform: translate(50%, -50%);
        }

        .bracket-pdf-fallback {
            padding: 14px 16px;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <script>window.__BRACKET_PDF_READY = false;</script>
    @forelse ($reportBrackets as $bracket)
        <div class="bracket-pdf-group">
            <div class="bracket-pdf-master">
                <div class="lap-bracket-host" data-pdf-bracket-master data-bracket-readonly="true" data-page-title="{{ $bracket['age_group']?->name ?: 'Kelompok Usia' }}" style="--report-bracket-height: {{ $bracket['layout']['desktop_height'] ?? 520 }}px;">
                    <script type="application/json" data-bracketry-data>
                        @json($bracket['data'])
                    </script>
                </div>
            </div>

            <div class="bracket-pdf-slices" data-pdf-bracket-slices></div>
            <div class="bracket-pdf-fallback" data-pdf-bracket-fallback>
                Gagal menyiapkan tree bracket untuk PDF.
            </div>
        </div>
    @empty
        <div class="bracket-pdf-frame bracket-pdf-fallback">
            Belum ada bracket knockout yang bisa ditampilkan untuk filter ini.
        </div>
    @endforelse

    @if (filled($bracketryScript))
        <script>{!! $bracketryScript !!}</script>
        <script>
            (() => {
                const createBracket = window.createBracket;

                if (typeof createBracket !== 'function') {
                    return;
                }

                const escapeHtml = (value = '') => String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const toMatchKey = (roundIndex, order) => `${roundIndex}:${order}`;

                const bracketOptions = (roundCount) => ({
                    width: '100%',
                    height: '100%',
                    rootBorderColor: '#d9e2ec',
                    wrapperBorderColor: '#d9e2ec',
                    verticalScrollMode: 'visible',
                    scrollButtonsPosition: 'hidden',
                    showScrollbar: false,
                    rootBgColor: '#ffffff',
                    visibleRoundsCount: roundCount,
                    displayWholeRounds: true,
                    useClassicalLayout: true,
                    disableHighlight: false,
                    mainVerticalPadding: 6,
                    roundTitlesVerticalPadding: 8,
                    roundTitlesBorderColor: '#d9e2ec',
                    roundTitleColor: '#102a43',
                    hoveredMatchBorderColor: '#1d4ed8',
                    matchStatusBgColor: '#f3f4f7',
                    navButtonsPosition: 'hidden',
                    rootFontFamily: 'DejaVu Sans, sans-serif',
                    roundTitlesFontFamily: 'DejaVu Sans, sans-serif',
                    roundTitlesFontSize: 8,
                    matchTextColor: '#102a43',
                    matchFontSize: 8,
                    playerTitleFontFamily: 'DejaVu Sans, sans-serif',
                    highlightedPlayerTitleColor: '#1d4ed8',
                    scoreFontFamily: 'DejaVu Sans, sans-serif',
                    connectionLinesWidth: 2,
                    connectionLinesColor: '#b7c9e8',
                    highlightedConnectionLinesColor: '#1d4ed8',
                    matchMaxWidth: 140,
                    matchMinVerticalGap: 10,
                    matchHorMargin: 12,
                    matchAxisMargin: 4,
                    oneSidePlayersGap: 4,
                    liveMatchBorderColor: '#e41b23',
                    liveMatchBgColor: '#fff8f8',
                    distanceBetweenScorePairs: 4,
                    getMatchElement: () => null,
                    getPlayerTitleHTML: (player) => `<span class="bracketry-team-title">${escapeHtml(player.title)}</span>`,
                    getScoresHTML: () => '',
                    getMatchTopHTML: () => '',
                    getMatchBottomHTML: () => '',
                    getEntryStatusHTML: () => '',
                    getNationalityHTML: () => '',
                });

                const hasIncomingMatch = (match, matchByKey) => {
                    if (!Number.isInteger(match?.roundIndex) || match.roundIndex <= 0 || !Number.isInteger(match?.order)) {
                        return false;
                    }

                    return [match.order * 2, (match.order * 2) + 1]
                        .some((previousOrder) => matchByKey.has(toMatchKey(match.roundIndex - 1, previousOrder)));
                };

                const markDirectEntryMatches = (host, matches, matchByKey) => {
                    matches
                        .filter((match) => Number.isInteger(match?.roundIndex) && match.roundIndex > 0 && !hasIncomingMatch(match, matchByKey))
                        .forEach((match) => {
                            const wrapper = host.querySelector(`.round-wrapper[round-index="${match.roundIndex}"] .match-wrapper[match-order="${match.order}"]`);
                            wrapper?.classList.add('no-incoming-connector');
                        });
                };

                const markEmptySlots = (host) => {
                    host.querySelectorAll('.match-wrapper').forEach((wrapper) => {
                        if (!wrapper.querySelector('.match-body')) {
                            wrapper.classList.add('is-empty-slot');
                        }
                    });
                };

                const matchElementFactoryBuilder = (data, contestants, matchByKey) => (roundIndex, matchOrder) => {
                    const match = matchByKey.get(toMatchKey(roundIndex, matchOrder));

                    if (!match) {
                        return null;
                    }

                    const topSide = match.sides?.[0];
                    const bottomSide = match.sides?.[1];
                    const topContestant = topSide?.contestantId ? contestants[topSide.contestantId] : null;
                    const bottomContestant = bottomSide?.contestantId ? contestants[bottomSide.contestantId] : null;
                    const topTitle = topContestant?.players?.[0]?.title || topSide?.title || 'Menunggu';
                    const bottomTitle = bottomContestant?.players?.[0]?.title || bottomSide?.title || 'Menunggu';
                    const topScore = topSide?.scores?.[0]?.mainScore;
                    const bottomScore = bottomSide?.scores?.[0]?.mainScore;
                    const status = match.matchStatus ? `<div class="bt-match-status">${escapeHtml(match.matchStatus)}</div>` : '';

                    const el = document.createElement('article');
                    el.className = 'bt-match';
                    el.innerHTML = `<div class="bt-match-main">
                        <div class="bt-match-head">
                            <div class="bt-match-ribbon">${escapeHtml(data.rounds?.[roundIndex]?.name || '')}</div>
                            ${status}
                        </div>
                        <div class="bt-side is-home">
                            <div class="bt-side-name">${escapeHtml(topTitle)}</div>
                            <div class="bt-side-score">${topScore ?? '–'}</div>
                        </div>
                        <div class="bt-vs">VS</div>
                        <div class="bt-side is-away">
                            <div class="bt-side-name">${escapeHtml(bottomTitle)}</div>
                            <div class="bt-side-score">${bottomScore ?? '–'}</div>
                        </div>
                    </div>`;

                    return el;
                };

                const buildHeader = (title, index, total) => `
                    <div class="bracket-pdf-frame">
                        <div class="bracket-pdf-header">
                            <div>
                                <h1 class="bracket-pdf-title">Bagan Knockout ${escapeHtml(title)}</h1>
                                <p class="bracket-pdf-copy">Tree bracket cetak · halaman ${index} dari ${total}</p>
                            </div>
                            <div class="bracket-pdf-meta">
                                <span class="bracket-pdf-chip">${escapeHtml(@json($filterLabel))}</span>
                                <span>Digenerate: ${escapeHtml(@json($generatedAt->format('d M Y H:i').' WIB'))}</span>
                                <span>Sumber: Dashboard Laporan Pertandingan</span>
                            </div>
                        </div>
                    </div>
                `;

                let renderedGroups = 0;
                document.querySelectorAll('[data-pdf-bracket-master]').forEach((host) => {
                    const script = host.querySelector('[data-bracketry-data]');
                    const group = host.closest('.bracket-pdf-group');
                    const slicesContainer = group?.querySelector('[data-pdf-bracket-slices]');
                    const fallback = group?.querySelector('[data-pdf-bracket-fallback]');
                    const master = group?.querySelector('.bracket-pdf-master');

                    if (!script || !slicesContainer) {
                        return;
                    }

                    let data;

                    try {
                        data = JSON.parse(script.textContent || '{}');
                    } catch {
                        return;
                    }

                    const matches = Array.isArray(data.matches) ? data.matches : [];
                    const contestants = data.contestants || {};
                    const matchByKey = new Map(matches.map((match) => [toMatchKey(match.roundIndex, match.order), match]));
                    const roundCount = Array.isArray(data.rounds) ? data.rounds.length : 4;

                    script.remove();

                    createBracket(data, host, {
                        ...bracketOptions(roundCount),
                        getMatchElement: matchElementFactoryBuilder(data, contestants, matchByKey),
                        getMatchTopHTML: () => '',
                        getMatchBottomHTML: () => '',
                        getScoresHTML: () => '',
                        getPlayerTitleHTML: () => '',
                    });

                    markEmptySlots(host);
                    markDirectEntryMatches(host, matches, matchByKey);

                    const root = host.querySelector('.bracket-root');
                    if (!root) {
                        return;
                    }

                    const viewportProbe = document.createElement('div');
                    viewportProbe.className = 'bracket-pdf-frame bracket-pdf-viewport';
                    slicesContainer.appendChild(viewportProbe);

                    const availableWidth = viewportProbe.getBoundingClientRect().width;
                    const availableHeight = viewportProbe.getBoundingClientRect().height;
                    viewportProbe.remove();

                    const rootRect = root.getBoundingClientRect();
                    const rawWidth = rootRect.width;
                    const rawHeight = rootRect.height;
                    const pageCount = 1;
                    const pageOffsets = [0];
                    const scale = Math.min(availableWidth / rawWidth, availableHeight / rawHeight);
                    const scaledWidth = rawWidth * scale;
                    const rootMarkup = root.outerHTML;
                    const title = host.dataset.pageTitle || 'Kelompok Usia';
                    const leftOffset = Math.max(0, (availableWidth - scaledWidth) / 2);

                    slicesContainer.innerHTML = pageOffsets.map((offset, pageIndex) => `
                        <div class="bracket-pdf-slice">
                            ${buildHeader(title, pageIndex + 1, pageCount)}
                            <div class="bracket-pdf-frame bracket-pdf-viewport">
                                <div class="bracket-pdf-canvas" style="width:${rawWidth}px; left:${leftOffset}px; transform: translateY(-${offset / scale}px) scale(${scale});">
                                    <div class="lap-bracket-host">${rootMarkup}</div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    fallback?.remove();
                    master?.remove();
                    renderedGroups += 1;
                });

                window.__BRACKET_PDF_READY = renderedGroups > 0 || document.querySelectorAll('.bracket-pdf-group').length === 0;
            })();
        </script>
    @endif
</body>
</html>

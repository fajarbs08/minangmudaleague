import { createBracket } from 'bracketry';

const escapeHtml = (value = '') => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const toMatchKey = (roundIndex, order) => `${roundIndex}:${order}`;

const navArrowHtml = (direction) => {
    const arrows = {
        left: '&#8249;',
        right: '&#8250;',
        up: '&#8593;',
        down: '&#8595;',
    };

    return `<span class="bt-nav-arrow" aria-hidden="true">${arrows[direction] || arrows.right}</span>`;
};

const activateBracketAgeTab = (targetKey, updateHash = true) => {
    const tabs = Array.from(document.querySelectorAll('[data-bracket-age-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-bracket-age-panel]'));

    if (!tabs.length || !panels.length) {
        return;
    }

    const nextKey = tabs.some((tab) => tab.dataset.bracketAgeTab === targetKey)
        ? targetKey
        : tabs[0].dataset.bracketAgeTab;

    tabs.forEach((tab) => {
        const isActive = tab.dataset.bracketAgeTab === nextKey;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    panels.forEach((panel) => {
        panel.hidden = panel.dataset.bracketAgePanel !== nextKey;
    });

    if (updateHash) {
        const url = new URL(window.location.href);
        url.hash = nextKey;
        window.history.replaceState({}, '', url);
    }
};

const initBracketAgeTabs = () => {
    const tabs = Array.from(document.querySelectorAll('[data-bracket-age-tab]'));

    if (!tabs.length) {
        return;
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activateBracketAgeTab(tab.dataset.bracketAgeTab);
        });
    });

    const hashKey = window.location.hash.replace(/^#/, '');
    const initialKey = hashKey || tabs.find((tab) => tab.classList.contains('is-active'))?.dataset.bracketAgeTab || tabs[0].dataset.bracketAgeTab;
    activateBracketAgeTab(initialKey, false);

    window.addEventListener('hashchange', () => {
        const nextHashKey = window.location.hash.replace(/^#/, '');
        if (nextHashKey) {
            activateBracketAgeTab(nextHashKey, false);
        }
    });
};

const bracketOptions = (roundCount) => {
    const isMobile = window.matchMedia('(max-width: 767px)').matches;
    const visibleRoundsCount = isMobile ? 1 : Math.min(4, roundCount);
    const hasNavigation = visibleRoundsCount < roundCount;

    return {
        width: '100%',
        height: '100%',
        rootBorderColor: '#e7e9f0',
        wrapperBorderColor: '#e7e9f0',
        verticalScrollMode: 'native',
        scrollButtonsPosition: 'overMatches',
        showScrollbar: false,
        scrollbarWidth: 8,
        scrollbarColor: '#cbd5e1',
        scrollButtonSvgColor: '#667085',
        scrollGutterBorderColor: '#e7e9f0',
        scrollButtonPadding: '0',
        scrollUpButtonHTML: navArrowHtml('up'),
        scrollDownButtonHTML: navArrowHtml('down'),
        resetScrollOnNavigation: true,
        buttonScrollAmount: 1,
        scrollButtonArrowSize: 18,
        rootBgColor: '#ffffff',
        mainVerticalPadding: isMobile ? 12 : 16,
        visibleRoundsCount,
        displayWholeRounds: true,
        useClassicalLayout: true,
        disableHighlight: false,
        roundTitlesVerticalPadding: isMobile ? 14 : 18,
        roundTitlesBorderColor: '#e7e9f0',
        roundTitleColor: '#10131f',
        hoveredMatchBorderColor: '#e41b23',
        matchStatusBgColor: '#f3f4f7',
        navButtonsPosition: hasNavigation ? 'overMatches' : 'hidden',
        navButtonsTopDistance: isMobile ? '48%' : '44%',
        navGutterBorderColor: '#e7e9f0',
        navButtonArrowSize: 18,
        navButtonSvgColor: '#667085',
        leftNavButtonHTML: navArrowHtml('left'),
        rightNavButtonHTML: navArrowHtml('right'),
        navButtonPadding: '0',
        rootFontFamily: 'inherit',
        roundTitlesFontFamily: 'inherit',
        roundTitlesFontSize: isMobile ? 12 : 13,
        matchTextColor: '#10131f',
        matchFontSize: isMobile ? 14 : 15,
        playerTitleFontFamily: 'inherit',
        highlightedPlayerTitleColor: '#e41b23',
        scoreFontFamily: 'inherit',
        connectionLinesWidth: 3,
        connectionLinesColor: '#b7c9e8',
        highlightedConnectionLinesColor: '#e41b23',
        matchMaxWidth: isMobile ? 218 : 244,
        matchMinVerticalGap: isMobile ? 10 : 14,
        matchHorMargin: isMobile ? 12 : 18,
        matchAxisMargin: isMobile ? 8 : 10,
        oneSidePlayersGap: 8,
        liveMatchBorderColor: '#e41b23',
        liveMatchBgColor: '#fff8f8',
        distanceBetweenScorePairs: 10,
        getMatchElement: (roundIndex, matchOrder) => null,
        getPlayerTitleHTML: (player) => `<span class="bracketry-team-title">${escapeHtml(player.title)}</span>`,
        getScoresHTML: () => '',
        getMatchTopHTML: () => '',
        getMatchBottomHTML: () => '',
        getEntryStatusHTML: () => '',
        getNationalityHTML: () => '',
    };
};

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
            const wrapper = host.querySelector(
                `.round-wrapper[round-index="${match.roundIndex}"] .match-wrapper[match-order="${match.order}"]`,
            );

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

const renderBracket = (host) => {
    const script = host.querySelector('[data-bracketry-data]');
    if (!script) return;

    let data;

    try {
        data = JSON.parse(script.textContent || '{}');
    } catch {
        return;
    }

    script.remove();

    const matches = Array.isArray(data.matches) ? data.matches : [];
    const contestants = data.contestants || {};
    const matchByKey = new Map(matches.map((match) => [toMatchKey(match.roundIndex, match.order), match]));
    const roundCount = Array.isArray(data.rounds) ? data.rounds.length : 4;
    const isReadOnly = host.dataset.bracketReadonly === 'true';
    const matchElementFactory = (roundIndex, matchOrder) => {
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
        const isFinalRound = roundIndex === roundCount - 1;
        const winnerLine = topSide?.isWinner || bottomSide?.isWinner ? 'is-live' : '';
        const status = match.matchStatus ? `<div class="bt-match-status">${escapeHtml(match.matchStatus)}</div>` : '';

        const detailUrl = match.detail?.detail_url || '';
        const el = document.createElement(isReadOnly ? 'article' : (detailUrl ? 'a' : 'button'));
        if (!isReadOnly && detailUrl) {
            el.href = detailUrl;
        } else if (!isReadOnly) {
            el.type = 'button';
        }
        el.className = `bt-match ${winnerLine} ${isFinalRound ? 'is-final' : ''}`.trim();
        el.setAttribute('aria-label', `${match.detail?.round_label || 'Bracket'}: ${topTitle} vs ${bottomTitle}`);
        el.innerHTML = isFinalRound
            ? `<div class="bt-match-main">
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
            </div>`
            : `
            <div class="bt-match-main">
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

    createBracket(data, host, {
        ...bracketOptions(Array.isArray(data.rounds) ? data.rounds.length : 4),
        getMatchElement: matchElementFactory,
        getMatchTopHTML: () => '',
        getMatchBottomHTML: () => '',
        getScoresHTML: () => '',
        getPlayerTitleHTML: () => '',
    });

    markEmptySlots(host);
    markDirectEntryMatches(host, matches, matchByKey);
};

const init = () => {
    document.querySelectorAll('[data-bracketry-host]').forEach(renderBracket);
    initBracketAgeTabs();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
} else {
    init();
}

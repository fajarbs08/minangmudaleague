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

const bracketDataCache = new WeakMap();
const bracketRenderState = new WeakMap();
const bracketMobileControlBindings = new WeakMap();
let responsiveBracketsResizeFrame = null;

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

    window.requestAnimationFrame(() => {
        panels
            .filter((panel) => !panel.hidden)
            .forEach((panel) => {
                panel.querySelectorAll('[data-bracketry-host]').forEach((host) => {
                    host.dispatchEvent(new CustomEvent('bracket-mobile-controls:refresh'));
                });
            });
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

const isMobileViewport = () => window.matchMedia('(max-width: 767px)').matches;

const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

const teardownMobileRoundControls = (host) => {
    host.removeAttribute('data-bracket-mobile-controls');

    const controls = host.parentElement?.nextElementSibling;
    if (controls?.classList.contains('bracket-mobile-controls')) {
        controls.remove();
    }

    const binding = bracketMobileControlBindings.get(host);
    if (!binding) {
        return;
    }

    binding.scroller?.removeEventListener('scroll', binding.onScroll);
    binding.prevAction?.removeEventListener('click', binding.onPrev);
    binding.nextAction?.removeEventListener('click', binding.onNext);
    host.removeEventListener('bracket-mobile-controls:refresh', binding.onRefresh);
    bracketMobileControlBindings.delete(host);
};

const bracketOptions = (roundCount, profile = 'default', hostWidth = window.innerWidth) => {
    const isPrintProfile = profile === 'print';
    const isMobile = !isPrintProfile && window.matchMedia('(max-width: 767px)').matches;
    const visibleRoundsCount = isPrintProfile ? roundCount : (isMobile ? 1 : Math.min(4, roundCount));
    const hasNavigation = !isPrintProfile && visibleRoundsCount < roundCount;
    const mobileMatchMaxWidth = Math.max(220, Math.min(320, hostWidth - 72));

    return {
        width: '100%',
        height: '100%',
        rootBorderColor: '#e7e9f0',
        wrapperBorderColor: '#e7e9f0',
        verticalScrollMode: 'native',
        scrollButtonsPosition: isPrintProfile || isMobile ? 'hidden' : 'overMatches',
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
        scrollButtonArrowSize: isMobile ? 22 : 18,
        rootBgColor: '#ffffff',
        mainVerticalPadding: isPrintProfile ? 8 : (isMobile ? 12 : 16),
        visibleRoundsCount,
        displayWholeRounds: true,
        useClassicalLayout: true,
        disableHighlight: false,
        roundTitlesVerticalPadding: isPrintProfile ? 10 : (isMobile ? 14 : 18),
        roundTitlesBorderColor: '#e7e9f0',
        roundTitleColor: '#10131f',
        hoveredMatchBorderColor: '#e41b23',
        matchStatusBgColor: '#f3f4f7',
        navButtonsPosition: isMobile ? 'hidden' : (hasNavigation ? 'overMatches' : 'hidden'),
        navButtonsTopDistance: isMobile ? '48%' : '44%',
        navGutterBorderColor: '#e7e9f0',
        navButtonArrowSize: isMobile ? 22 : 18,
        navButtonSvgColor: '#667085',
        leftNavButtonHTML: navArrowHtml('left'),
        rightNavButtonHTML: navArrowHtml('right'),
        navButtonPadding: '0',
        rootFontFamily: 'inherit',
        roundTitlesFontFamily: 'inherit',
        roundTitlesFontSize: isPrintProfile ? 11 : (isMobile ? 12 : 13),
        matchTextColor: '#10131f',
        matchFontSize: isPrintProfile ? 13 : (isMobile ? 14 : 15),
        playerTitleFontFamily: 'inherit',
        highlightedPlayerTitleColor: '#e41b23',
        scoreFontFamily: 'inherit',
        connectionLinesWidth: isPrintProfile ? 2 : 3,
        connectionLinesColor: '#b7c9e8',
        highlightedConnectionLinesColor: '#e41b23',
        matchMaxWidth: isPrintProfile ? 264 : (isMobile ? mobileMatchMaxWidth : 244),
        matchMinVerticalGap: isPrintProfile ? 8 : (isMobile ? 10 : 14),
        matchHorMargin: isPrintProfile ? 10 : (isMobile ? 8 : 18),
        matchAxisMargin: isPrintProfile ? 6 : (isMobile ? 6 : 10),
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

const mountMobileRoundControls = (host, roundNames) => {
    if (!isMobileViewport() || host.dataset.bracketProfile === 'print' || roundNames.length <= 1) {
        teardownMobileRoundControls(host);
        return;
    }

    const root = host.querySelector('.bracket-root');
    const matchesScroller = root?.querySelector('.matches-scroller');
    const roundTitlesScroller = root?.querySelector('.round-titles-grid-item');

    if (!root || !matchesScroller || !roundTitlesScroller) {
        return;
    }

    let controls = host.parentElement?.nextElementSibling;

    if (!controls || !controls.classList.contains('bracket-mobile-controls')) {
        controls = document.createElement('div');
        controls.className = 'bracket-mobile-controls';
        controls.innerHTML = `
            <button type="button" class="bracket-mobile-controls__button" data-round-nav="prev" aria-label="Ronde sebelumnya">
                <span aria-hidden="true">&#8249;</span>
            </button>
            <div class="bracket-mobile-controls__label" aria-live="polite">
                <div class="bracket-mobile-controls__eyebrow">Navigasi ronde</div>
                <div class="bracket-mobile-controls__title">-</div>
            </div>
            <button type="button" class="bracket-mobile-controls__button" data-round-nav="next" aria-label="Ronde berikutnya">
                <span aria-hidden="true">&#8250;</span>
            </button>
        `;

        host.parentElement?.after(controls);
    }

    host.dataset.bracketMobileControls = 'true';

    const prevAction = controls.querySelector('[data-round-nav="prev"]');
    const nextAction = controls.querySelector('[data-round-nav="next"]');
    const labelTitle = controls.querySelector('.bracket-mobile-controls__title');
    const labelEyebrow = controls.querySelector('.bracket-mobile-controls__eyebrow');

    const previousBinding = bracketMobileControlBindings.get(host);
    if (previousBinding) {
        previousBinding.scroller?.removeEventListener('scroll', previousBinding.onScroll);
        previousBinding.prevAction?.removeEventListener('click', previousBinding.onPrev);
        previousBinding.nextAction?.removeEventListener('click', previousBinding.onNext);
        host.removeEventListener('bracket-mobile-controls:refresh', previousBinding.onRefresh);
    }

    const updateControls = () => {
        const pageWidth = matchesScroller.clientWidth || 1;
        const roundIndex = clamp(Math.round(matchesScroller.scrollLeft / pageWidth), 0, roundNames.length - 1);

        if (labelTitle) {
            labelTitle.textContent = roundNames[roundIndex] || `Ronde ${roundIndex + 1}`;
        }

        if (labelEyebrow) {
            labelEyebrow.textContent = `Ronde ${roundIndex + 1} dari ${roundNames.length}`;
        }

        if (prevAction instanceof HTMLButtonElement) {
            prevAction.disabled = roundIndex === 0;
        }

        if (nextAction instanceof HTMLButtonElement) {
            nextAction.disabled = roundIndex >= roundNames.length - 1;
        }
    };

    const scrollToRound = (targetIndex) => {
        const roundIndex = clamp(targetIndex, 0, roundNames.length - 1);
        const matchesPageWidth = matchesScroller.clientWidth || 1;
        const titlePageWidth = roundTitlesScroller.clientWidth || matchesPageWidth;

        matchesScroller.scrollTo({
            left: roundIndex * matchesPageWidth,
            behavior: 'smooth',
        });

        roundTitlesScroller.scrollTo({
            left: roundIndex * titlePageWidth,
            behavior: 'smooth',
        });
    };

    const queueUpdate = () => {
        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(updateControls);
        });
    };

    const onPrev = () => {
        const currentIndex = clamp(Math.round(matchesScroller.scrollLeft / (matchesScroller.clientWidth || 1)), 0, roundNames.length - 1);
        scrollToRound(currentIndex - 1);
        queueUpdate();
    };

    const onNext = () => {
        const currentIndex = clamp(Math.round(matchesScroller.scrollLeft / (matchesScroller.clientWidth || 1)), 0, roundNames.length - 1);
        scrollToRound(currentIndex + 1);
        queueUpdate();
    };

    const onRefresh = () => {
        queueUpdate();
    };

    prevAction?.addEventListener('click', onPrev);
    nextAction?.addEventListener('click', onNext);
    matchesScroller.addEventListener('scroll', updateControls, { passive: true });
    host.addEventListener('bracket-mobile-controls:refresh', onRefresh);

    bracketMobileControlBindings.set(host, {
        scroller: matchesScroller,
        prevAction,
        nextAction,
        onPrev,
        onNext,
        onScroll: updateControls,
        onRefresh,
    });

    queueUpdate();
};

const renderBracket = (host, { force = false } = {}) => {
    let data = bracketDataCache.get(host);

    if (!data) {
        const script = host.querySelector('[data-bracketry-data]');
        if (!script) {
            return;
        }

        try {
            data = JSON.parse(script.textContent || '{}');
        } catch {
            return;
        }

        bracketDataCache.set(host, data);
    }

    const matches = Array.isArray(data.matches) ? data.matches : [];
    const contestants = data.contestants || {};
    const matchByKey = new Map(matches.map((match) => [toMatchKey(match.roundIndex, match.order), match]));
    const roundCount = Array.isArray(data.rounds) ? data.rounds.length : 4;
    const isReadOnly = host.dataset.bracketReadonly === 'true';
    const bracketProfile = host.dataset.bracketProfile || 'default';
    const viewportMode = bracketProfile === 'print' ? 'print' : (isMobileViewport() ? 'mobile' : 'desktop');
    const previousMode = bracketRenderState.get(host)?.mode;

    if (!force && previousMode === viewportMode && host.querySelector('.bracket-root')) {
        if (viewportMode === 'mobile') {
            host.dispatchEvent(new CustomEvent('bracket-mobile-controls:refresh'));
        } else {
            teardownMobileRoundControls(host);
        }

        return;
    }

    teardownMobileRoundControls(host);
    host.replaceChildren();

    const hostWidth = host.clientWidth || window.innerWidth;
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
        ...bracketOptions(Array.isArray(data.rounds) ? data.rounds.length : 4, bracketProfile, hostWidth),
        getMatchElement: matchElementFactory,
        getMatchTopHTML: () => '',
        getMatchBottomHTML: () => '',
        getScoresHTML: () => '',
        getPlayerTitleHTML: () => '',
    });

    markEmptySlots(host);
    markDirectEntryMatches(host, matches, matchByKey);
    mountMobileRoundControls(host, Array.isArray(data.rounds) ? data.rounds.map((round) => round?.name || 'Ronde') : []);
    bracketRenderState.set(host, { mode: viewportMode });
};

const syncResponsiveBrackets = () => {
    if (responsiveBracketsResizeFrame !== null) {
        window.cancelAnimationFrame(responsiveBracketsResizeFrame);
    }

    responsiveBracketsResizeFrame = window.requestAnimationFrame(() => {
        document.querySelectorAll('[data-bracketry-host]').forEach((host) => {
            const profile = host.dataset.bracketProfile || 'default';

            if (profile === 'print') {
                return;
            }

            const nextMode = isMobileViewport() ? 'mobile' : 'desktop';
            const previousMode = bracketRenderState.get(host)?.mode;

            if (previousMode && previousMode !== nextMode) {
                renderBracket(host, { force: true });
                return;
            }

            if (nextMode === 'mobile') {
                host.dispatchEvent(new CustomEvent('bracket-mobile-controls:refresh'));
            } else {
                teardownMobileRoundControls(host);
            }
        });

        responsiveBracketsResizeFrame = null;
    });
};

const init = () => {
    document.querySelectorAll('[data-bracketry-host]').forEach(renderBracket);
    initBracketAgeTabs();
    window.addEventListener('resize', syncResponsiveBrackets, { passive: true });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
} else {
    init();
}

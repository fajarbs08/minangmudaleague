@extends('public.public-layout')

@php
    $bracketGroups = $publicBracketryBrackets ?? collect();
    $bracketGroupCount = $bracketGroups->count();
    $bracketMatchCount = $bracketGroups->sum('match_count');
    $bracketWinnerCount = $bracketGroups->filter(fn ($bracket) => ! empty($bracket['winner']))->count();
    $selectedPublicSeason = $selectedPublicSeason ?? null;
    $publicSeasonOptions = $publicSeasonOptions ?? collect();
    $isHistoricalPublicSeason = $isHistoricalPublicSeason ?? false;
    $bracketTabKey = static fn (array $bracket, int $index) => 'age-'.($bracket['age_group']?->id ?? $index);
    $defaultBracketTab = $bracketGroupCount > 0 ? $bracketTabKey($bracketGroups->values()->first(), 0) : null;
@endphp

@push('styles')
    <style>
        .lap-bracket-page,
        .lap-bracket-page .lap-page-shell {
            background: #ffffff;
        }

        .lap-bracket-section {
            background: #ffffff;
            padding: 80px 0 96px;
            color: #030523;
        }

        .lap-bracket-section .container {
            max-width: 1620px;
        }

        @media (min-width: 1400px) {
            .lap-bracket-section .container {
                max-width: 1680px;
            }
        }

        .lap-bracket-intro {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 28px;
            align-items: end;
        }

        .lap-bracket-intro h3,
        .lap-bracket-group-head h3,
        .lap-bracket-summary-head h4,
        .lap-bracket-summary-title {
            font-family: 'Big Shoulders', sans-serif;
            letter-spacing: .01em;
            text-transform: uppercase;
            color: #030523;
        }

        .lap-bracket-intro h3 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }

        .lap-bracket-intro p {
            margin: .45rem 0 0;
            max-width: 66ch;
            font-family: 'Chakra Petch', sans-serif;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: .01em;
            line-height: 1.45;
            text-transform: uppercase;
            color: #030523;
        }

        .lap-bracket-intro-stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 18px 28px;
        }

        .lap-bracket-intro-stat {
            min-width: 128px;
            padding-top: 12px;
            border-top: 1px solid #e7e9f0;
        }

        .lap-bracket-intro-stat strong {
            display: block;
            font-family: 'Big Shoulders', sans-serif;
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
            color: #0d2f67;
        }

        .lap-bracket-intro-stat span {
            display: block;
            margin-top: 8px;
            color: #667085;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-age-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 32px;
            padding-bottom: 4px;
        }

        .lap-age-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: .7rem 1rem;
            border: 1px solid #dbe4f0;
            border-radius: 999px;
            background: #fff;
            color: #667085;
            font-size: .78rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            white-space: nowrap;
            appearance: none;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
        }

        .lap-age-tab:hover,
        .lap-age-tab:focus-visible {
            border-color: #0d2f67;
            color: #0d2f67;
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(13, 47, 103, .08);
            outline: none;
        }

        .lap-age-tab.is-active {
            border-color: #0d2f67;
            background: #0d2f67;
            color: #fff;
            box-shadow: 0 12px 22px rgba(13, 47, 103, .14);
        }

        .lap-bracket-group {
            margin-top: 72px;
        }

        .lap-bracket-group-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            padding-bottom: 16px;
            border-bottom: 1px solid #e7e9f0;
        }

        .lap-bracket-group-head h3 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
        }

        .lap-bracket-group-head p {
            margin: .35rem 0 0;
            max-width: 68ch;
            color: #667085;
            line-height: 1.65;
        }

        .lap-bracket-group-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: #0d2f67;
            color: #fff;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .lap-bracket-group-body {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 28px;
            align-items: start;
        }

        .lap-bracket-stage-shell {
            position: relative;
            display: grid;
            grid-template-columns: 1fr;
            min-height: var(--lap-bracket-height, clamp(560px, 68vw, 840px));
            border: 1px solid #eef2f7;
            border-radius: .85rem;
            background: #fff;
            overflow: hidden;
        }

        .lap-bracket-host {
            min-height: var(--lap-bracket-height, clamp(560px, 68vw, 840px));
            min-width: 0;
        }

        .bracket-mobile-controls {
            display: none;
        }

        .lap-bracket-host [data-bracketry-target] {
            height: 100%;
        }

        .lap-bracket-preliminary {
            display: grid;
            gap: 16px;
        }

        .lap-bracket-preliminary-round {
            display: grid;
            gap: 12px;
        }

        .lap-bracket-preliminary-label {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: .4rem .8rem;
            border-radius: 999px;
            background: #f2f6ff;
            color: #0d2f67;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-bracket-preliminary-track {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .lap-bracket-preliminary-match {
            display: grid;
            gap: 12px;
            width: 100%;
            padding: 16px 18px;
            border: 1px solid #e7e9f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
            text-align: left;
            appearance: none;
            font: inherit;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .lap-bracket-preliminary-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            color: #667085;
            font-size: .72rem;
            font-weight: 700;
        }

        .lap-bracket-preliminary-status {
            margin-left: auto;
            color: #0d2f67;
            font-weight: 800;
            letter-spacing: .02em;
            text-align: right;
        }

        .lap-bracket-preliminary-teams {
            display: grid;
            gap: 10px;
        }

        .lap-bracket-preliminary-team {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .85rem;
            align-items: center;
            font-size: .96rem;
            font-weight: 800;
            color: #10131f;
        }

        .lap-bracket-preliminary-team + .lap-bracket-preliminary-team {
            padding-top: 10px;
            border-top: 1px solid #edf1f6;
        }

        .lap-bracket-preliminary-score {
            min-width: 2rem;
            padding: .18rem .5rem;
            border-radius: 999px;
            background: #0d2f67;
            color: #fff;
            text-align: center;
            font-size: .82rem;
            font-weight: 900;
            line-height: 1.2;
        }

        .lap-bracket-preliminary-winner {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            color: #0d2f67;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lap-bracket-preliminary-winner svg {
            width: .95rem;
            height: .95rem;
            flex: 0 0 auto;
            color: #d8a317;
        }

        .lap-bracket-host .bracket-root {
            border: none;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        .lap-bracket-host .bt-nav-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border: 1px solid #d5dbe7;
            border-radius: 999px;
            background: #fff;
            color: #0d2f67;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
            font-size: 1.1rem;
            font-weight: 900;
            line-height: 1;
        }

        .lap-bracket-host .navigation-button.active:hover .bt-nav-arrow {
            border-color: #0d2f67;
            transform: translateY(-1px);
        }

        .lap-bracket-host .round-title {
            font-size: .82rem;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: none;
            color: #0d2f67;
        }

        .lap-bracket-host .bt-match {
            position: relative;
            display: grid;
            gap: .4rem;
            width: 100%;
            padding: .75rem .8rem .8rem;
            border: 1px solid #eef2f7;
            border-radius: .75rem;
            background: #fff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .035);
            text-align: left;
            appearance: none;
            font: inherit;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .lap-bracket-host .bt-match.is-final {
            grid-template-columns: 1fr;
            justify-items: center;
            gap: .65rem;
        }

        .lap-bracket-host .bt-match.is-final .bt-match-main {
            width: 100%;
        }

        .lap-bracket-host .bt-match-main {
            display: grid;
            gap: .4rem;
        }

        .lap-bracket-host .bt-match-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
        }

        .lap-bracket-host .bt-match-ribbon {
            display: inline-flex;
            align-items: center;
            padding: .15rem .5rem;
            border-radius: 999px;
            background: #f2f6ff;
            color: #0d2f67;
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: .08em;
        }

        .lap-bracket-host .bt-match-status {
            color: #667085;
            font-size: .68rem;
            font-weight: 700;
            text-align: right;
            max-width: 12ch;
        }

        .lap-bracket-host .bt-side {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .65rem;
            align-items: center;
            padding-left: 0;
        }

        .lap-bracket-host .bt-side + .bt-side {
            padding-top: .45rem;
            border-top: 1px solid #edf1f6;
        }

        .lap-bracket-host .bt-side-name {
            color: #10131f;
            font-size: .92rem;
            font-weight: 800;
            letter-spacing: -.01em;
            line-height: 1.2;
        }

        .lap-bracket-host .bt-side-score {
            min-width: 1.9rem;
            padding: .16rem .4rem;
            border-radius: .45rem;
            background: #f3f4f7;
            color: #0d2f67;
            text-align: center;
            font-size: .82rem;
            font-weight: 900;
            transition: background-color .18s ease, color .18s ease, transform .18s ease;
        }

        .lap-bracket-host .bt-vs {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: .05rem 0;
            color: #10131f;
            font-size: .72rem;
            font-weight: 900;
            letter-spacing: .12em;
        }

        .lap-bracket-host .bt-final-note {
            justify-self: end;
            margin-top: .1rem;
            padding: .1rem .45rem;
            border-radius: 999px;
            background: #f2f6ff;
            color: #0d2f67;
            font-size: .64rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .lap-bracket-host .bt-match.is-live,
        .lap-bracket-host .bt-match:hover,
        .lap-bracket-preliminary-match:hover,
        .lap-bracket-preliminary-match.is-selected {
            border-color: rgba(13, 47, 103, .24);
            transform: translateY(-1px);
            background: #fff;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .06);
        }

        .lap-bracket-host .bt-match:focus-visible,
        .lap-bracket-preliminary-match:focus-visible {
            outline: 3px solid rgba(13, 47, 103, .16);
            outline-offset: 2px;
        }

        .lap-bracket-host .bt-match.is-live .bt-side-score,
        .lap-bracket-host .bt-match:hover .bt-side-score,
        .lap-bracket-preliminary-match.is-selected .lap-bracket-preliminary-score {
            background: #0d2f67;
            color: #fff;
            transform: translateY(-1px);
        }

        .lap-bracket-host .player-title {
            color: #10131f;
            font-weight: 700;
        }

        .lap-bracket-host .match-status {
            border-radius: 999px;
            background: #f3f4f7;
            color: #667085;
            border-color: #e7e9f0;
            font-size: .7rem;
            font-weight: 800;
        }

        .lap-bracket-host .current-score,
        .lap-bracket-host .side-info-item.current-score {
            border-radius: 999px;
            background: #10131f;
            color: #fff;
            border-color: #10131f;
        }

        .lap-bracket-host .side-wrapper.highlighted .player-title,
        .lap-bracket-host .match-body:not(:empty):hover .player-title {
            color: #e41b23;
        }

        .lap-bracket-host .match-body {
            border-radius: 1rem;
        }

        .lap-bracket-host .bracket-root .round-wrapper:first-of-type .match-lines-area {
            left: 50% !important;
        }

        /* Placeholder slots generated by bracketry should not render orphan connectors. */
        .lap-bracket-host .match-wrapper.is-empty-slot .match-lines-area {
            visibility: hidden;
        }

        /* Direct-entry semifinal slots keep the outgoing connector, but drop the fake left stub. */
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

        .lap-bracket-summary {
            display: grid;
            gap: 1rem;
        }

        .lap-bracket-summary-copy {
            margin: 0;
            color: #667085;
            line-height: 1.65;
        }

        .lap-bracket-empty {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid #e7e9f0;
            color: #667085;
        }

        @media (max-width: 991.98px) {
            .lap-bracket-intro,
            .lap-bracket-group-body {
                grid-template-columns: 1fr;
            }

            .lap-bracket-intro-stats {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767px) {
            .lap-bracket-section {
                padding: 80px 0 72px;
            }

            .lap-bracket-group {
                margin-top: 56px;
            }

            .lap-bracket-host {
                min-height: var(--lap-bracket-height-mobile, clamp(420px, 128vw, 640px));
            }

            .lap-bracket-stage-shell {
                min-height: var(--lap-bracket-height-mobile, clamp(420px, 128vw, 640px));
                border-radius: .75rem;
            }

            .lap-bracket-group-head {
                align-items: flex-start;
            }

            .lap-bracket-group-badge {
                align-self: flex-start;
            }

            .lap-bracket-host[data-bracket-mobile-controls="true"] .navigation-button,
            .lap-bracket-host[data-bracket-mobile-controls="true"] .scroll-button {
                display: none !important;
            }

            .lap-bracket-host[data-bracket-mobile-controls="true"] .match-lines-area,
            .lap-bracket-host[data-bracket-mobile-controls="true"] .line-wrapper {
                display: none !important;
            }

            .bracket-mobile-controls {
                display: grid;
                grid-template-columns: 3.4rem minmax(0, 1fr) 3.4rem;
                align-items: center;
                gap: .75rem;
                margin-top: .85rem;
            }

            .bracket-mobile-controls__button {
                min-width: 3.4rem;
                width: 3.4rem;
                height: 3.4rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #d5dbe7;
                border-radius: 999px;
                background: #fff;
                color: #0d2f67;
                box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
                font-size: 1.55rem;
                font-weight: 900;
                line-height: 1;
            }

            .bracket-mobile-controls__button:disabled {
                opacity: .38;
                box-shadow: none;
            }

            .bracket-mobile-controls__label {
                min-width: 0;
                text-align: center;
            }

            .bracket-mobile-controls__eyebrow {
                color: #64748b;
                font-size: .67rem;
                font-weight: 800;
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .bracket-mobile-controls__title {
                margin-top: .2rem;
                color: #0f172a;
                font-size: .95rem;
                font-weight: 800;
                line-height: 1.15;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .lap-bracket-host .bt-match {
                gap: .3rem;
                padding: .65rem .72rem .72rem;
                border-radius: .7rem;
            }

            .lap-bracket-host .bt-match.is-final {
                gap: .45rem;
            }

            .lap-bracket-host .bt-match-main {
                gap: .3rem;
            }

            .lap-bracket-host .bt-match-head {
                gap: .5rem;
            }

            .lap-bracket-host .bt-match-ribbon {
                padding: .12rem .42rem;
                font-size: .6rem;
            }

            .lap-bracket-host .bt-match-status {
                max-width: 10ch;
                font-size: .62rem;
            }

            .lap-bracket-host .bt-side {
                gap: .55rem;
            }

            .lap-bracket-host .bt-side + .bt-side {
                padding-top: .34rem;
            }

            .lap-bracket-host .bt-side-name {
                font-size: .86rem;
                line-height: 1.16;
                word-break: break-word;
            }

            .lap-bracket-host .bt-side-score {
                min-width: 1.85rem;
                padding: .12rem .35rem;
                font-size: .78rem;
            }

            .lap-bracket-host .bt-vs {
                margin: 0;
                font-size: .64rem;
            }

            .lap-bracket-preliminary-track {
                grid-template-columns: 1fr;
            }

            .lap-bracket-preliminary-meta {
                display: grid;
                gap: .25rem;
            }

            .lap-bracket-preliminary-status {
                text-align: left;
            }

            .lap-age-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 8px;
                scrollbar-width: none;
            }

            .lap-age-tabs::-webkit-scrollbar {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    <section class="lap-bracket-section">
        <div class="container">
            @if ($publicSeasonOptions->isNotEmpty())
                <div class="mb-4 d-flex flex-wrap align-items-center gap-3">
                    <div class="fw-bold text-uppercase small text-muted">Season</div>
                    <form method="GET">
                        <select name="season" class="form-select" style="min-width: 220px;" onchange="this.form.submit()">
                            @foreach ($publicSeasonOptions as $season)
                                <option value="{{ $season->slug }}" @selected(($selectedPublicSeason?->id ?? 0) === $season->id)>{{ $season->name }}{{ $season->is_active ? ' • aktif' : '' }}</option>
                            @endforeach
                        </select>
                    </form>
                    @if ($selectedPublicSeason)
                        <span class="lap-bracket-group-badge">{{ $selectedPublicSeason->name }}{{ $isHistoricalPublicSeason ? ' · histori' : '' }}</span>
                    @endif
                </div>
            @endif

            @if ($bracketGroupCount > 0)
                <div class="lap-age-tabs" data-bracket-age-tabs role="tablist" aria-label="Pilih kelompok usia bracket">
                    @foreach ($bracketGroups->values() as $index => $bracket)
                        @php
                            $tabKey = $bracketTabKey($bracket, $index);
                        @endphp
                        <button
                            type="button"
                            class="lap-age-tab {{ $tabKey === $defaultBracketTab ? 'is-active' : '' }}"
                            data-bracket-age-tab="{{ $tabKey }}"
                            role="tab"
                            aria-selected="{{ $tabKey === $defaultBracketTab ? 'true' : 'false' }}"
                        >
                            {{ $bracket['age_group']?->name ?: 'Kelompok Usia' }}
                        </button>
                    @endforeach
                </div>
            @endif

            @forelse ($bracketGroups as $bracket)
                @php
                    $tabKey = $bracketTabKey($bracket, $loop->index);
                @endphp
                <section class="lap-bracket-group" data-bracket-age-panel="{{ $tabKey }}">
                    <div class="lap-bracket-group-head">
                        <div>
                            <span class="lap-section-kicker">Bagan Knockout</span>
                            <h3>{{ $bracket['age_group']?->name ?: '-' }}</h3>
                            <p>Jalur knockout yang tersusun berdasarkan ronde, slot bracket, dan hasil pertandingan.</p>
                        </div>
                        <span class="lap-bracket-group-badge">{{ $bracket['match_count'] }} laga</span>
                    </div>

                    <div class="lap-bracket-group-body">
                        <div
                            class="lap-bracket-stage-shell"
                            style="--lap-bracket-height: {{ $bracket['layout']['desktop_height'] ?? 720 }}px; --lap-bracket-height-mobile: {{ $bracket['layout']['mobile_height'] ?? 520 }}px;"
                        >
                            <div class="lap-bracket-host" data-bracketry-host>
                                <script type="application/json" data-bracketry-data>
                                    @json($bracket['data'])
                                </script>
                            </div>
                        </div>
                    </div>
                </section>
            @empty
                <div class="lap-bracket-empty">
                    <h3 class="lap-card-title-sm">Belum ada bagan knockout</h3>
                    <p class="lap-card-copy">Bracket akan tampil setelah pertandingan knockout pertama disiapkan.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection

@push('scripts')
    @vite(['resources/js/public-brackets.js'])
@endpush

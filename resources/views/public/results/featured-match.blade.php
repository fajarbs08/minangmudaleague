<section aria-labelledby="featured-match-heading" class="tw-relative tw-overflow-hidden tw-border-y tw-border-white/10 tw-bg-[linear-gradient(135deg,rgba(255,255,255,0.04),rgba(255,255,255,0.02))] tw-text-white">
    <div class="tw-absolute tw-left-1/2 tw-top-8 tw-h-40 tw-w-40 tw--translate-x-1/2 tw-rounded-full tw-bg-lap-red/12 tw-blur-3xl"></div>

    <div class="tw-relative tw-px-4 tw-py-6 sm:tw-px-6 lg:tw-px-8 lg:tw-py-8">
        <div class="tw-flex tw-flex-col tw-gap-3 tw-border-b tw-border-white/10 tw-pb-5 sm:tw-flex-row sm:tw-items-end sm:tw-justify-between">
            <div>
                <p class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.3em] tw-text-lap-red">Featured Match</p>
                <h2 id="featured-match-heading" class="tw-mt-3 tw-font-display tw-text-3xl tw-font-black tw-leading-[0.96] tw-tracking-[-0.04em] tw-text-white lg:tw-text-4xl">{{ $match['home_name'] }} vs {{ $match['away_name'] }}</h2>
            </div>

            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.2em]">
                <span class="tw-inline-flex tw-items-center tw-border tw-border-lap-red/40 tw-bg-lap-red/10 tw-px-3 tw-py-2 tw-text-white">{{ $match['status'] }}</span>
                <span class="tw-inline-flex tw-items-center tw-border tw-border-white/10 tw-bg-white/[0.03] tw-px-3 tw-py-2 tw-text-slate-300">{{ $match['age_group'] }}</span>
            </div>
        </div>

        <div class="tw-grid tw-gap-6 tw-py-6 lg:tw-grid-cols-[minmax(0,1fr)_240px_minmax(0,1fr)] lg:tw-items-center lg:tw-gap-8 lg:tw-py-8">
            <div class="tw-flex tw-items-center tw-gap-4 tw-min-w-0">
                <div class="tw-flex tw-h-16 tw-w-16 tw-shrink-0 tw-items-center tw-justify-center tw-border tw-border-white/10 tw-bg-white tw-p-2 sm:tw-h-20 sm:tw-w-20">
                    <img class="tw-h-full tw-w-full tw-object-contain" src="{{ $match['home_logo'] }}" alt="{{ $match['home_name'] }}">
                </div>
                <div class="tw-min-w-0">
                    <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Home</div>
                    <div class="tw-mt-2 tw-truncate tw-text-2xl tw-font-black tw-leading-none tw-text-white lg:tw-text-3xl">{{ $match['home_name'] }}</div>
                    <div class="tw-mt-2 tw-text-sm tw-font-semibold tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $match['home_short'] }}</div>
                </div>
            </div>

            <div class="tw-border tw-border-white/10 tw-bg-black/20 tw-px-4 tw-py-5 tw-text-center">
                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.24em] tw-text-slate-400">Skor Akhir</div>
                <div class="tw-mt-4 tw-flex tw-items-end tw-justify-center tw-gap-3 tw-font-display tw-font-black tw-leading-none tw-text-white">
                    <span class="tw-min-w-[64px] tw-text-center tw-text-5xl lg:tw-text-6xl">{{ $match['home_score'] }}</span>
                    <span class="tw-mb-1 tw-text-2xl tw-text-lap-red lg:tw-text-3xl">:</span>
                    <span class="tw-min-w-[64px] tw-text-center tw-text-5xl lg:tw-text-6xl">{{ $match['away_score'] }}</span>
                </div>
                <div class="tw-mt-4 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-400">{{ $match['status_label'] }}</div>
            </div>

            <div class="tw-flex tw-items-center tw-gap-4 tw-min-w-0 lg:tw-justify-end lg:tw-text-right">
                <div class="tw-min-w-0 lg:tw-order-1">
                    <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Away</div>
                    <div class="tw-mt-2 tw-truncate tw-text-2xl tw-font-black tw-leading-none tw-text-white lg:tw-text-3xl">{{ $match['away_name'] }}</div>
                    <div class="tw-mt-2 tw-text-sm tw-font-semibold tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $match['away_short'] }}</div>
                </div>
                <div class="tw-flex tw-h-16 tw-w-16 tw-shrink-0 tw-items-center tw-justify-center tw-border tw-border-white/10 tw-bg-white tw-p-2 sm:tw-h-20 sm:tw-w-20">
                    <img class="tw-h-full tw-w-full tw-object-contain" src="{{ $match['away_logo'] }}" alt="{{ $match['away_name'] }}">
                </div>
            </div>
        </div>

        <div class="tw-grid tw-gap-px tw-bg-white/10 lg:tw-grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
            <div class="tw-bg-black/10 tw-p-4">
                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-400">Tanggal</div>
                <div class="tw-mt-2 tw-text-base tw-font-black tw-text-white lg:tw-text-lg">{{ $match['date_full'] }}</div>
            </div>
            <div class="tw-bg-black/10 tw-p-4">
                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-400">Jam Kickoff</div>
                <div class="tw-mt-2 tw-text-base tw-font-black tw-text-white lg:tw-text-lg">{{ $match['time'] }}</div>
            </div>
            <div class="tw-bg-black/10 tw-p-4">
                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-400">Venue</div>
                <div class="tw-mt-2 tw-text-base tw-font-black tw-text-white lg:tw-text-lg">{{ $match['venue'] }}</div>
            </div>
            <div class="tw-flex tw-items-center tw-justify-start tw-bg-black/10 tw-p-4 lg:tw-justify-center">
                @if ($match['detail_url'] !== '#')
                    <a href="{{ $match['detail_url'] }}" class="tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white hover:tw-border-lap-red hover:tw-text-lap-red">Lihat Detail</a>
                @else
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Preview</span>
                @endif
            </div>
        </div>
    </div>
</section>

@php
    $homeNameClass = $row['home_outcome'] === 'Kalah' ? 'tw-text-slate-400' : 'tw-text-white';
    $awayNameClass = $row['away_outcome'] === 'Kalah' ? 'tw-text-slate-400' : 'tw-text-white';
    $statusClass = $row['status'] === 'LIVE'
        ? 'tw-border-lap-red/40 tw-bg-lap-red/10 tw-text-white'
        : 'tw-border-white/10 tw-bg-white/[0.03] tw-text-slate-200';
@endphp

<li class="tw-group">
    <article class="tw-grid tw-gap-5 tw-px-4 tw-py-5 tw-transition tw-duration-200 hover:tw-bg-white/[0.03] sm:tw-px-6 lg:tw-grid-cols-[128px_minmax(0,1fr)_200px_108px] lg:tw-items-center lg:tw-gap-6 lg:tw-py-6">
        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3 lg:tw-block">
            <div>
                <p class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.24em] tw-text-lap-red">{{ $row['date_short'] }}</p>
                <p class="tw-mt-2 tw-text-sm tw-font-semibold tw-text-white">{{ $row['date_full'] }}</p>
                <p class="tw-mt-1 tw-text-xs tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $row['time'] }}</p>
            </div>

            <span class="tw-inline-flex tw-items-center tw-border tw-border-white/10 tw-bg-white/[0.03] tw-px-3 tw-py-2 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-200">{{ $row['age_group'] }}</span>
        </div>

        <div class="tw-grid tw-gap-4 sm:tw-grid-cols-[minmax(0,1fr)_118px_minmax(0,1fr)] sm:tw-items-center">
            <div class="tw-flex tw-items-center tw-gap-3 tw-min-w-0">
                <div class="tw-flex tw-h-12 tw-w-12 tw-shrink-0 tw-items-center tw-justify-center tw-border tw-border-white/10 tw-bg-white tw-p-2">
                    <img class="tw-h-full tw-w-full tw-object-contain" src="{{ $row['home_logo'] }}" alt="{{ $row['home_name'] }}">
                </div>
                <div class="tw-min-w-0">
                    <div class="tw-truncate tw-text-base tw-font-black tw-leading-none {{ $homeNameClass }}">{{ $row['home_name'] }}</div>
                    <div class="tw-mt-2 tw-text-[0.68rem] tw-font-semibold tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $row['home_short'] }}</div>
                </div>
            </div>

            <div class="tw-border tw-border-white/10 tw-bg-black/10 tw-px-3 tw-py-4 tw-text-center">
                <div class="tw-font-display tw-text-3xl tw-font-black tw-leading-none tw-text-white lg:tw-text-4xl">{{ $row['home_score'] }}<span class="tw-px-1 tw-text-lap-red">:</span>{{ $row['away_score'] }}</div>
                <div class="tw-mt-2 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $row['status'] }}</div>
            </div>

            <div class="tw-flex tw-items-center tw-gap-3 tw-min-w-0 sm:tw-flex-row-reverse sm:tw-text-right">
                <div class="tw-flex tw-h-12 tw-w-12 tw-shrink-0 tw-items-center tw-justify-center tw-border tw-border-white/10 tw-bg-white tw-p-2">
                    <img class="tw-h-full tw-w-full tw-object-contain" src="{{ $row['away_logo'] }}" alt="{{ $row['away_name'] }}">
                </div>
                <div class="tw-min-w-0">
                    <div class="tw-truncate tw-text-base tw-font-black tw-leading-none {{ $awayNameClass }}">{{ $row['away_name'] }}</div>
                    <div class="tw-mt-2 tw-text-[0.68rem] tw-font-semibold tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $row['away_short'] }}</div>
                </div>
            </div>
        </div>

        <div class="tw-flex tw-items-start tw-justify-between tw-gap-4 lg:tw-block lg:tw-text-right">
            <div>
                <span class="tw-inline-flex tw-items-center tw-border tw-px-3 tw-py-2 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] {{ $statusClass }}">{{ $row['status'] }}</span>
                <p class="tw-mt-2 tw-text-xs tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $row['status_label'] }}</p>
            </div>

            <div class="lg:tw-mt-4">
                <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-500">Venue</p>
                <p class="tw-mt-2 tw-text-sm tw-font-medium tw-leading-6 tw-text-slate-200">{{ $row['venue'] }}</p>
            </div>
        </div>

        <div class="tw-flex tw-justify-start lg:tw-justify-end">
            @if ($row['detail_url'] !== '#')
                <a href="{{ $row['detail_url'] }}" class="tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white hover:tw-border-lap-red hover:tw-text-lap-red">Detail</a>
            @else
                <span class="tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Preview</span>
            @endif
        </div>
    </article>
</li>

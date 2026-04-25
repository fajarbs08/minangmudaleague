@php
    $featuredSummary = filled($match['summary'])
        ? \Illuminate\Support\Str::limit($match['summary'], 110)
        : 'Lihat detail lengkap untuk ringkasan hasil resmi.';
    $featuredStatusLabel = match ($match['status']) {
        'LIVE' => 'LIVE',
        'SCHEDULED' => 'SCHEDULED',
        default => 'FULL TIME',
    };
    $featuredMeta = [
        ['label' => 'Tanggal', 'value' => $match['date_full']],
        ['label' => 'Kickoff', 'value' => $match['time']],
        ['label' => 'Venue', 'value' => $match['venue']],
    ];
@endphp

<article aria-labelledby="featured-match-heading" class="tw-overflow-hidden tw-rounded-[32px] tw-border tw-border-slate-950 tw-bg-[linear-gradient(180deg,#060b14_0%,#0b1220_54%,#111927_100%)] tw-text-white tw-shadow-[0_24px_60px_rgba(15,23,42,0.16)]">
    <div class="tw-grid xl:tw-grid-cols-[minmax(0,1.2fr)_320px]">
        <div class="tw-px-5 tw-py-5 sm:tw-px-8 sm:tw-py-7">
            <div class="tw-flex tw-flex-col tw-gap-3 tw-border-b tw-border-white/10 tw-pb-5 sm:tw-flex-row sm:tw-items-start sm:tw-justify-between">
                <div>
                    <p class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.3em] tw-text-lap-red">Sorotan Utama</p>
                    <h2 id="featured-match-heading" class="tw-mt-2 tw-font-display tw-text-2xl tw-font-black tw-tracking-[-0.03em] tw-text-white sm:tw-text-3xl">{{ $match['home_name'] }} vs {{ $match['away_name'] }}</h2>
                </div>

                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                    <span class="tw-inline-flex tw-items-center tw-rounded-full tw-border tw-border-white/10 tw-bg-white/5 tw-px-3 tw-py-1.5 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-200">{{ $match['competition_format_label'] }}</span>
                    <span class="tw-inline-flex tw-items-center tw-rounded-full tw-border tw-border-white/10 tw-bg-white/5 tw-px-3 tw-py-1.5 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-200">{{ $match['age_group'] }}</span>
                    @include('public.results.status-badge', ['status' => $match['status'], 'label' => $featuredStatusLabel, 'theme' => 'dark'])
                </div>
            </div>

            <div class="tw-grid tw-gap-5 tw-py-6 lg:tw-grid-cols-[minmax(0,1fr)_184px_minmax(0,1fr)] lg:tw-items-center">
                <div class="tw-flex tw-items-center tw-gap-4 sm:tw-gap-5">
                    <div class="tw-flex tw-h-20 tw-w-20 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-[22px] tw-border tw-border-white/10 tw-bg-white tw-p-3 sm:tw-h-24 sm:tw-w-24">
                        @include('public.partials.identity-mark', ['imageUrl' => $match['home_logo'], 'label' => $match['home_name'], 'imgClass' => 'tw-h-full tw-w-full tw-object-contain', 'badgeClass' => 'tw-inline-flex tw-h-full tw-w-full tw-items-center tw-justify-center tw-rounded-[18px] tw-bg-white tw-font-display tw-text-2xl tw-font-black tw-uppercase tw-tracking-[0.16em] tw-text-slate-950'])
                    </div>
                    <div class="tw-min-w-0">
                        <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Tuan rumah</p>
                        <h3 class="tw-mt-3 tw-truncate tw-font-display tw-text-2xl tw-font-black tw-leading-tight tw-text-white lg:tw-text-3xl">{{ $match['home_name'] }}</h3>
                        <p class="tw-mt-2 tw-text-sm tw-font-semibold tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $match['home_short'] }}</p>
                    </div>
                </div>

                <div class="tw-rounded-[28px] tw-border tw-border-white/10 tw-bg-[linear-gradient(180deg,rgba(228,27,35,0.22)_0%,rgba(228,27,35,0.04)_100%)] tw-px-4 tw-py-5 tw-text-center">
                    <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.24em] tw-text-slate-300">Skor resmi</p>
                    <div class="tw-mt-4 tw-flex tw-items-end tw-justify-center tw-gap-3 tw-font-display tw-leading-none tw-text-white">
                        <span class="tw-min-w-[56px] tw-text-center tw-text-5xl tw-font-black lg:tw-text-6xl">{{ $match['home_score'] }}</span>
                        <span class="tw-pb-1 tw-text-3xl tw-font-black tw-text-lap-red">:</span>
                        <span class="tw-min-w-[56px] tw-text-center tw-text-5xl tw-font-black lg:tw-text-6xl">{{ $match['away_score'] }}</span>
                    </div>
                    <p class="tw-mt-4 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-300">{{ $featuredStatusLabel }}</p>
                </div>

                <div class="tw-flex tw-items-center tw-gap-4 sm:tw-gap-5 lg:tw-justify-end lg:tw-text-right">
                    <div class="tw-min-w-0 lg:tw-order-1">
                        <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Tim tandang</p>
                        <h3 class="tw-mt-3 tw-truncate tw-font-display tw-text-2xl tw-font-black tw-leading-tight tw-text-white lg:tw-text-3xl">{{ $match['away_name'] }}</h3>
                        <p class="tw-mt-2 tw-text-sm tw-font-semibold tw-uppercase tw-tracking-[0.18em] tw-text-slate-400">{{ $match['away_short'] }}</p>
                    </div>
                    <div class="tw-flex tw-h-20 tw-w-20 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-[22px] tw-border tw-border-white/10 tw-bg-white tw-p-3 sm:tw-h-24 sm:tw-w-24">
                        @include('public.partials.identity-mark', ['imageUrl' => $match['away_logo'], 'label' => $match['away_name'], 'imgClass' => 'tw-h-full tw-w-full tw-object-contain', 'badgeClass' => 'tw-inline-flex tw-h-full tw-w-full tw-items-center tw-justify-center tw-rounded-[18px] tw-bg-white tw-font-display tw-text-2xl tw-font-black tw-uppercase tw-tracking-[0.16em] tw-text-slate-950'])
                    </div>
                </div>
            </div>
        </div>

        <aside class="tw-border-t tw-border-white/10 tw-bg-[linear-gradient(180deg,rgba(255,255,255,0.05)_0%,rgba(255,255,255,0.02)_100%)] tw-px-5 tw-py-5 sm:tw-px-8 xl:tw-border-l xl:tw-border-t-0">
            <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">Ringkasan</p>
            <p class="tw-mt-3 tw-text-sm tw-leading-7 tw-text-slate-100">{{ $featuredSummary }}</p>

            <dl class="tw-mt-6 tw-grid tw-gap-4">
                @foreach ($featuredMeta as $meta)
                    <div>
                        <dt class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-500">{{ $meta['label'] }}</dt>
                        <dd class="tw-mt-2 tw-text-sm tw-font-semibold tw-leading-6 tw-text-white">{{ $meta['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="tw-mt-6">
                @if ($match['detail_url'])
                    <a href="{{ $match['detail_url'] }}" class="tw-inline-flex tw-h-11 tw-w-full tw-items-center tw-justify-center tw-rounded-xl tw-bg-lap-red tw-px-5 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white tw-transition hover:tw-bg-[#c9151d] focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-white/40 focus-visible:tw-ring-offset-2 focus-visible:tw-ring-offset-slate-950">
                        Buka detail laga
                    </a>
                @else
                    <span class="tw-inline-flex tw-h-11 tw-w-full tw-items-center tw-justify-center tw-rounded-xl tw-border tw-border-white/10 tw-px-5 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-300">
                        Detail belum tersedia
                    </span>
                @endif
            </div>
        </aside>
    </div>
</article>

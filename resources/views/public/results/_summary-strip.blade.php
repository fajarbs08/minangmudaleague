<section aria-labelledby="results-summary-heading" class="tw-overflow-hidden tw-rounded-[30px] tw-border tw-border-slate-200/90 tw-bg-white tw-shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
    <div class="tw-grid xl:tw-grid-cols-[minmax(0,1.28fr)_360px]">
        <div class="tw-relative tw-px-5 tw-py-6 sm:tw-px-8 sm:tw-py-8 lg:tw-py-9">
            <div class="tw-absolute tw-inset-x-0 tw-top-0 tw-h-1 tw-bg-[linear-gradient(90deg,#e41b23_0%,#7f1d1d_62%,transparent_100%)]"></div>

            <p class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.3em] tw-text-lap-red">{{ $eyebrow }}</p>
            <h1 id="results-summary-heading" class="tw-mt-3 tw-max-w-3xl tw-font-display tw-text-3xl tw-font-black tw-leading-[1.02] tw-tracking-[-0.04em] tw-text-slate-950 lg:tw-text-[2.8rem]">{{ $title }}</h1>
            <p class="tw-mt-4 tw-max-w-3xl tw-text-sm tw-leading-7 tw-text-slate-600 lg:tw-text-base">{{ $description }}</p>

            <div class="tw-mt-6 tw-border-t tw-border-slate-200 tw-pt-6">
                <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-500">Sorotan terbaru</p>
                <p class="tw-mt-3 tw-max-w-3xl tw-font-display tw-text-2xl tw-font-black tw-leading-tight tw-tracking-[-0.03em] tw-text-slate-950 sm:tw-text-[2rem]">{{ $featuredHeadline }}</p>
                <p class="tw-mt-2 tw-text-sm tw-leading-6 tw-text-slate-600">{{ $featuredMetaLine }}</p>
            </div>
        </div>

        <div class="tw-grid tw-gap-px tw-border-t tw-border-slate-200 tw-bg-slate-900 xl:tw-border-l xl:tw-border-t-0">
            @foreach ($metrics as $metric)
                <div class="tw-bg-[linear-gradient(180deg,#0f172a_0%,#111827_100%)] tw-p-5 tw-text-white sm:tw-p-6">
                    <p class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-slate-400">{{ $metric['label'] }}</p>
                    <p class="tw-mt-3 tw-font-display tw-text-4xl tw-font-black tw-leading-none tw-tracking-[-0.04em] tw-text-white">{{ $metric['value'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

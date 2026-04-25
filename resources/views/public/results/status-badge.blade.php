@php
    $resolvedStatus = strtoupper($status ?? 'FT');
    $resolvedLabel = $label ?? $resolvedStatus;
    $theme = $theme ?? 'light';

    $classes = match ([$theme, $resolvedStatus]) {
        ['dark', 'LIVE'] => 'tw-border-lap-red/40 tw-bg-lap-red/15 tw-text-white',
        ['dark', 'SCHEDULED'] => 'tw-border-white/15 tw-bg-white/10 tw-text-slate-100',
        ['dark', 'FT'] => 'tw-border-emerald-400/20 tw-bg-emerald-400/10 tw-text-emerald-100',
        ['light', 'LIVE'] => 'tw-border-lap-red/20 tw-bg-lap-red/10 tw-text-lap-red',
        ['light', 'SCHEDULED'] => 'tw-border-amber-200 tw-bg-amber-50 tw-text-amber-700',
        default => 'tw-border-emerald-200 tw-bg-emerald-50 tw-text-emerald-700',
    };
@endphp

<span class="tw-inline-flex tw-items-center tw-gap-2 tw-whitespace-nowrap tw-rounded-full tw-border tw-px-3 tw-py-1.5 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] {{ $classes }}">
    @if ($resolvedStatus === 'LIVE')
        <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-current"></span>
    @endif
    {{ $resolvedLabel }}
</span>

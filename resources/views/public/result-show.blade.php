@extends('public.layout')

@php
    $clubAName = $matchResult->clubA?->name ?: 'Klub A';
    $clubBName = $matchResult->clubB?->name ?: 'Klub B';
    $clubAShort = $matchResult->clubA?->short_name ?: 'Home';
    $clubBShort = $matchResult->clubB?->short_name ?: 'Away';
    $winnerLabel = $matchResult->score_club_a === $matchResult->score_club_b
        ? 'Laga berakhir imbang'
        : ($matchResult->score_club_a > $matchResult->score_club_b ? $clubAName.' menang' : $clubBName.' menang');
    $shareText = 'Hasil pertandingan '.$clubAName.' vs '.$clubBName.' - '.$matchResult->score_label;
    $clubAStats = $matchResultClubStats['club_a'] ?? null;
    $clubBStats = $matchResultClubStats['club_b'] ?? null;
@endphp

@section('content')
    <div class="tw-bg-lap-ink tw-py-14 lg:tw-py-20">
        <div class="container">
            <section class="tw-overflow-hidden tw-border-y-4 tw-border-lap-red tw-bg-[linear-gradient(140deg,#06080c_0%,#101722_44%,#4b0b18_44%,#8f0f20_70%,#e41b23_100%)] tw-text-white">
                <div class="tw-grid xl:tw-grid-cols-[minmax(0,1fr)_220px_minmax(0,1fr)]">
                    <div class="tw-border-b tw-border-white/10 tw-p-6 lg:tw-border-b-0 lg:tw-border-r lg:tw-p-10">
                        <div class="tw-flex tw-items-center tw-gap-4">
                            <div class="tw-flex tw-h-20 tw-w-20 tw-shrink-0 tw-items-center tw-justify-center tw-bg-white">
                                <img class="tw-h-14 tw-w-14 tw-object-contain" src="{{ $matchResult->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-3.svg') }}" alt="{{ $clubAName }}">
                            </div>
                            <div class="tw-min-w-0">
                                <div class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white/52">{{ $clubAShort }}</div>
                                <div class="tw-mt-2 tw-text-3xl tw-font-black tw-leading-none tw-text-white lg:tw-text-4xl">{{ $clubAName }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-border-y tw-border-white/10 tw-bg-black/25 tw-p-6 tw-text-center lg:tw-border-x lg:tw-border-y-0 lg:tw-p-10">
                        <div class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.3em] tw-text-white/62">Final Score</div>
                        <div class="tw-mt-5 tw-text-6xl tw-font-black tw-leading-none tw-text-white lg:tw-text-7xl">{{ $matchResult->score_label }}</div>
                        <div class="tw-mt-5 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.24em] tw-text-white/68">{{ $winnerLabel }}</div>
                        <div class="tw-mt-6 tw-inline-flex tw-items-center tw-bg-lap-red tw-px-4 tw-py-2 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">{{ $matchResult->competition_format_label }}</div>
                    </div>

                    <div class="tw-p-6 lg:tw-border-l lg:tw-border-white/10 lg:tw-p-10">
                        <div class="tw-flex tw-items-center tw-gap-4 lg:tw-flex-row-reverse lg:tw-text-right">
                            <div class="tw-flex tw-h-20 tw-w-20 tw-shrink-0 tw-items-center tw-justify-center tw-bg-white">
                                <img class="tw-h-14 tw-w-14 tw-object-contain" src="{{ $matchResult->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-4.svg') }}" alt="{{ $clubBName }}">
                            </div>
                            <div class="tw-min-w-0">
                                <div class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white/52">{{ $clubBShort }}</div>
                                <div class="tw-mt-2 tw-text-3xl tw-font-black tw-leading-none tw-text-white lg:tw-text-4xl">{{ $clubBName }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-grid tw-gap-px tw-bg-white/10 lg:tw-grid-cols-4">
                    <div class="tw-bg-black/20 tw-p-4">
                        <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/55">Tanggal</div>
                        <div class="tw-mt-2 tw-text-lg tw-font-black tw-text-white">{{ optional($matchResult->match_date)->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="tw-bg-black/20 tw-p-4">
                        <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/55">Kickoff</div>
                        <div class="tw-mt-2 tw-text-lg tw-font-black tw-text-white">{{ optional($matchResult->kickoff_time)->format('H:i') ?: '-' }} WIB</div>
                    </div>
                    <div class="tw-bg-black/20 tw-p-4">
                        <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/55">Lokasi</div>
                        <div class="tw-mt-2 tw-text-lg tw-font-black tw-text-white">{{ $matchResult->venue ?: 'Lokasi belum diisi' }}</div>
                    </div>
                    <div class="tw-bg-black/20 tw-p-4">
                        <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/55">Babak</div>
                        <div class="tw-mt-2 tw-text-lg tw-font-black tw-text-white">{{ $matchResult->round_display_label }}</div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="tw-bg-[#efefef] tw-py-12 lg:tw-py-16">
        <div class="container">
            <div class="tw-grid tw-gap-6 xl:tw-grid-cols-[minmax(0,1.35fr)_340px]">
                <div class="tw-space-y-6">
                    <section class="tw-border-l-[8px] tw-border-l-lap-red tw-border-r tw-border-y tw-border-slate-300 tw-bg-white tw-p-6 lg:tw-p-8">
                        <div class="tw-inline-flex tw-items-center tw-bg-slate-950 tw-px-5 tw-py-2 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Ringkasan Pertandingan</div>
                        <p class="tw-mt-6 tw-max-w-3xl tw-text-sm tw-leading-7 tw-text-slate-700">{{ $winnerLabel }}. {{ $matchResult->result_summary }}. Panel ini menampilkan konteks pertandingan dengan format yang lebih mirip match report daripada dashboard widget.</p>

                        @if (filled($matchResult->notes))
                            <div class="tw-mt-5 tw-border-l-4 tw-border-orange-500 tw-bg-orange-50 tw-p-4 tw-text-sm tw-leading-7 tw-text-orange-950">
                                <strong class="tw-font-black">Catatan pertandingan:</strong> {{ $matchResult->notes }}
                            </div>
                        @endif
                    </section>

                    <section class="tw-border-l-[8px] tw-border-l-lap-red tw-border-r tw-border-y tw-border-slate-300 tw-bg-white tw-p-6 lg:tw-p-8">
                        <div class="tw-inline-flex tw-items-center tw-bg-slate-950 tw-px-5 tw-py-2 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Konteks Dua Tim</div>

                        <div class="tw-mt-6 tw-grid tw-gap-4 lg:tw-grid-cols-2">
                            <article class="tw-bg-[#0d1219] tw-p-5 tw-text-white">
                                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/52">{{ $clubAShort }}</div>
                                <h3 class="tw-mt-2 tw-text-2xl tw-font-black tw-leading-none tw-text-white">{{ $clubAName }}</h3>
                                <p class="tw-mt-4 tw-text-sm tw-leading-7 tw-text-white/68">{{ $clubAStats['result_story'] ?? 'Belum ada rekap statistik.' }}</p>
                                <div class="tw-mt-5 tw-grid tw-grid-cols-2 tw-gap-px tw-overflow-hidden tw-border tw-border-white/8 xl:tw-grid-cols-3">
                                    @foreach ([
                                        'Main' => $clubAStats['played'] ?? 0,
                                        'Menang' => $clubAStats['wins'] ?? 0,
                                        'Imbang' => $clubAStats['draws'] ?? 0,
                                        'Kalah' => $clubAStats['losses'] ?? 0,
                                        'Gol' => $clubAStats['goals_for'] ?? 0,
                                        'Kebobolan' => $clubAStats['goals_against'] ?? 0,
                                    ] as $label => $value)
                                        <div class="tw-bg-white/5 tw-p-3">
                                            <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white/50">{{ $label }}</div>
                                            <div class="tw-mt-2 tw-text-2xl tw-font-black tw-leading-none tw-text-white">{{ $value }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>

                            <article class="tw-bg-[#0d1219] tw-p-5 tw-text-white">
                                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/52">{{ $clubBShort }}</div>
                                <h3 class="tw-mt-2 tw-text-2xl tw-font-black tw-leading-none tw-text-white">{{ $clubBName }}</h3>
                                <p class="tw-mt-4 tw-text-sm tw-leading-7 tw-text-white/68">{{ $clubBStats['result_story'] ?? 'Belum ada rekap statistik.' }}</p>
                                <div class="tw-mt-5 tw-grid tw-grid-cols-2 tw-gap-px tw-overflow-hidden tw-border tw-border-white/8 xl:tw-grid-cols-3">
                                    @foreach ([
                                        'Main' => $clubBStats['played'] ?? 0,
                                        'Menang' => $clubBStats['wins'] ?? 0,
                                        'Imbang' => $clubBStats['draws'] ?? 0,
                                        'Kalah' => $clubBStats['losses'] ?? 0,
                                        'Gol' => $clubBStats['goals_for'] ?? 0,
                                        'Kebobolan' => $clubBStats['goals_against'] ?? 0,
                                    ] as $label => $value)
                                        <div class="tw-bg-white/5 tw-p-3">
                                            <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white/50">{{ $label }}</div>
                                            <div class="tw-mt-2 tw-text-2xl tw-font-black tw-leading-none tw-text-white">{{ $value }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="tw-border-l-[8px] tw-border-l-lap-red tw-border-r tw-border-y tw-border-slate-300 tw-bg-white tw-p-6 lg:tw-p-8">
                        <div class="tw-inline-flex tw-items-center tw-bg-slate-950 tw-px-5 tw-py-2 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Timeline Gol</div>
                        @if ($matchResultTimeline->isNotEmpty())
                            <div class="tw-relative tw-mt-6 tw-space-y-4 tw-pl-7 before:tw-absolute before:tw-left-2.5 before:tw-top-0 before:tw-bottom-0 before:tw-w-px before:tw-bg-slate-300 before:tw-content-['']">
                                @foreach ($matchResultTimeline as $timelineItem)
                                    @php
                                        $timelineAccent = $timelineItem['side'] === 'home'
                                            ? 'tw-border-l-blue-600'
                                            : ($timelineItem['side'] === 'away' ? 'tw-border-l-lap-red' : 'tw-border-l-slate-500');
                                    @endphp
                                    <div class="tw-relative">
                                        <span class="tw-absolute -tw-left-[1.8rem] tw-top-5 tw-h-3 tw-w-3 tw-rounded-full tw-bg-slate-950 tw-ring-4 tw-ring-white"></span>
                                        <article class="tw-border-l-[6px] {{ $timelineAccent }} tw-border-r tw-border-y tw-border-slate-300 tw-bg-slate-50 tw-p-4">
                                            <div class="tw-flex tw-flex-wrap tw-items-start tw-justify-between tw-gap-3">
                                                <div>
                                                    <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">{{ $timelineItem['label'] }}</div>
                                                    <div class="tw-mt-2 tw-text-lg tw-font-black tw-text-slate-950">{{ $timelineItem['club_name'] }}</div>
                                                </div>
                                                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 lg:tw-justify-end">
                                                    <span class="tw-inline-flex tw-items-center tw-bg-slate-950 tw-px-3 tw-py-1 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white">#{{ $timelineItem['sequence'] }}</span>
                                                    <span class="tw-inline-flex tw-items-center tw-bg-lap-red tw-px-3 tw-py-1 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white">{{ $timelineItem['score_after'] }}</span>
                                                </div>
                                            </div>
                                            <p class="tw-mt-3 tw-text-sm tw-leading-7 tw-text-slate-600"><strong class="tw-font-black tw-text-slate-950">{{ $timelineItem['scorer'] }}</strong>@if ($timelineItem['assist']) menerima assist dari {{ $timelineItem['assist'] }}@endif</p>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="tw-mt-6 tw-text-sm tw-leading-7 tw-text-slate-600">Belum ada urutan kejadian gol yang dicatat untuk pertandingan ini.</p>
                        @endif
                    </section>

                    <section class="tw-border-l-[8px] tw-border-l-lap-red tw-border-r tw-border-y tw-border-slate-300 tw-bg-white tw-p-6 lg:tw-p-8">
                        <div class="tw-inline-flex tw-items-center tw-bg-slate-950 tw-px-5 tw-py-2 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Rincian Pencetak Gol</div>
                        @if ($matchResult->goalEvents->isNotEmpty())
                            <div class="tw-mt-6 tw-space-y-4">
                                @foreach ([$matchResult->clubA, $matchResult->clubB] as $club)
                                    @php
                                        $goalReport = $matchResult->goalReportForClub($club?->id);
                                    @endphp
                                    @if ($club && !empty($goalReport))
                                        <article class="tw-border-l-[6px] tw-border-l-slate-950 tw-border-r tw-border-y tw-border-slate-300 tw-bg-slate-50 tw-p-4">
                                            <h3 class="tw-text-lg tw-font-black tw-text-slate-950">{{ $club->name ?: $club->short_name }}</h3>
                                            <ul class="tw-mt-3 tw-space-y-2 tw-pl-5 tw-text-sm tw-leading-7 tw-text-slate-600">
                                                @foreach ($goalReport as $goalItem)
                                                    <li>{{ $goalItem }}</li>
                                                @endforeach
                                            </ul>
                                        </article>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="tw-mt-6 tw-text-sm tw-leading-7 tw-text-slate-600">Belum ada rincian pencetak gol yang dicatat untuk pertandingan ini.</p>
                        @endif
                    </section>
                </div>

                <aside class="tw-space-y-6 xl:tw-sticky xl:tw-top-28 xl:tw-self-start">
                    <section class="tw-border-y-4 tw-border-lap-red tw-bg-[#0b1016] tw-p-6 tw-text-white lg:tw-p-8">
                        <div class="tw-flex tw-flex-wrap tw-gap-3">
                            <a href="{{ route('public.results') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-bg-lap-red tw-px-5 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white hover:tw-bg-[#c9151d]">Kembali ke Hasil</a>
                            <a href="{{ route('public.schedule') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-white/12 tw-bg-white/5 tw-px-5 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Lihat Jadwal</a>
                            <button type="button" class="tw-inline-flex tw-items-center tw-justify-center tw-border tw-border-white/12 tw-bg-transparent tw-px-5 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white" data-share-result data-share-url="{{ $matchResultShareUrl }}" data-share-text="{{ $shareText }}">Bagikan Hasil</button>
                        </div>
                        <span class="tw-mt-3 tw-block tw-min-h-[1.25rem] tw-text-sm tw-text-white/60" data-share-feedback></span>

                        <div class="tw-mt-6 tw-grid tw-gap-px tw-overflow-hidden tw-border tw-border-white/10">
                            <div class="tw-bg-black/20 tw-p-4">
                                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/52">Total Gol</div>
                                <div class="tw-mt-2 tw-text-3xl tw-font-black tw-leading-none tw-text-white">{{ $matchResultStats['total_goals'] }}</div>
                            </div>
                            <div class="tw-bg-black/20 tw-p-4">
                                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/52">Pencetak Gol Unik</div>
                                <div class="tw-mt-2 tw-text-3xl tw-font-black tw-leading-none tw-text-white">{{ $matchResultStats['scorer_count'] }}</div>
                            </div>
                            <div class="tw-bg-black/20 tw-p-4">
                                <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-white/52">Clean Sheet</div>
                                <div class="tw-mt-2 tw-text-lg tw-font-black tw-leading-tight tw-text-white">{{ $matchResultStats['clean_sheet'] }}</div>
                            </div>
                        </div>
                    </section>

                    <section class="tw-border-l-[8px] tw-border-l-lap-red tw-border-r tw-border-y tw-border-slate-300 tw-bg-white tw-p-6 lg:tw-p-8">
                        <div class="tw-inline-flex tw-items-center tw-bg-slate-950 tw-px-5 tw-py-2 tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Hasil Lainnya</div>
                        @if ($relatedResults->isNotEmpty())
                            <div class="tw-mt-6 tw-space-y-3">
                                @foreach ($relatedResults as $relatedMatch)
                                    <article class="tw-border tw-border-slate-300 tw-bg-slate-50 tw-p-4">
                                        <div class="tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">{{ optional($relatedMatch->match_date)->translatedFormat('d F Y') }}</div>
                                        <h3 class="tw-mt-2 tw-text-lg tw-font-black tw-leading-snug tw-text-slate-950">{{ $relatedMatch->clubA?->name ?: 'Klub A' }} vs {{ $relatedMatch->clubB?->name ?: 'Klub B' }}</h3>
                                        <p class="tw-mt-2 tw-text-sm tw-text-slate-600">{{ $relatedMatch->score_label }} · {{ $relatedMatch->result_summary }}</p>
                                        <a href="{{ route('public.results.show', ['matchSlug' => $relatedMatch->public_slug]) }}" class="tw-mt-4 tw-inline-flex tw-items-center tw-justify-center tw-bg-slate-950 tw-px-4 tw-py-3 tw-text-[0.68rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white">Buka Detail</a>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p class="tw-mt-6 tw-text-sm tw-leading-7 tw-text-slate-600">Belum ada hasil lain pada kelompok usia yang sama.</p>
                        @endif
                    </section>
                </aside>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shareButton = document.querySelector('[data-share-result]');
            const feedback = document.querySelector('[data-share-feedback]');
            if (!shareButton) return;

            shareButton.addEventListener('click', async function () {
                const shareUrl = shareButton.getAttribute('data-share-url');
                const shareText = shareButton.getAttribute('data-share-text') || 'Hasil pertandingan';

                try {
                    if (navigator.share) {
                        await navigator.share({ title: shareText, text: shareText, url: shareUrl });
                        if (feedback) feedback.textContent = 'Tautan hasil siap dibagikan.';
                        return;
                    }

                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(shareUrl);
                        if (feedback) feedback.textContent = 'Tautan hasil berhasil disalin.';
                        return;
                    }

                    window.prompt('Salin tautan hasil ini:', shareUrl);
                } catch (error) {
                    if (feedback) feedback.textContent = 'Tautan belum berhasil dibagikan. Silakan coba lagi.';
                }
            });
        });
    </script>
@endsection

@extends('public.layout')

@php
    $selectedAgeGroupId = $resultFilters['age_group_id'] ?? null;
    $selectedKeyword = $resultFilters['q'] ?? '';
    $selectedDateFrom = $resultFilters['date_from'] ?? null;
    $selectedDateTo = $resultFilters['date_to'] ?? null;

    $normalizeResult = function ($match): array {
        $homeName = $match->clubA?->name ?: $match->clubA?->short_name ?: 'Klub A';
        $awayName = $match->clubB?->name ?: $match->clubB?->short_name ?: 'Klub B';
        $homeShort = $match->clubA?->short_name ?: 'HOME';
        $awayShort = $match->clubB?->short_name ?: 'AWAY';
        $homeScore = (int) ($match->score_club_a ?? 0);
        $awayScore = (int) ($match->score_club_b ?? 0);
        $status = $match->is_finished ? 'FT' : 'LIVE';

        return [
            'date_short' => optional($match->match_date)->translatedFormat('d M') ?: '-- ---',
            'date_full' => optional($match->match_date)->translatedFormat('d F Y') ?: 'Tanggal belum diatur',
            'time' => optional($match->kickoff_time)->format('H:i') ? optional($match->kickoff_time)->format('H:i').' WIB' : '- WIB',
            'age_group' => $match->ageGroup?->name ?: '-',
            'home_name' => $homeName,
            'home_short' => $homeShort,
            'home_logo' => $match->clubA?->logo_file_url ?: asset('kester-assets/images/icons/club-3.svg'),
            'home_score' => $homeScore,
            'home_outcome' => $homeScore > $awayScore ? 'Menang' : ($homeScore === $awayScore ? 'Imbang' : 'Kalah'),
            'away_name' => $awayName,
            'away_short' => $awayShort,
            'away_logo' => $match->clubB?->logo_file_url ?: asset('kester-assets/images/icons/club-4.svg'),
            'away_score' => $awayScore,
            'away_outcome' => $awayScore > $homeScore ? 'Menang' : ($homeScore === $awayScore ? 'Imbang' : 'Kalah'),
            'score' => $match->score_label,
            'status' => $status,
            'status_label' => $status === 'FT' ? 'Selesai' : 'Sedang Berjalan',
            'venue' => $match->venue ?: 'Lokasi belum diisi',
            'summary' => $match->result_summary,
            'detail_url' => route('public.results.show', ['matchSlug' => $match->public_slug]),
            'is_dummy' => false,
        ];
    };

    $dummyResults = collect([
        [
            'date_short' => '12 Mei',
            'date_full' => '12 Mei 2026',
            'time' => '15:30 WIB',
            'age_group' => 'U-12',
            'home_name' => 'Garuda Muda FC',
            'home_short' => 'GMF',
            'home_logo' => asset('kester-assets/images/icons/club-3.svg'),
            'home_score' => 3,
            'home_outcome' => 'Menang',
            'away_name' => 'Elang Junior',
            'away_short' => 'ELG',
            'away_logo' => asset('kester-assets/images/icons/club-4.svg'),
            'away_score' => 1,
            'away_outcome' => 'Kalah',
            'score' => '3 - 1',
            'status' => 'FT',
            'status_label' => 'Selesai',
            'venue' => 'Stadion Piaman Utama',
            'summary' => 'Garuda Muda FC menang meyakinkan lewat tekanan sejak babak pertama.',
            'detail_url' => '#',
            'is_dummy' => true,
        ],
        [
            'date_short' => '12 Mei',
            'date_full' => '12 Mei 2026',
            'time' => '17:00 WIB',
            'age_group' => 'U-12',
            'home_name' => 'Rajawali Soccer School',
            'home_short' => 'RJS',
            'home_logo' => asset('kester-assets/images/icons/club-4.svg'),
            'home_score' => 2,
            'home_outcome' => 'Imbang',
            'away_name' => 'Bintang Pesisir',
            'away_short' => 'BPS',
            'away_logo' => asset('kester-assets/images/icons/club-3.svg'),
            'away_score' => 2,
            'away_outcome' => 'Imbang',
            'score' => '2 - 2',
            'status' => 'FT',
            'status_label' => 'Selesai',
            'venue' => 'Lapangan Andalas',
            'summary' => 'Pertandingan ketat berakhir imbang setelah gol penyeimbang di penghujung laga.',
            'detail_url' => '#',
            'is_dummy' => true,
        ],
        [
            'date_short' => '13 Mei',
            'date_full' => '13 Mei 2026',
            'time' => '14:30 WIB',
            'age_group' => 'U-14',
            'home_name' => 'Satria Minang Academy',
            'home_short' => 'SMA',
            'home_logo' => asset('kester-assets/images/icons/club-3.svg'),
            'home_score' => 0,
            'home_outcome' => 'Kalah',
            'away_name' => 'Harimau Barat FC',
            'away_short' => 'HBF',
            'away_logo' => asset('kester-assets/images/icons/club-4.svg'),
            'away_score' => 2,
            'away_outcome' => 'Menang',
            'score' => '0 - 2',
            'status' => 'FT',
            'status_label' => 'Selesai',
            'venue' => 'Stadion Piaman Utama',
            'summary' => 'Harimau Barat FC tampil disiplin dan menutup laga dengan clean sheet.',
            'detail_url' => '#',
            'is_dummy' => true,
        ],
        [
            'date_short' => '13 Mei',
            'date_full' => '13 Mei 2026',
            'time' => '16:15 WIB',
            'age_group' => 'U-14',
            'home_name' => 'Persada Talenta',
            'home_short' => 'PST',
            'home_logo' => asset('kester-assets/images/icons/club-4.svg'),
            'home_score' => 4,
            'home_outcome' => 'Menang',
            'away_name' => 'Tunas Laut United',
            'away_short' => 'TLU',
            'away_logo' => asset('kester-assets/images/icons/club-3.svg'),
            'away_score' => 3,
            'away_outcome' => 'Kalah',
            'score' => '4 - 3',
            'status' => 'FT',
            'status_label' => 'Selesai',
            'venue' => 'Lapangan Bahari',
            'summary' => 'Tujuh gol tercipta dalam laga terbuka dengan tempo tinggi sepanjang babak kedua.',
            'detail_url' => '#',
            'is_dummy' => true,
        ],
        [
            'date_short' => '14 Mei',
            'date_full' => '14 Mei 2026',
            'time' => '15:45 WIB',
            'age_group' => 'U-16',
            'home_name' => 'Piaman Elite Youth',
            'home_short' => 'PEY',
            'home_logo' => asset('kester-assets/images/icons/club-3.svg'),
            'home_score' => 1,
            'home_outcome' => 'Imbang',
            'away_name' => 'Mutiara Selatan',
            'away_short' => 'MTS',
            'away_logo' => asset('kester-assets/images/icons/club-4.svg'),
            'away_score' => 1,
            'away_outcome' => 'Imbang',
            'score' => '1 - 1',
            'status' => 'FT',
            'status_label' => 'Selesai',
            'venue' => 'Stadion Piaman Utama',
            'summary' => 'Dua tim berbagi poin setelah saling menekan namun gagal menemukan gol tambahan.',
            'detail_url' => '#',
            'is_dummy' => true,
        ],
    ]);

    $actualRows = $recentResults->getCollection()->values();
    $featuredMatch = $featuredResult ? $normalizeResult($featuredResult) : $dummyResults->first();
    $resultRows = $actualRows
        ->reject(fn ($match) => $featuredResult && (int) $match->id === (int) $featuredResult->id)
        ->map($normalizeResult)
        ->values();

    if ($resultRows->isEmpty()) {
        $resultRows = $dummyResults->slice(1)->values();
    }

    $displayResultCount = $actualRows->isNotEmpty() ? $recentResults->total() : $dummyResults->count();

    $paginationPages = collect();

    if ($recentResults->hasPages()) {
        $paginationPages = collect([
            1,
            $recentResults->currentPage() - 1,
            $recentResults->currentPage(),
            $recentResults->currentPage() + 1,
            $recentResults->lastPage(),
        ])->filter(fn ($page) => $page >= 1 && $page <= $recentResults->lastPage())
            ->unique()
            ->values();
    }
@endphp

@section('content')
    <div class="tw-bg-[#f4f6fa] tw-py-8 lg:tw-py-12">
        <div class="tw-mx-auto tw-w-full tw-max-w-[1320px] tw-space-y-10 tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <section class="tw-space-y-6">
                <div class="tw-flex tw-flex-col tw-gap-5 lg:tw-flex-row lg:tw-items-end lg:tw-justify-between">
                    <div class="tw-max-w-4xl">
                        <p class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.28em] tw-text-lap-red">Liga Sepak Bola</p>
                        <h2 class="tw-mt-3 tw-font-display tw-text-3xl tw-font-black tw-leading-[0.96] tw-tracking-[-0.04em] tw-text-slate-950 lg:tw-text-4xl">Pantau hasil terbaru dengan format match center.</h2>
                        <p class="tw-mt-4 tw-max-w-2xl tw-text-sm tw-leading-7 tw-text-slate-600 lg:tw-text-base">Banner hero publik tetap tampil di atas. Di bawahnya, filter dan papan skor utama tetap dipertahankan agar halaman hasil tetap cepat dipakai.</p>
                    </div>
                    <div class="tw-border-l-4 tw-border-l-lap-red tw-pl-4 lg:tw-text-right">
                        <div class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.24em] tw-text-slate-500">Arsip Aktif</div>
                        <div class="tw-mt-2 tw-text-4xl tw-font-black tw-leading-none tw-text-slate-950">{{ $displayResultCount }}</div>
                        <div class="tw-mt-2 tw-text-sm tw-text-slate-500">hasil pertandingan</div>
                    </div>
                </div>

                @include('public.results.filter-bar', [
                    'resultAgeGroups' => $resultAgeGroups,
                    'selectedAgeGroupId' => $selectedAgeGroupId,
                    'selectedDateFrom' => $selectedDateFrom,
                    'selectedDateTo' => $selectedDateTo,
                    'selectedKeyword' => $selectedKeyword,
                ])

            </section>

            <div class="tw-relative tw-isolate tw-overflow-hidden tw-border-y tw-border-slate-900/10 tw-bg-[radial-gradient(circle_at_top,rgba(228,27,35,0.16),transparent_26%),linear-gradient(180deg,#08111d_0%,#0d1624_38%,#0a1320_100%)]">
                <div class="tw-absolute tw-inset-x-0 tw-top-0 tw-h-px tw-bg-white/10"></div>
                <div class="tw-absolute tw-inset-x-0 tw-bottom-0 tw-h-px tw-bg-white/10"></div>
                <div class="tw-absolute tw-left-1/2 tw-top-0 tw-h-56 tw-w-56 tw--translate-x-1/2 tw-rounded-full tw-bg-lap-red/10 tw-blur-3xl"></div>

                <div class="tw-relative tw-space-y-8 tw-py-8 lg:tw-space-y-10 lg:tw-py-10">
                    @include('public.results.featured-match', ['match' => $featuredMatch])

                    <section aria-labelledby="result-list-heading" class="tw-space-y-5">
                        <div class="tw-flex tw-flex-col tw-gap-4 lg:tw-flex-row lg:tw-items-end lg:tw-justify-between">
                            <div class="tw-max-w-3xl">
                                <p class="tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.28em] tw-text-lap-red">Result Feed</p>
                                <h2 id="result-list-heading" class="tw-mt-3 tw-font-display tw-text-3xl tw-font-black tw-tracking-[-0.03em] tw-text-white lg:tw-text-4xl">Daftar Hasil Pertandingan</h2>
                                <p class="tw-mt-2 tw-text-sm tw-leading-7 tw-text-slate-300">Setiap hasil ditampilkan sebagai row livescore yang ringan, tegas, dan cepat dipindai saat pengguna scroll panjang.</p>
                            </div>

                            <div class="tw-flex tw-items-center tw-gap-3 tw-text-sm tw-text-slate-300">
                                <span class="tw-inline-flex tw-items-center tw-gap-2">
                                    <span class="tw-h-2 tw-w-2 tw-rounded-full tw-bg-lap-red"></span>
                                    {{ $displayResultCount }} arsip hasil
                                </span>
                                @if ($recentResults->hasPages())
                                    <span class="tw-hidden tw-h-4 tw-w-px tw-bg-white/10 sm:tw-block"></span>
                                    <span>Menampilkan {{ $recentResults->firstItem() }}-{{ $recentResults->lastItem() }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="tw-border-y tw-border-white/10 tw-bg-white/[0.02]">
                            <ul class="tw-divide-y tw-divide-white/10" role="list">
                                @foreach ($resultRows as $row)
                                    @include('public.results.result-row', ['row' => $row])
                                @endforeach
                            </ul>
                        </div>

                        @if ($recentResults->hasPages())
                            <nav aria-label="Navigasi halaman hasil pertandingan" class="tw-flex tw-flex-col tw-gap-4 tw-border-t tw-border-white/10 tw-pt-5 lg:tw-flex-row lg:tw-items-center lg:tw-justify-between">
                                <p class="tw-text-sm tw-text-slate-400">Halaman {{ $recentResults->currentPage() }} dari {{ $recentResults->lastPage() }} untuk arsip hasil pertandingan.</p>

                                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                                    @if ($recentResults->onFirstPage())
                                        <span class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-500">Prev</span>
                                    @else
                                        <a href="{{ $recentResults->previousPageUrl() }}" class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white hover:tw-border-lap-red hover:tw-text-lap-red">Prev</a>
                                    @endif

                                    @php $previousPaginationPage = null; @endphp
                                    @foreach ($paginationPages as $page)
                                        @if (! is_null($previousPaginationPage) && $page - $previousPaginationPage > 1)
                                            <span class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-px-2 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-500">...</span>
                                        @endif

                                        @if ($page === $recentResults->currentPage())
                                            <span aria-current="page" class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-border tw-border-lap-red tw-bg-lap-red tw-px-4 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white">{{ $page }}</span>
                                        @else
                                            <a href="{{ $recentResults->url($page) }}" class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white hover:tw-border-lap-red hover:tw-text-lap-red">{{ $page }}</a>
                                        @endif

                                        @php $previousPaginationPage = $page; @endphp
                                    @endforeach

                                    @if ($recentResults->hasMorePages())
                                        <a href="{{ $recentResults->nextPageUrl() }}" class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-white hover:tw-border-lap-red hover:tw-text-lap-red">Next</a>
                                    @else
                                        <span class="tw-inline-flex tw-min-w-[44px] tw-items-center tw-justify-center tw-border tw-border-white/10 tw-px-4 tw-py-2 tw-text-xs tw-font-black tw-uppercase tw-tracking-[0.18em] tw-text-slate-500">Next</span>
                                    @endif
                                </div>
                            </nav>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-results-filter-form]')?.closest('form');
            if (!form) return;

            const submitForm = () => {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                    return;
                }

                form.submit();
            };

            form.querySelectorAll('[data-results-auto-submit]').forEach((element) => {
                element.addEventListener('change', submitForm);
            });

            const searchField = form.querySelector('[data-results-auto-search]');
            if (!searchField) return;

            let timer = null;

            searchField.addEventListener('input', function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(submitForm, 400);
            });
        });
    </script>
@endsection

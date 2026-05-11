@extends('public.public-layout')

@push('styles')
    <style>
        .lap-public .lap-logo-frame .lap-results-club-mark {
            align-items: center;
            background: #ffffff;
            border-radius: 50%;
            color: #030523;
            display: inline-flex;
            font-family: 'Big Shoulders', sans-serif;
            font-size: clamp(1rem, 1.8vw, 1.5rem);
            font-weight: 800;
            height: 100%;
            justify-content: center;
            letter-spacing: .08em;
            line-height: 1;
            text-transform: uppercase;
            width: 100%;
        }
    </style>
@endpush

@php
    $filters = $filters ?? $resultFilters ?? [];
    $ageGroups = $ageGroups ?? $resultAgeGroups ?? collect();
    $resultFormatOptions = $resultFormatOptions ?? [];
    $filterActionUrl = $filterActionUrl ?? route('public.results');
    $resetUrl = route('public.results');

    $selectedClub = $selectedClub ?? $filters['q'] ?? '';
    $selectedAgeGroupId = $filters['age_group_id'] ?? $selectedAgeGroupId ?? null;
    $selectedCompetitionFormat = $filters['competition_format'] ?? $selectedCompetitionFormat ?? null;
    $selectedDateFrom = $filters['date_from'] ?? $selectedDateFrom ?? $selectedDate ?? null;
    $selectedDateTo = $filters['date_to'] ?? $selectedDateTo ?? null;
    $selectedStatus = $selectedStatus ?? $filters['status'] ?? request('status');
    $resultStats = $resultStats ?? [];

    $sanitizeImageUrl = function ($imageUrl): ?string {
        if (! filled($imageUrl)) {
            return null;
        }

        $path = parse_url((string) $imageUrl, PHP_URL_PATH) ?: '';

        if (str_contains($path, '/kester-assets/images/icons/club-') || str_contains($path, '/public-assets/img/inner/flag/')) {
            return null;
        }

        return (string) $imageUrl;
    };

    $clubLogoUrl = function ($club) use ($sanitizeImageUrl): ?string {
        if (! $club) {
            return null;
        }

        if (filled($club->logo_file_url)) {
            return $sanitizeImageUrl($club->logo_file_url);
        }

        if (filled($club->logo_url)) {
            return $sanitizeImageUrl(str_starts_with($club->logo_url, 'http')
                ? $club->logo_url
                : url('/storage/'.ltrim($club->logo_url, '/')));
        }

        return null;
    };
    $resultsSource = $matches ?? $recentResults ?? collect();
    $featuredSource = $featuredMatch ?? $featuredResult ?? null;

    $statusLabelMap = [
        'FT' => 'FT',
        'LIVE' => 'Live',
        'SCHEDULED' => 'Scheduled',
    ];

    $formatDate = function ($value, string $format, string $fallback): string {
        if (blank($value)) {
            return $fallback;
        }

        if ($value instanceof \Carbon\CarbonInterface || $value instanceof \DateTimeInterface) {
            return \Illuminate\Support\Carbon::instance($value)->translatedFormat($format);
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->translatedFormat($format);
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $formatTime = function ($value, string $fallback = 'Kickoff TBD'): string {
        if (blank($value)) {
            return $fallback;
        }

        if ($value instanceof \Carbon\CarbonInterface || $value instanceof \DateTimeInterface) {
            return \Illuminate\Support\Carbon::instance($value)->format('H:i').' WIB';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('H:i').' WIB';
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $normalizeStatus = function ($value, $fallbackFinished = false): string {
        $status = strtoupper((string) ($value ?: ($fallbackFinished ? 'FT' : 'SCHEDULED')));

        return match ($status) {
            'FINISHED', 'FULL_TIME' => 'FT',
            'UPCOMING' => 'SCHEDULED',
            default => $status,
        };
    };

    $normalizeMatch = function ($match) use ($clubLogoUrl, $sanitizeImageUrl, $formatDate, $formatTime, $normalizeStatus): ?array {
        if (blank($match)) {
            return null;
        }

        $competitionFormat = strtolower((string) data_get($match, 'competition_format'));

        $homeName = data_get($match, 'home_name')
            ?: data_get($match, 'clubA.name')
            ?: data_get($match, 'clubA.short_name')
            ?: 'Klub Home';
        $awayName = data_get($match, 'away_name')
            ?: data_get($match, 'clubB.name')
            ?: data_get($match, 'clubB.short_name')
            ?: 'Klub Away';

        $homeScore = (int) data_get($match, 'home_score', data_get($match, 'score_club_a', 0));
        $awayScore = (int) data_get($match, 'away_score', data_get($match, 'score_club_b', 0));
        $matchDateValue = data_get($match, 'match_date', data_get($match, 'date_full'));
        $status = $normalizeStatus(data_get($match, 'status'), (bool) data_get($match, 'is_finished'));
        $detailUrl = data_get($match, 'detail_url');
        $publicSlug = data_get($match, 'public_slug');

        if (! $detailUrl && filled($publicSlug)) {
            $detailUrl = route('public.results.show', ['matchSlug' => $publicSlug]);
        }

        return [
            'key' => (string) (data_get($match, 'id') ?: $publicSlug ?: md5($homeName.$awayName.$matchDateValue.$homeScore.$awayScore)),
            'date_short' => data_get($match, 'date_short') ?: $formatDate($matchDateValue, 'd M', '-- ---'),
            'date_full' => data_get($match, 'date_full') ?: $formatDate($matchDateValue, 'd F Y', 'Tanggal belum tersedia'),
            'time' => data_get($match, 'time') ?: $formatTime(data_get($match, 'kickoff_time')),
            'age_group' => data_get($match, 'age_group') ?: data_get($match, 'ageGroup.name') ?: '-',
            'competition_format' => $competitionFormat,
            'competition_format_label' => data_get($match, 'competition_format_label') ?: match ($competitionFormat) {
                'knockout' => 'Knockout',
                'league' => 'Liga',
                default => 'Arsip resmi',
            },
            'home_name' => $homeName,
            'home_short' => data_get($match, 'home_short') ?: data_get($match, 'clubA.short_name') ?: 'HOME',
            'home_logo' => $sanitizeImageUrl(data_get($match, 'home_logo')) ?: $clubLogoUrl(data_get($match, 'clubA')),
            'away_name' => $awayName,
            'away_short' => data_get($match, 'away_short') ?: data_get($match, 'clubB.short_name') ?: 'AWAY',
            'away_logo' => $sanitizeImageUrl(data_get($match, 'away_logo')) ?: $clubLogoUrl(data_get($match, 'clubB')),
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'score' => data_get($match, 'score') ?: data_get($match, 'score_label') ?: $homeScore.' - '.$awayScore,
            'status' => $status,
            'status_label' => match ($status) {
                'LIVE' => 'Sedang berlangsung',
                'SCHEDULED' => 'Belum dimulai',
                default => 'Full time',
            },
            'venue' => data_get($match, 'venue') ?: 'Venue belum tersedia',
            'detail_url' => $detailUrl,
            'summary' => data_get($match, 'summary', data_get($match, 'result_summary')),
            'date_group' => data_get($match, 'date_full') ?: $formatDate($matchDateValue, 'l, d F Y', 'Arsip pertandingan'),
        ];
    };

    $resultCollection = method_exists($resultsSource, 'getCollection')
        ? $resultsSource->getCollection()->values()
        : collect($resultsSource)->values();

    $normalizedFeatured = $featuredSource ? $normalizeMatch($featuredSource) : null;
    $normalizedMatches = $resultCollection->map($normalizeMatch)->filter()->values();

    $availableStatuses = collect([$normalizedFeatured])
        ->filter()
        ->merge($normalizedMatches)
        ->pluck('status')
        ->filter()
        ->unique()
        ->values();

    $selectedStatus = filled($selectedStatus) ? $normalizeStatus($selectedStatus) : null;

    if (filled($selectedStatus) && ! $availableStatuses->contains($selectedStatus)) {
        $selectedStatus = null;
    }

    $statusOptions = collect([['value' => '', 'label' => 'Semua status']])
        ->merge($availableStatuses->map(fn ($status) => [
            'value' => $status,
            'label' => $statusLabelMap[$status] ?? $status,
        ]))
        ->values()
        ->all();

    if (filled($selectedStatus)) {
        $normalizedMatches = $normalizedMatches
            ->filter(fn ($match) => $match['status'] === $selectedStatus)
            ->values();

        if ($normalizedFeatured && $normalizedFeatured['status'] !== $selectedStatus) {
            $normalizedFeatured = null;
        }
    }

    if (! $normalizedFeatured && $normalizedMatches->isNotEmpty()) {
        $normalizedFeatured = $normalizedMatches->first();
    }

    $feedMatches = $normalizedMatches;
    $groupedMatches = $feedMatches->groupBy('date_group');
    $activeArchiveCount = $activeArchiveCount ?? (method_exists($resultsSource, 'total') ? $resultsSource->total() : $normalizedMatches->count());
    $activeFilterCount = collect([$selectedClub, $selectedAgeGroupId, $selectedCompetitionFormat, $selectedDateFrom, $selectedDateTo, $selectedStatus])
        ->filter(fn ($value) => filled($value))
        ->count();

    $selectedAgeGroupLabel = collect($ageGroups)
        ->first(fn ($ageGroup) => (string) data_get($ageGroup, 'id') === (string) $selectedAgeGroupId);
    $selectedAgeGroupLabel = data_get($selectedAgeGroupLabel, 'name');
    $selectedCompetitionFormatLabel = filled($selectedCompetitionFormat)
        ? ($resultFormatOptions[$selectedCompetitionFormat] ?? ucfirst((string) $selectedCompetitionFormat))
        : null;

    $selectedDateWindow = match (true) {
        filled($selectedDateFrom) && filled($selectedDateTo) => $formatDate($selectedDateFrom, 'd M', '...').' - '.$formatDate($selectedDateTo, 'd M Y', '...'),
        filled($selectedDateFrom) => 'Mulai '.$formatDate($selectedDateFrom, 'd M Y', '...'),
        filled($selectedDateTo) => 'Sampai '.$formatDate($selectedDateTo, 'd M Y', '...'),
        default => null,
    };

    $totalGoals = (int) data_get($resultStats, 'goals', $normalizedMatches->sum(fn ($match) => (int) $match['home_score'] + (int) $match['away_score']));
    $cleanSheetCount = (int) data_get($resultStats, 'clean_sheets', $normalizedMatches->filter(fn ($match) => (int) $match['home_score'] === 0 || (int) $match['away_score'] === 0)->count());
    $summaryMetrics = [
        [
            'label' => 'Arsip aktif',
            'value' => number_format($activeArchiveCount),
            'caption' => $activeFilterCount > 0 ? 'hasil resmi sesuai filter saat ini' : 'hasil resmi siap dipindai publik',
        ],
        [
            'label' => 'Gol tercatat',
            'value' => number_format($totalGoals),
            'caption' => 'total gol pada arsip yang sedang tampil',
        ],
        [
            'label' => 'Clean sheet',
            'value' => number_format($cleanSheetCount),
            'caption' => $cleanSheetCount > 0 ? 'laga tanpa kebobolan dari salah satu tim' : 'belum ada clean sheet di arsip aktif',
        ],
    ];
    $activeFilterLabels = collect([
        filled($selectedClub) ? 'Klub: '.$selectedClub : null,
        filled($selectedAgeGroupLabel) ? 'Kategori: '.$selectedAgeGroupLabel : null,
        filled($selectedCompetitionFormatLabel) ? 'Format: '.$selectedCompetitionFormatLabel : null,
        filled($selectedDateWindow) ? 'Periode: '.$selectedDateWindow : null,
        filled($selectedStatus) ? 'Status: '.($statusLabelMap[$selectedStatus] ?? $selectedStatus) : null,
    ])->filter()->values()->all();
    $archiveStatement = $activeFilterCount > 0
        ? 'Arsip sedang dipersempit supaya keluarga dan panitia bisa langsung fokus ke pertandingan yang dicari.'
        : 'Semua hasil di halaman ini berasal dari laga yang sudah selesai dan siap dibagikan sebagai arsip resmi kompetisi.';
    $paginationPages = collect();

    if (method_exists($resultsSource, 'hasPages') && $resultsSource->hasPages()) {
        $paginationPages = collect([
            1,
            $resultsSource->currentPage() - 1,
            $resultsSource->currentPage(),
            $resultsSource->currentPage() + 1,
            $resultsSource->lastPage(),
        ])->filter(fn ($page) => $page >= 1 && $page <= $resultsSource->lastPage())
            ->unique()
            ->values();
    }
@endphp

@section('content')
    <div class="lap-page-shell is-dark">
        <div class="container">
            <section class="lap-dark-card lap-spacer-bottom wow fadeInUp" data-wow-delay=".15s">
                <span class="lap-section-kicker">Arsip Hasil</span>
                <span class="visually-hidden">Daftar Hasil Pertandingan</span>
                <h2 class="lap-card-title">Skor resmi dan ringkasan pertandingan yang sudah selesai</h2>
                <p class="lap-card-copy">Gunakan halaman ini untuk memindai skor akhir, kategori usia, venue, dan detail pertandingan yang siap dibagikan ke publik.</p>

                @if ($normalizedFeatured)
                    <div class="lap-spacer-top">
                        <div class="lap-card-meta">
                            <span>{{ strtoupper($normalizedFeatured['competition_format_label']) }}</span>
                            <span>{{ strtoupper($normalizedFeatured['age_group']) }}</span>
                            <span>{{ strtoupper($normalizedFeatured['status_label']) }}</span>
                        </div>
                        <div class="lap-scoreboard">
                            <div class="lap-team-stack">
                                <span class="lap-logo-frame">@include('public.partials.identity-mark', ['imageUrl' => $normalizedFeatured['home_logo'], 'label' => $normalizedFeatured['home_name'], 'badgeClass' => 'lap-results-club-mark', 'width' => 72, 'height' => 72])</span>
                                <h3 class="lap-team-name">{{ $normalizedFeatured['home_name'] }}</h3>
                            </div>
                            <div class="text-center">
                                <div class="lap-score-pill">{{ $normalizedFeatured['score'] }}</div>
                                <p class="lap-card-copy mt-3">{{ $normalizedFeatured['date_full'] }}</p>
                                <p class="lap-card-copy">{{ $normalizedFeatured['time'] }} · {{ $normalizedFeatured['venue'] }}</p>
                            </div>
                            <div class="lap-team-stack is-away">
                                <span class="lap-logo-frame">@include('public.partials.identity-mark', ['imageUrl' => $normalizedFeatured['away_logo'], 'label' => $normalizedFeatured['away_name'], 'badgeClass' => 'lap-results-club-mark', 'width' => 72, 'height' => 72])</span>
                                <h3 class="lap-team-name">{{ $normalizedFeatured['away_name'] }}</h3>
                            </div>
                        </div>
                        <p class="lap-card-copy lap-spacer-top">{{ $normalizedFeatured['summary'] ?: 'Skor akhir resmi telah dicatat pada arsip kompetisi.' }}</p>
                        @if ($normalizedFeatured['detail_url'])
                            <div class="lap-spacer-top">
                                <a href="{{ $normalizedFeatured['detail_url'] }}" class="theme-btn">Lihat Detail Hasil <i class="fa-solid fa-arrow-up-right"></i></a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="lap-empty-card lap-spacer-top">
                        <h3 class="lap-card-title-sm">Belum ada hasil yang bisa disorot</h3>
                        <p class="lap-card-copy">Sorotan utama akan otomatis terisi ketika pertandingan selesai dan skor akhir sudah dicatat.</p>
                    </div>
                @endif
            </section>

            <div class="lap-result-layout">
                <div>
                    <section class="lap-filter-shell lap-spacer-bottom wow fadeInUp" data-wow-delay=".2s">
                        <span class="lap-section-kicker">Filter Arsip</span>
                        <span class="visually-hidden">Kontrol pencarian hasil pertandingan</span>
                        <h3 class="lap-card-title-sm">Persempit hasil yang ingin ditinjau</h3>
                        <form method="GET" action="{{ $filterActionUrl }}" class="lap-spacer-top">
                            <div class="lap-form-grid">
                                <div class="lap-form-group is-span-2">
                                    <label for="result-q">Cari klub</label>
                                    <input id="result-q" type="text" name="q" value="{{ $selectedClub }}" placeholder="Nama klub">
                                </div>
                                <div class="lap-form-group">
                                    <label for="result-status">Status</label>
                                    <select id="result-status" name="status">
                                        @foreach ($statusOptions as $statusOption)
                                            <option value="{{ $statusOption['value'] }}" @selected((string) $selectedStatus === (string) $statusOption['value'])>{{ $statusOption['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lap-form-group">
                                    <label for="result-age-group">Kelompok usia</label>
                                    <select id="result-age-group" name="age_group_id">
                                        <option value="">Semua kategori</option>
                                        @foreach ($ageGroups as $ageGroup)
                                            <option value="{{ $ageGroup->id }}" @selected((string) $selectedAgeGroupId === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lap-form-group">
                                    <label for="result-format">Format</label>
                                    <select id="result-format" name="competition_format">
                                        <option value="">Semua format</option>
                                        @foreach ($resultFormatOptions as $formatValue => $formatLabel)
                                            <option value="{{ $formatValue }}" @selected((string) $selectedCompetitionFormat === (string) $formatValue)>{{ $formatLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lap-form-group">
                                    <label for="result-date-from">Dari tanggal</label>
                                    <input id="result-date-from" type="date" name="date_from" value="{{ $selectedDateFrom }}">
                                </div>
                                <div class="lap-form-group">
                                    <label for="result-date-to">Sampai tanggal</label>
                                    <input id="result-date-to" type="date" name="date_to" value="{{ $selectedDateTo }}">
                                </div>
                            </div>
                            <div class="lap-form-actions">
                                <button type="submit" class="theme-btn">Terapkan Filter <i class="fa-solid fa-arrow-up-right"></i></button>
                                <a href="{{ $resetUrl }}" class="theme-btn style-border">Reset Arsip</a>
                            </div>
                        </form>
                    </section>

                    @forelse ($groupedMatches as $dateGroup => $matches)
                        <section class="lap-result-group wow fadeInUp" data-wow-delay=".25s">
                            <div class="lap-light-card">
                                <span class="lap-section-kicker">{{ $dateGroup }}</span>
                                <div class="lap-list-stack lap-spacer-top">
                                    @foreach ($matches as $match)
                                        <article class="lap-result-card wow fadeInUp" data-wow-delay=".35s">
                                            <div class="lap-result-meta">
                                                <span>{{ strtoupper($match['competition_format_label']) }}</span>
                                                <span>{{ strtoupper($match['age_group']) }}</span>
                                                <span>{{ strtoupper($match['status_label']) }}</span>
                                            </div>
                                            <div class="lap-match-teams">
                                                <div class="lap-team-stack">
                                                    <span class="lap-logo-frame">@include('public.partials.identity-mark', ['imageUrl' => $match['home_logo'], 'label' => $match['home_name'], 'badgeClass' => 'lap-results-club-mark', 'width' => 72, 'height' => 72])</span>
                                                    <strong class="lap-team-name">{{ $match['home_name'] }}</strong>
                                                </div>
                                                <div class="text-center">
                                                    <div class="lap-result-score">@include('public.partials.match-score', ['homeScore' => $match['home_score'], 'awayScore' => $match['away_score'], 'separator' => ' - '])</div>
                                                    <p class="lap-card-copy">{{ $match['time'] }}</p>
                                                    <p class="lap-card-copy">{{ $match['venue'] }}</p>
                                                </div>
                                                <div class="lap-team-stack is-away">
                                                    <span class="lap-logo-frame">@include('public.partials.identity-mark', ['imageUrl' => $match['away_logo'], 'label' => $match['away_name'], 'badgeClass' => 'lap-results-club-mark', 'width' => 72, 'height' => 72])</span>
                                                    <strong class="lap-team-name">{{ $match['away_name'] }}</strong>
                                                </div>
                                            </div>
                                            <p class="lap-result-summary">{{ $match['summary'] ?: 'Skor akhir resmi tersedia di arsip pertandingan.' }}</p>
                                            @if ($match['detail_url'])
                                                <div class="lap-spacer-top">
                                                    <a href="{{ $match['detail_url'] }}" class="lap-card-link">Lihat detail pertandingan <i class="fa-solid fa-arrow-right"></i></a>
                                                </div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @empty
                        <section class="wow fadeInUp" data-wow-delay=".25s">
                            <div class="lap-empty-card">
                                <h3 class="lap-card-title-sm">Belum ada hasil yang cocok</h3>
                                <p class="lap-card-copy">Coba longgarkan filter pencarian atau kembali ke arsip utama untuk melihat seluruh hasil yang tersedia.</p>
                            </div>
                        </section>
                    @endforelse

                    @if ($paginationPages->isNotEmpty())
                        <div class="lap-pagination wow fadeInUp" data-wow-delay=".25s">
                            @if ($resultsSource->onFirstPage())
                                <span>&laquo;</span>
                            @else
                                <a href="{{ $resultsSource->previousPageUrl() }}">&laquo;</a>
                            @endif

                            @foreach ($paginationPages as $page)
                                @if ($page === $resultsSource->currentPage())
                                    <span class="is-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $resultsSource->url($page) }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($resultsSource->hasMorePages())
                                <a href="{{ $resultsSource->nextPageUrl() }}">&raquo;</a>
                            @else
                                <span>&raquo;</span>
                            @endif
                        </div>
                    @endif
                </div>

                <aside class="lap-section-stack">
                    @foreach ($summaryMetrics as $metric)
                        <article class="lap-stat-card">
                            <div class="lap-value">{{ $metric['value'] }}</div>
                            <span class="lap-label">{{ $metric['label'] }}</span>
                            <span class="lap-caption">{{ $metric['caption'] }}</span>
                        </article>
                    @endforeach

                    <section class="lap-side-card">
                        <span class="lap-section-kicker">Ringkasan Arsip</span>
                        <h3 class="lap-card-title-sm">Status filter saat ini</h3>
                        <p class="lap-card-copy">{{ $archiveStatement }}</p>
                        @if (!empty($activeFilterLabels))
                            <div class="lap-chip-list">
                                @foreach ($activeFilterLabels as $filterLabel)
                                    <span class="lap-pill">{{ $filterLabel }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="lap-side-card">
                        <span class="lap-section-kicker">Navigasi</span>
                        <h3 class="lap-card-title-sm">Lanjut ke halaman lain</h3>
                        <div class="lap-list-stack lap-spacer-top">
                            <a href="{{ route('public.schedule') }}" class="lap-card-link">Buka jadwal pertandingan <i class="fa-solid fa-arrow-right"></i></a>
                            <a href="{{ route('public.standings') }}" class="lap-card-link">Lihat klasemen dan bracket <i class="fa-solid fa-arrow-right"></i></a>
                            <a href="{{ route('public.clubs') }}" class="lap-card-link">Jelajahi profil klub <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection

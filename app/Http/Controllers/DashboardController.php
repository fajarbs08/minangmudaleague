<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Club as ClubModel;
use App\Models\LineupList;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonClub;
use App\Models\SeasonOfficial;
use App\Models\SeasonPlayer;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\SeasonContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct(private SeasonContext $seasonContext) {}

    public function publicHome()
    {
        return view('public.home', $this->publicPageData([
            'title' => 'Liga Anak Piaman Laweh',
            'seoTitle' => 'Liga Anak Pariaman | Liga Anak Piaman Laweh',
            'activePublicPage' => 'home',
            'seoDescription' => 'Portal resmi Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, untuk jadwal pertandingan, hasil, klasemen, klub peserta, pemain, sponsor, dan kontak panitia.',
        ]));
    }

    public function robots()
    {
        $content = implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Disallow: /dashboard',
            'Disallow: /home',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Sitemap: '.route('public.sitemap'),
        ]).PHP_EOL;

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap()
    {
        $urls = collect([
            $this->sitemapEntry(route('public.home'), priority: '1.0', changefreq: 'daily'),
            $this->sitemapEntry(route('public.schedule'), priority: '0.9', changefreq: 'hourly'),
            $this->sitemapEntry(route('public.results'), priority: '0.9', changefreq: 'hourly'),
            $this->sitemapEntry(route('public.brackets'), priority: '0.8', changefreq: 'daily'),
            $this->sitemapEntry(route('public.standings'), priority: '0.8', changefreq: 'hourly'),
            $this->sitemapEntry(route('public.clubs'), priority: '0.8', changefreq: 'daily'),
        ])
            ->merge(
                $this->activePublicClubsQuery()
                    ->select(['id', 'name', 'short_name', 'updated_at'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Club $club) => $this->sitemapEntry(
                        route('public.clubs.show', ['clubSlug' => $club->public_slug]),
                        $club->updated_at?->toAtomString(),
                        'weekly',
                        '0.7'
                    ))
            )
            ->merge(
                $this->activePublicPlayersQuery()
                    ->select(['id', 'name', 'updated_at'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Player $player) => $this->sitemapEntry(
                        route('public.players.show', ['playerSlug' => $player->public_slug]),
                        $player->updated_at?->toAtomString(),
                        'weekly',
                        '0.6'
                    ))
            )
            ->merge(
                $this->activePublicOfficialsQuery()
                    ->select(['id', 'name', 'updated_at'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Official $official) => $this->sitemapEntry(
                        route('public.officials.show', ['officialSlug' => $official->public_slug]),
                        $official->updated_at?->toAtomString(),
                        'weekly',
                        '0.6'
                    ))
            )
            ->merge(
                MatchSchedule::query()->forActiveSeason()
                    ->with([
                        'clubA:id,name,short_name',
                        'clubB:id,name,short_name',
                    ])
                    ->select([
                        'id',
                        'club_a_id',
                        'club_b_id',
                        'match_date',
                        'is_finished',
                        'updated_at',
                    ])
                    ->where(function (Builder $query) {
                        $query->where('is_finished', true)
                            ->orWhere(function (Builder $inner) {
                                $inner->whereHas('clubA', fn (Builder $clubQuery) => $clubQuery->visibleInActiveContext())
                                    ->whereHas('clubB', fn (Builder $clubQuery) => $clubQuery->visibleInActiveContext());
                            });
                    })
                    ->whereNotNull('match_date')
                    ->orderByDesc('match_date')
                    ->get()
                    ->map(function (MatchSchedule $match) {
                        $routeName = $match->is_finished ? 'public.results.show' : 'public.schedule.show';

                        return $this->sitemapEntry(
                            route($routeName, ['matchSlug' => $match->public_slug]),
                            $match->updated_at?->toAtomString(),
                            $match->is_finished ? 'daily' : 'hourly',
                            '0.7'
                        );
                    })
            )
            ->values();

        $escape = fn (string $value) => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $body = $urls->map(function (array $url) use ($escape) {
            $xml = [
                '    <url>',
                '        <loc>'.$escape($url['loc']).'</loc>',
            ];

            if (! empty($url['lastmod'])) {
                $xml[] = '        <lastmod>'.$escape($url['lastmod']).'</lastmod>';
            }

            if (! empty($url['changefreq'])) {
                $xml[] = '        <changefreq>'.$escape($url['changefreq']).'</changefreq>';
            }

            if (! empty($url['priority'])) {
                $xml[] = '        <priority>'.$escape($url['priority']).'</priority>';
            }

            $xml[] = '    </url>';

            return implode(PHP_EOL, $xml);
        })->implode(PHP_EOL);

        return response('<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.$body.PHP_EOL.'</urlset>', 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function publicSchedule(Request $request)
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);

        return view('public.schedule', $this->buildPublicSchedulePageData($request, [
            'title' => 'Jadwal Pertandingan',
            'seoTitle' => 'Jadwal Liga Anak Pariaman | Liga Anak Piaman Laweh',
            'activePublicPage' => 'schedule',
            'bannerTitle' => 'Jadwal Pertandingan',
            'bannerCurrent' => 'Jadwal Pertandingan',
            'seoDescription' => 'Pantau jadwal pertandingan Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, termasuk laga terdekat, waktu kickoff, kelompok usia, dan venue pertandingan.',
            'scheduleFilterActionUrl' => route('public.schedule'),
            'scheduleCtaLabel' => 'LIHAT HASIL PERTANDINGAN',
            'scheduleCtaUrl' => route('public.results', $publicSeasonQuery),
        ]));
    }

    public function publicScheduleShow(Request $request, string $matchSlug)
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $match = $this->resolvePublicSchedule($matchSlug, $season);
        $match->loadMissing([
            'ageGroup',
            'clubA',
            'clubB',
            'clubASeason',
            'clubBSeason',
            'lineupLists.club',
            'lineupLists.players.ageRegistrations',
        ]);

        if ($matchSlug !== $match->public_slug) {
            return redirect()->route('public.schedule.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery, 301);
        }

        $homeClubLabel = $match->club_a_display_name ?: 'Klub A';
        $awayClubLabel = $match->club_b_display_name ?: 'Klub B';
        $matchBreadcrumbLabel = $homeClubLabel.' vs '.$awayClubLabel;
        $seoImage = $this->normalizeAbsoluteUrl($match->club_a_logo_file_url ?: $match->club_b_logo_file_url ?: $this->defaultSeoImageUrl());
        $statusLabel = $match->is_finished ? 'Pertandingan selesai' : 'Pertandingan terjadwal';
        $matchSummary = $statusLabel.'. '.$homeClubLabel.' vs '.$awayClubLabel.' pada '.optional($match->match_date)->translatedFormat('d F Y').' di '.($match->venue ?: 'venue belum tersedia').'.';

        return view('public.schedule-show', $this->publicPageData([
            'title' => 'Detail Jadwal Pertandingan',
            'seoTitle' => $homeClubLabel.' vs '.$awayClubLabel.' | Jadwal Liga Anak Pariaman',
            'activePublicPage' => 'schedule',
            'bannerTitle' => 'Detail Jadwal Pertandingan',
            'bannerCurrent' => $matchBreadcrumbLabel,
            'pageHeadingAccentWord' => 'Jadwal',
            'breadcrumbItems' => [
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Jadwal Pertandingan', 'url' => route('public.schedule', $publicSeasonQuery)],
                ['label' => $matchBreadcrumbLabel],
            ],
            'seoDescription' => $matchSummary,
            'seoUrl' => route('public.schedule.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery),
            'seoType' => 'article',
            'seoImage' => $seoImage,
            'seoStructuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'SportsEvent',
                'name' => $homeClubLabel.' vs '.$awayClubLabel,
                'description' => $matchSummary,
                'url' => route('public.schedule.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery),
                'startDate' => optional($match->match_date)->toDateString(),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'location' => [
                    '@type' => 'Place',
                    'name' => $match->venue ?: 'Venue pertandingan',
                ],
                'competitor' => array_values(array_filter([
                    $match->clubA ? [
                        '@type' => 'SportsTeam',
                        'name' => $homeClubLabel,
                        'url' => route('public.clubs.show', ['clubSlug' => $match->club_a_public_slug] + $publicSeasonQuery),
                    ] : null,
                    $match->clubB ? [
                        '@type' => 'SportsTeam',
                        'name' => $awayClubLabel,
                        'url' => route('public.clubs.show', ['clubSlug' => $match->club_b_public_slug] + $publicSeasonQuery),
                    ] : null,
                ])),
            ]],
            'matchSchedule' => $match,
            'selectedPublicSeason' => $season,
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => ! $this->seasonContext->isActiveSeason($season),
        ]));
    }

    public function publicResults(Request $request)
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);

        return view('public.schedule', $this->buildPublicResultsPageData($request, [
            'title' => 'Hasil Pertandingan',
            'seoTitle' => 'Hasil Liga Anak Pariaman | Skor Liga Anak Piaman Laweh',
            'activePublicPage' => 'results',
            'bannerTitle' => 'Hasil Pertandingan',
            'bannerCurrent' => 'Hasil Pertandingan',
            'seoDescription' => 'Pantau hasil pertandingan Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, beserta skor akhir, lawan, venue, dan status laga.',
            'scheduleFilterActionUrl' => route('public.results'),
            'scheduleCtaLabel' => 'LIHAT JADWAL PERTANDINGAN',
            'scheduleCtaUrl' => route('public.schedule', $publicSeasonQuery),
        ]));
    }

    public function publicBracketsPage(Request $request)
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $knockoutBrackets = $this->publicKnockoutBrackets($season);

        return view('public.brackets', $this->publicPageData([
            'title' => 'Bagan Knockout',
            'seoTitle' => 'Bagan Knockout Liga Anak Pariaman | Piaman Laweh',
            'activePublicPage' => 'brackets',
            'bannerTitle' => 'Bagan Knockout',
            'bannerCurrent' => 'Bagan Knockout',
            'seoDescription' => 'Lihat bagan knockout resmi Liga Anak Piaman Laweh, kompetisi sepak bola anak di Pariaman, per kelompok usia lengkap dengan jalur pertandingan dan hasil tiap babak.',
            'seoUrl' => $this->normalizeAbsoluteUrl($request->fullUrl()),
            'publicBracketryBrackets' => $this->publicBracketryBrackets($season, $publicSeasonQuery, $knockoutBrackets),
            'selectedPublicSeason' => $season,
            'publicSeasonOptions' => $this->seasonContext->publicVisible(),
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => ! $this->seasonContext->isActiveSeason($season),
        ]));
    }

    public function publicInformationRedirect()
    {
        return redirect()->to(route('public.home').'#footer-kontak');
    }

    public function publicInformationFileRedirect(string $informationResource)
    {
        return redirect()->route('public.information');
    }

    public function publicInformationShowRedirect(string $resourceSlug)
    {
        return redirect()->route('public.information');
    }

    private function buildPublicSchedulePageData(Request $request, array $overrides = []): array
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);
        $selectedAgeGroupId = AgeGroup::competition()->whereKey($request->integer('age_group_id'))->value('id');
        $selectedYear = $request->integer('year') ?: null;
        $selectedDate = $this->normalizePublicDateFilter($request->input('date'));
        $selectedClubId = $request->integer('club_id') ?: null;

        $scheduledMatchesQuery = $this->publicUpcomingMatchesQuery($season)
            ->with(['ageGroup', 'clubA', 'clubB', 'clubASeason', 'clubBSeason'])
            ->orderBy('match_date')
            ->orderBy('kickoff_time');

        $allScheduledMatches = (clone $scheduledMatchesQuery)->get();
        $filteredScheduledMatches = (clone $scheduledMatchesQuery)
            ->when($selectedAgeGroupId, fn (Builder $query, int $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($selectedYear, fn (Builder $query, int $year) => $query->whereYear('match_date', $year))
            ->when($selectedDate, fn (Builder $query, string $date) => $query->whereDate('match_date', $date))
            ->when($selectedClubId, function (Builder $query, int $clubId) {
                $query->where(function (Builder $inner) use ($clubId) {
                    $inner->where('club_a_id', $clubId)
                        ->orWhere('club_b_id', $clubId);
                });
            })
            ->get();

        $scheduleFilterOptions = [
            'age_groups' => $allScheduledMatches
                ->pluck('ageGroup')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->map(fn ($ageGroup) => [
                    'value' => (string) $ageGroup->id,
                    'label' => $ageGroup->name,
                ]),
            'years' => $allScheduledMatches
                ->map(fn (MatchSchedule $match) => optional($match->match_date)->format('Y'))
                ->filter()
                ->unique()
                ->sortDesc()
                ->values()
                ->map(fn (string $year) => [
                    'value' => $year,
                    'label' => $year,
                ]),
            'dates' => $allScheduledMatches
                ->map(function (MatchSchedule $match) {
                    $date = $match->match_date;

                    if (! $date) {
                        return null;
                    }

                    return [
                        'value' => $date->toDateString(),
                        'label' => $date->translatedFormat('d F Y'),
                    ];
                })
                ->filter()
                ->unique('value')
                ->sortBy('value')
                ->values(),
            'clubs' => $allScheduledMatches
                ->flatMap(function (MatchSchedule $match) {
                    return collect([
                        ['value' => (string) $match->club_a_id, 'label' => $match->club_a_short_name ?: $match->club_a_display_name],
                        ['value' => (string) $match->club_b_id, 'label' => $match->club_b_short_name ?: $match->club_b_display_name],
                    ]);
                })
                ->filter(fn (array $club) => filled($club['value']) && filled($club['label']))
                ->unique('value')
                ->sortBy('label')
                ->values(),
        ];

        return $this->publicPageData(array_merge([
            'title' => 'Jadwal Pertandingan',
            'seoTitle' => 'Jadwal Liga Anak Pariaman | Liga Anak Piaman Laweh',
            'activePublicPage' => 'schedule',
            'bannerTitle' => 'Jadwal Pertandingan',
            'bannerCurrent' => 'Jadwal Pertandingan',
            'seoDescription' => 'Pantau jadwal pertandingan Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, termasuk laga terdekat, waktu kickoff, kelompok usia, dan venue pertandingan.',
            'upcomingMatches' => $filteredScheduledMatches,
            'scheduleFilterOptions' => $scheduleFilterOptions,
            'scheduleFilters' => [
                'age_group_id' => $selectedAgeGroupId,
                'year' => $selectedYear,
                'date' => $selectedDate,
                'club_id' => $selectedClubId,
            ],
            'selectedPublicSeason' => $season,
            'publicSeasonOptions' => $this->seasonContext->publicVisible(),
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => $isHistoricalPublicSeason,
        ], $overrides));
    }

    private function buildPublicResultsPageData(Request $request, array $overrides = []): array
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);
        $selectedAgeGroupId = AgeGroup::competition()->whereKey($request->integer('age_group_id'))->value('id');
        $selectedYear = $request->integer('year') ?: null;
        $selectedDate = $this->normalizePublicDateFilter($request->input('date'));
        $selectedClubId = $request->integer('club_id') ?: null;

        $resultsQuery = $this->publicResultsQuery($season);

        $filteredResults = (clone $resultsQuery)
            ->when($selectedAgeGroupId, fn (Builder $query, int $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($selectedYear, fn (Builder $query, int $year) => $query->whereYear('match_date', $year))
            ->when($selectedDate, fn (Builder $query, string $date) => $query->whereDate('match_date', $date))
            ->when($selectedClubId, function (Builder $query, int $clubId) {
                $query->where(function (Builder $inner) use ($clubId) {
                    $inner->where('club_a_id', $clubId)
                        ->orWhere('club_b_id', $clubId);
                });
            })
            ->get();

        $ageGroupIds = (clone $resultsQuery)
            ->select('age_group_id')
            ->distinct()
            ->pluck('age_group_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->values();

        $matchDateOptions = (clone $resultsQuery)
            ->whereNotNull('match_date')
            ->reorder()
            ->get(['match_date'])
            ->pluck('match_date')
            ->filter();

        $resultFilterOptions = [
            'age_groups' => AgeGroup::competition()
                ->whereIn('id', $ageGroupIds)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (AgeGroup $ageGroup) => [
                    'value' => (string) $ageGroup->id,
                    'label' => $ageGroup->name,
                ]),
            'years' => $matchDateOptions
                ->map(fn ($matchDate) => \Illuminate\Support\Carbon::parse($matchDate)->year)
                ->unique()
                ->sortDesc()
                ->values()
                ->map(fn ($year) => [
                    'value' => (string) $year,
                    'label' => (string) $year,
                ])
                ->values(),
            'dates' => $matchDateOptions
                ->map(fn ($matchDate) => \Illuminate\Support\Carbon::parse($matchDate)->toDateString())
                ->unique()
                ->sort()
                ->values()
                ->map(function (string $dateValue) {
                    $date = \Illuminate\Support\Carbon::parse($dateValue);

                    return [
                        'value' => $date->toDateString(),
                        'label' => $date->translatedFormat('d F Y'),
                    ];
                })
                ->values(),
        ];

        $clubIdQuery = MatchSchedule::query()->forSeason($season?->id)
            ->selectRaw('club_a_id as club_id')
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->union(
                MatchSchedule::query()->forSeason($season?->id)
                    ->selectRaw('club_b_id as club_id')
                    ->where('is_finished', true)
                    ->whereNotNull('score_club_a')
                    ->whereNotNull('score_club_b')
            );

        $clubIds = DB::query()
            ->fromSub($clubIdQuery, 'result_clubs')
            ->select('club_id')
            ->distinct()
            ->pluck('club_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->values();

        $seasonClubMap = SeasonClub::query()
            ->where('season_id', $season?->id)
            ->whereIn('club_id', $clubIds)
            ->get(['club_id', 'name', 'short_name'])
            ->keyBy('club_id');

        $clubMap = Club::query()
            ->whereIn('id', $clubIds)
            ->get(['id', 'name', 'short_name'])
            ->keyBy('id');

        $resultFilterOptions['clubs'] = $clubIds
            ->map(function (int $clubId) use ($seasonClubMap, $clubMap) {
                $seasonClub = $seasonClubMap->get($clubId);
                $club = $clubMap->get($clubId);
                $label = $seasonClub?->name ?: $seasonClub?->short_name ?: $club?->name ?: $club?->short_name;

                return filled($label)
                    ? ['value' => (string) $clubId, 'label' => $label]
                    : null;
            })
            ->filter()
            ->sortBy('label')
            ->values();

        return $this->publicPageData(array_merge([
            'title' => 'Hasil Pertandingan',
            'seoTitle' => 'Hasil Liga Anak Pariaman | Skor Liga Anak Piaman Laweh',
            'activePublicPage' => 'results',
            'bannerTitle' => 'Hasil Pertandingan',
            'bannerCurrent' => 'Hasil Pertandingan',
            'seoDescription' => 'Pantau hasil pertandingan Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, beserta skor akhir, lawan, venue, dan status laga.',
            'recentResults' => $filteredResults,
            'resultFilterOptions' => $resultFilterOptions,
            'resultFilters' => [
                'age_group_id' => $selectedAgeGroupId,
                'year' => $selectedYear,
                'date' => $selectedDate,
                'club_id' => $selectedClubId,
            ],
            'selectedPublicSeason' => $season,
            'publicSeasonOptions' => $this->seasonContext->publicVisible(),
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => $isHistoricalPublicSeason,
        ], $overrides));
    }

    public function publicResultShow(Request $request, string $matchSlug)
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $match = $this->resolvePublicResult($matchSlug, $season);
        $match->loadMissing([
            'ageGroup',
            'clubA',
            'clubB',
            'clubASeason',
            'clubBSeason',
            'goalEvents.club',
            'goalEvents.scorer',
            'goalEvents.assistPlayer',
            'lineupLists.club',
            'lineupLists.players.ageRegistrations',
        ]);

        if ($matchSlug !== $match->public_slug) {
            return redirect()->route('public.results.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery, 301);
        }

        $clubALabel = $match->club_a_display_name ?: 'Klub A';
        $clubBLabel = $match->club_b_display_name ?: 'Klub B';
        $matchShareUrl = route('public.results.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery);
        $cleanSheetLabel = match (true) {
            ! $match->has_clean_sheet => 'Tidak ada clean sheet',
            (int) $match->score_club_a === 0 && (int) $match->score_club_b === 0 => 'Kedua tim clean sheet',
            (int) $match->score_club_a === 0 => ($match->club_b_short_name ?: $match->club_b_display_name ?: 'Klub B').' clean sheet',
            default => ($match->club_a_short_name ?: $match->club_a_display_name ?: 'Klub A').' clean sheet',
        };
        $winnerLabel = $match->score_club_a === $match->score_club_b
            ? 'Laga berakhir imbang'
            : ($match->score_club_a > $match->score_club_b
                ? $clubALabel.' menang'
                : $clubBLabel.' menang');
        $matchBreadcrumbLabel = $clubALabel.' vs '.$clubBLabel;
        $seoImage = $this->normalizeAbsoluteUrl($match->club_a_logo_file_url ?: $match->club_b_logo_file_url ?: $this->defaultSeoImageUrl());
        $matchSummary = $winnerLabel.'. Skor akhir '.$match->score_label.' pada '.optional($match->match_date)->translatedFormat('d F Y').' di '.($match->venue ?: 'lokasi pertandingan').'.';

        return view('public.result-show', $this->publicPageData([
            'title' => $clubALabel.' vs '.$clubBLabel,
            'seoTitle' => $clubALabel.' '.$match->score_label.' '.$clubBLabel.' | Hasil Liga Anak Pariaman',
            'activePublicPage' => 'results',
            'bannerTitle' => 'Detail Hasil Pertandingan',
            'bannerCurrent' => $matchBreadcrumbLabel,
            'pageHeadingAccentWord' => 'Hasil',
            'breadcrumbItems' => [
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Hasil Pertandingan', 'url' => route('public.results', $publicSeasonQuery)],
                ['label' => $matchBreadcrumbLabel],
            ],
            'seoDescription' => $matchSummary.' Lihat rincian gol, statistik tim, dan detail laga lengkap.',
            'seoUrl' => $matchShareUrl,
            'seoType' => 'article',
            'seoImage' => $seoImage,
            'seoStructuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'SportsEvent',
                'name' => $clubALabel.' vs '.$clubBLabel,
                'description' => $matchSummary,
                'url' => $matchShareUrl,
                'startDate' => optional($match->match_date)->toDateString(),
                'eventStatus' => 'https://schema.org/EventCompleted',
                'location' => [
                    '@type' => 'Place',
                    'name' => $match->venue ?: 'Venue pertandingan',
                ],
                'competitor' => array_values(array_filter([
                    $match->clubA ? [
                        '@type' => 'SportsTeam',
                        'name' => $clubALabel,
                        'url' => route('public.clubs.show', ['clubSlug' => $match->club_a_public_slug] + $publicSeasonQuery),
                    ] : null,
                    $match->clubB ? [
                        '@type' => 'SportsTeam',
                        'name' => $clubBLabel,
                        'url' => route('public.clubs.show', ['clubSlug' => $match->club_b_public_slug] + $publicSeasonQuery),
                    ] : null,
                ])),
            ]],
            'matchResult' => $match,
            'matchResultShareUrl' => $matchShareUrl,
            'matchResultStats' => [
                'total_goals' => $match->total_goals,
                'scorer_count' => $match->goalEvents->pluck('player_id')->filter()->unique()->count(),
                'clean_sheet' => $cleanSheetLabel,
            ],
            'matchResultClubStats' => [
                'club_a' => $this->buildPublicResultClubStats($match, (int) $match->club_a_id),
                'club_b' => $this->buildPublicResultClubStats($match, (int) $match->club_b_id),
            ],
            'matchResultTimeline' => $this->buildPublicGoalTimeline($match),
            'relatedResults' => $this->publicResultsQuery($season)
                ->whereKeyNot($match->id)
                ->where('age_group_id', $match->age_group_id)
                ->limit(4)
                ->get(),
            'selectedPublicSeason' => $season,
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => ! $this->seasonContext->isActiveSeason($season),
        ]));
    }

    public function publicStandingsPage(Request $request)
    {
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $baseStandingsQuery = $this->publicStandingsMatchesQuery($season);
        $defaultAgeGroupId = (clone $baseStandingsQuery)
            ->select('age_group_id')
            ->distinct()
            ->reorder('age_group_id')
            ->value('age_group_id');
        $selectedAgeGroupId = $request->has('age_group_id')
            ? ($request->integer('age_group_id') ?: null)
            : $defaultAgeGroupId;
        $selectedYear = $request->integer('year') ?: null;
        $selectedDate = $this->normalizePublicDateFilter($request->input('date'));
        $selectedClubId = $request->integer('club_id') ?: null;

        $filteredMatches = (clone $baseStandingsQuery)
            ->when($selectedAgeGroupId, fn (Builder $query, int $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($selectedYear, fn (Builder $query, int $year) => $query->whereYear('match_date', $year))
            ->when($selectedDate, fn (Builder $query, string $date) => $query->whereDate('match_date', $date))
            ->when($selectedClubId, function (Builder $query, int $clubId) {
                $query->where(function (Builder $inner) use ($clubId) {
                    $inner->where('club_a_id', $clubId)
                        ->orWhere('club_b_id', $clubId);
                });
            })
            ->get();

        $standings = $this->buildPublicStandings($filteredMatches);
        $topScorers = $this->buildPublicGoalLeaderboard(
            season: $season,
            type: 'scorer',
            ageGroupId: $selectedAgeGroupId,
            year: $selectedYear,
            date: $selectedDate,
            clubId: $selectedClubId,
        );
        $topAssists = $this->buildPublicGoalLeaderboard(
            season: $season,
            type: 'assist',
            ageGroupId: $selectedAgeGroupId,
            year: $selectedYear,
            date: $selectedDate,
            clubId: $selectedClubId,
        );

        $ageGroupIds = (clone $baseStandingsQuery)
            ->select('age_group_id')
            ->distinct()
            ->reorder('age_group_id')
            ->pluck('age_group_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->values();

        $ageGroupOptions = AgeGroup::competition()
            ->whereIn('id', $ageGroupIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (AgeGroup $ageGroup) => [
                'value' => (string) $ageGroup->id,
                'label' => $ageGroup->name,
            ]);

        $standingMatchDateOptions = (clone $baseStandingsQuery)
            ->whereNotNull('match_date')
            ->reorder()
            ->get(['match_date'])
            ->pluck('match_date')
            ->filter();

        $yearOptions = $standingMatchDateOptions
            ->map(fn ($matchDate) => \Illuminate\Support\Carbon::parse($matchDate)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->filter()
            ->map(fn ($year) => [
                'value' => (string) $year,
                'label' => (string) $year,
            ])
            ->values();

        $dateOptions = $standingMatchDateOptions
            ->map(fn ($matchDate) => \Illuminate\Support\Carbon::parse($matchDate)->toDateString())
            ->unique()
            ->sort()
            ->values()
            ->filter()
            ->map(function (string $dateValue) {
                $date = \Illuminate\Support\Carbon::parse($dateValue);

                return [
                    'value' => $date->toDateString(),
                    'label' => $date->translatedFormat('d F Y'),
                ];
            })
            ->values();

        $clubIdQuery = MatchSchedule::query()->forSeason($season?->id)
            ->selectRaw('club_a_id as club_id')
            ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->union(
                MatchSchedule::query()->forSeason($season?->id)
                    ->selectRaw('club_b_id as club_id')
                    ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
                    ->where('is_finished', true)
                    ->whereNotNull('score_club_a')
                    ->whereNotNull('score_club_b')
            );

        $clubIds = DB::query()
            ->fromSub($clubIdQuery, 'standings_clubs')
            ->select('club_id')
            ->distinct()
            ->pluck('club_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->values();

        $seasonClubMap = SeasonClub::query()
            ->where('season_id', $season?->id)
            ->whereIn('club_id', $clubIds)
            ->get(['club_id', 'name', 'short_name'])
            ->keyBy('club_id');

        $clubMap = Club::query()
            ->whereIn('id', $clubIds)
            ->get(['id', 'name', 'short_name'])
            ->keyBy('id');

        $clubOptions = $clubIds
            ->map(function (int $clubId) use ($seasonClubMap, $clubMap) {
                $seasonClub = $seasonClubMap->get($clubId);
                $club = $clubMap->get($clubId);
                $label = $seasonClub?->short_name ?: $seasonClub?->name ?: $club?->short_name ?: $club?->name;

                return filled($label)
                    ? ['value' => (string) $clubId, 'label' => $label]
                    : null;
            })
            ->filter()
            ->sortBy('label')
            ->values();

        $viewData = $this->publicPageData([
            'title' => 'Klasemen Liga',
            'seoTitle' => 'Klasemen Liga Anak Pariaman | Posisi Klub Piaman Laweh',
            'activePublicPage' => 'standings',
            'bannerTitle' => 'Klasemen Liga',
            'bannerCurrent' => 'Klasemen Liga',
            'seoDescription' => 'Klasemen sementara Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, berdasarkan hasil pertandingan resmi di setiap kelompok usia.',
            'publicStandings' => $standings,
            'topScorers' => $topScorers,
            'topAssists' => $topAssists,
            'standingsFilterOptions' => [
                'age_groups' => $ageGroupOptions,
                'years' => $yearOptions,
                'dates' => $dateOptions,
                'clubs' => $clubOptions,
            ],
            'standingsFilters' => [
                'age_group_id' => $selectedAgeGroupId,
                'year' => $selectedYear,
                'date' => $selectedDate,
                'club_id' => $selectedClubId,
            ],
            'selectedPublicSeason' => $season,
            'publicSeasonOptions' => $this->seasonContext->publicVisible(),
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => ! $this->seasonContext->isActiveSeason($season),
        ]);

        if ($request->boolean('partial') && $request->ajax()) {
            return view('public.partials.standings-table', $viewData);
        }

        return view('public.standings', $viewData);
    }

    public function publicClubs(Request $request)
    {
        $season = $this->selectedPublicSeason($request);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $featuredClubs = $isHistoricalPublicSeason
            ? SeasonClub::query()
                ->where('season_id', $season?->id)
                ->where('verification_status', Club::STATUS_APPROVED)
                ->orderBy('name')
                ->get()
            : $this->activePublicClubsQuery()
                ->latest('updated_at')
                ->limit(12)
                ->get();

        return view('public.clubs', $this->publicPageData([
            'title' => 'Klub Peserta',
            'seoTitle' => 'Klub Sepak Bola Anak Pariaman | Liga Anak Piaman Laweh',
            'activePublicPage' => 'clubs',
            'bannerTitle' => 'Daftar Klub',
            'bannerCurrent' => 'Daftar Klub',
            'seoDescription' => 'Daftar klub peserta '.($season?->name ?: 'Liga Anak Piaman Laweh').' di Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, lengkap dengan profil singkat, pemain, dan ofisial terdaftar.',
            'seoUrl' => $this->normalizeAbsoluteUrl($request->fullUrl()),
            'featuredClubs' => $featuredClubs,
            'selectedPublicSeason' => $season,
            'publicSeasonOptions' => $this->seasonContext->publicVisible(),
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => $isHistoricalPublicSeason,
        ]));
    }

    public function publicClubShow(Request $request, string $clubSlug)
    {
        preg_match('/(\d+)$/', $clubSlug, $matches);
        $clubId = isset($matches[1]) ? (int) $matches[1] : 0;

        $season = $this->selectedPublicSeason($request);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);
        $publicSeasonQuery = $this->publicSeasonQuery($season);

        if ($isHistoricalPublicSeason) {
            $club = SeasonClub::query()
                ->where('season_id', $season?->id)
                ->where('club_id', $clubId)
                ->where('verification_status', Club::STATUS_APPROVED)
                ->firstOrFail();

            if ($clubSlug !== $club->public_slug) {
                return redirect()->route('public.clubs.show', ['clubSlug' => $club->public_slug] + $publicSeasonQuery, 301);
            }

            $clubPlayers = SeasonPlayer::query()
                ->with('primaryAgeGroup')
                ->where('season_id', $season?->id)
                ->where('club_id', $club->club_id)
                ->where('verification_status', Player::STATUS_APPROVED)
                ->orderByDesc('is_captain')
                ->orderBy('name')
                ->get();

            $clubOfficials = SeasonOfficial::query()
                ->with('ageGroup')
                ->where('season_id', $season?->id)
                ->where('club_id', $club->club_id)
                ->where('verification_status', Official::STATUS_APPROVED)
                ->where('is_active', true)
                ->orderBy('role')
                ->orderBy('name')
                ->get();

            $clubMatches = MatchSchedule::query()
                ->forSeason($season?->id)
                ->with(['ageGroup', 'clubA', 'clubB', 'clubASeason', 'clubBSeason'])
                ->where(function ($query) use ($club) {
                    $query->where('club_a_id', $club->club_id)
                        ->orWhere('club_b_id', $club->club_id);
                })
                ->orderByDesc('match_date')
                ->orderByDesc('kickoff_time')
                ->limit(6)
                ->get();
        } else {
            $club = $this->activePublicClubsQuery()->findOrFail($clubId);

            $club->load([
                'players' => fn ($query) => $query
                    ->with('primaryAgeGroup')
                    ->visibleInActiveContext()
                    ->orderByDesc('is_captain')
                    ->orderBy('name'),
                'officials' => fn ($query) => $query
                    ->with('ageGroup')
                    ->visibleInActiveContext()
                    ->where('is_active', true)
                    ->orderBy('role')
                    ->orderBy('name'),
            ]);

            $clubPlayers = $club->players;
            $clubOfficials = $club->officials;
            $clubMatches = MatchSchedule::query()->forSeason($season?->id)
                ->with(['ageGroup', 'clubA', 'clubB'])
                ->whereHas('clubA', fn (Builder $query) => $query->visibleInActiveContext())
                ->whereHas('clubB', fn (Builder $query) => $query->visibleInActiveContext())
                ->where(function ($query) use ($club) {
                    $query->where('club_a_id', $club->id)
                        ->orWhere('club_b_id', $club->id);
                })
                ->orderByDesc('match_date')
                ->orderByDesc('kickoff_time')
                ->limit(6)
                ->get();
        }

        return view('public.club-show', $this->publicPageData([
            'title' => $club->name.' - Klub Peserta',
            'activePublicPage' => 'clubs',
            'bannerTitle' => 'Klub '.$club->name,
            'bannerCurrent' => $club->name,
            'pageHeadingAccentWord' => 'Klub',
            'breadcrumbItems' => [
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Daftar Klub', 'url' => route('public.clubs', $publicSeasonQuery)],
                ['label' => $club->name],
            ],
            'club' => $club,
            'clubPlayers' => $clubPlayers,
            'clubOfficials' => $clubOfficials,
            'clubRecentMatches' => $clubMatches,
            'seoTitle' => $club->name.' | Klub Liga Anak Pariaman',
            'seoDescription' => 'Profil klub '.$club->name.' pada '.($season?->name ?: 'Liga Anak Piaman Laweh').', liga sepak bola anak di Pariaman, termasuk pemain, ofisial, dan riwayat pertandingan terbaru.',
            'seoImage' => $this->normalizeAbsoluteUrl($club->logo_file_url ?: $this->defaultSeoImageUrl()),
            'seoUrl' => $this->normalizeAbsoluteUrl($request->fullUrl()),
            'seoSchemaType' => 'ProfilePage',
            'seoStructuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'SportsTeam',
                'name' => $club->name,
                'alternateName' => $club->short_name ?: null,
                'url' => route('public.clubs.show', ['clubSlug' => $club->public_slug] + $publicSeasonQuery),
                'logo' => $this->normalizeAbsoluteUrl($club->logo_file_url ?: $this->defaultSeoImageUrl()),
                'sport' => 'Soccer',
            ]],
            'selectedPublicSeason' => $season,
            'publicSeasonOptions' => $this->seasonContext->publicVisible(),
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => $isHistoricalPublicSeason,
        ]));
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $currentSeasonId = $this->seasonContext->currentId();
        $showAdminWorkflow = $user->isAdmin() && ! $this->seasonContext->isViewingHistory();
        $hasSeasonSnapshots = $currentSeasonId && $this->hasSeasonSnapshotTables();
        $clubIdValues = $user->isAdmin()
            ? null
            : Club::query()->where('user_id', $user->id)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $dashboardPayload = Cache::remember(
            $this->dashboardSummaryCacheKey($user->id, $currentSeasonId, $showAdminWorkflow, $hasSeasonSnapshots),
            now()->addSeconds(20),
            function () use ($user, $currentSeasonId, $showAdminWorkflow, $hasSeasonSnapshots, $clubIdValues) {
                $clubScope = function ($query, string $column = 'club_id') use ($user, $clubIdValues) {
                    if ($user->isAdmin()) {
                        return $query;
                    }

                    return empty($clubIdValues)
                        ? $query->whereRaw('1 = 0')
                        : $query->whereIn($column, $clubIdValues);
                };

                $stats = $hasSeasonSnapshots
                    ? [
                        'clubs' => $clubScope(SeasonClub::query()->where('season_id', $currentSeasonId))->count(),
                        'officials' => $clubScope(SeasonOfficial::query()->where('season_id', $currentSeasonId))->count(),
                        'players' => $clubScope(SeasonPlayer::query()->where('season_id', $currentSeasonId))->count(),
                        'lineups' => $clubScope(LineupList::query()->forActiveSeason())->count(),
                        'pending_clubs' => $clubScope(SeasonClub::query()->where('season_id', $currentSeasonId))->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                        'pending_officials' => $clubScope(SeasonOfficial::query()->where('season_id', $currentSeasonId))->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                        'pending_players' => $clubScope(SeasonPlayer::query()->where('season_id', $currentSeasonId))->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                        'pending_lineups' => $clubScope(LineupList::query()->forActiveSeason())->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                    ]
                    : [
                        'clubs' => $clubScope(Club::query(), 'id')->count(),
                        'officials' => $clubScope(Official::query())->count(),
                        'players' => $clubScope(Player::query())->count(),
                        'lineups' => $clubScope(LineupList::query())->count(),
                        'pending_clubs' => $clubScope(Club::query(), 'id')->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                        'pending_officials' => $clubScope(Official::query())->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                        'pending_players' => $clubScope(Player::query())->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                        'pending_lineups' => $clubScope(LineupList::query())->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                    ];

                $recentPlayers = $hasSeasonSnapshots
                    ? $clubScope(SeasonPlayer::query()->with(['seasonClub', 'primaryAgeGroup'])->where('season_id', $currentSeasonId))
                        ->latest('updated_at')
                        ->take(5)
                        ->get()
                    : $clubScope(Player::query()->with(['club', 'primaryAgeGroup']))
                        ->latest()
                        ->take(5)
                        ->get();

                $recentLineups = $clubScope(
                    LineupList::query()
                        ->when($hasSeasonSnapshots, fn ($query) => $query->forActiveSeason())
                        ->with(['club', 'ageGroup'])
                )
                    ->latest()
                    ->take(5)
                    ->get();

                $clubSummary = null;
                if (! $user->isAdmin()) {
                    $clubSummary = $hasSeasonSnapshots
                        ? $clubScope(SeasonClub::query()->where('season_id', $currentSeasonId))->latest('id')->first()
                        : $clubScope(Club::query(), 'id')->latest()->first();
                }

                return [
                    'stats' => $stats,
                    'recentPlayers' => $recentPlayers,
                    'recentLineups' => $recentLineups,
                    'clubSummary' => $clubSummary,
                ];
            }
        );

        $adminPayload = $showAdminWorkflow
            ? Cache::remember(
                $this->dashboardAdminCacheKey($currentSeasonId),
                now()->addSeconds(15),
                fn () => [
                    'adminReviewStats' => $this->adminReviewStats(),
                    'adminQueues' => $this->adminQueues(),
                    'recentSubmissions' => $this->recentSubmissions(),
                    'oldestPendingReviews' => $this->oldestPendingReviews(),
                    'adminResources' => [
                        'club_accounts' => User::query()->where('role', 'club')->count(),
                        'unused_club_accounts' => User::query()->where('role', 'club')->doesntHave('club')->count(),
                    ],
                ]
            )
            : [
                'adminReviewStats' => [],
                'adminQueues' => [],
                'recentSubmissions' => collect(),
                'oldestPendingReviews' => collect(),
                'adminResources' => [],
            ];

        return view('competition.dashboard', [
            'title' => 'Dashboard Registrasi',
            'stats' => $dashboardPayload['stats'],
            'recentPlayers' => $this->sortDashboardItems(
                $dashboardPayload['recentPlayers'],
                $request,
                'recent_players_sort',
                'recent_players_direction',
                'updated_at',
                'desc',
                [
                    'name' => fn ($player) => mb_strtolower((string) $player->name),
                    'club' => fn ($player) => mb_strtolower((string) ($player->seasonClub?->name ?? $player->club?->name ?? '')),
                    'age_group' => fn ($player) => mb_strtolower((string) ($player->primaryAgeGroup?->name ?? '')),
                    'updated_at' => fn ($player) => $player->updated_at?->getTimestamp() ?? 0,
                ]
            ),
            'recentLineups' => $this->sortDashboardItems(
                $dashboardPayload['recentLineups'],
                $request,
                'recent_lineups_sort',
                'recent_lineups_direction',
                'match_date',
                'desc',
                [
                    'title' => fn ($lineup) => mb_strtolower((string) $lineup->title),
                    'club' => fn ($lineup) => mb_strtolower((string) ($lineup->club?->name ?? '')),
                    'age_group' => fn ($lineup) => mb_strtolower((string) ($lineup->ageGroup?->name ?? '')),
                    'match_date' => fn ($lineup) => optional($lineup->match_date)->getTimestamp() ?? 0,
                ]
            ),
            'clubSummary' => $dashboardPayload['clubSummary'],
            'showAdminWorkflow' => $showAdminWorkflow,
            'adminReviewStats' => $adminPayload['adminReviewStats'],
            'adminQueues' => $adminPayload['adminQueues'],
            'recentSubmissions' => $this->sortDashboardItems(
                $adminPayload['recentSubmissions'],
                $request,
                'recent_submissions_sort',
                'recent_submissions_direction',
                'submitted_at',
                'desc',
                [
                    'type' => fn (array $item) => mb_strtolower((string) ($item['type'] ?? '')),
                    'name' => fn (array $item) => mb_strtolower((string) ($item['name'] ?? '')),
                    'club' => fn (array $item) => mb_strtolower((string) ($item['club'] ?? '')),
                    'status' => fn (array $item) => mb_strtolower((string) ($item['status'] ?? '')),
                    'submitted_at' => fn (array $item) => optional($item['submitted_at'] ?? null)?->getTimestamp() ?? 0,
                    'reviewed_by' => fn (array $item) => mb_strtolower((string) ($item['reviewed_by'] ?? '')),
                ]
            ),
            'oldestPendingReviews' => $this->sortDashboardItems(
                $adminPayload['oldestPendingReviews'],
                $request,
                'pending_reviews_sort',
                'pending_reviews_direction',
                'submitted_at',
                'asc',
                [
                    'type' => fn (array $item) => mb_strtolower((string) ($item['type'] ?? '')),
                    'name' => fn (array $item) => mb_strtolower((string) ($item['name'] ?? '')),
                    'club' => fn (array $item) => mb_strtolower((string) ($item['club'] ?? '')),
                    'submitted_at' => fn (array $item) => optional($item['submitted_at'] ?? null)?->getTimestamp() ?? 0,
                    'waiting_label' => fn (array $item) => mb_strtolower((string) ($item['waiting_label'] ?? '')),
                ]
            ),
            'adminResources' => $adminPayload['adminResources'],
        ]);
    }

    private function sortDashboardItems(Collection $items, Request $request, string $sortParam, string $directionParam, string $defaultSort, string $defaultDirection, array $sortMap): Collection
    {
        $sort = $request->string($sortParam)->value() ?: $defaultSort;
        $direction = $request->input($directionParam) === 'asc' ? 'asc' : 'desc';

        if (! array_key_exists($sort, $sortMap)) {
            $sort = $defaultSort;
            $direction = $defaultDirection;
        }

        $sorted = $items->sortBy($sortMap[$sort], SORT_NATURAL | SORT_FLAG_CASE);

        return $direction === 'desc'
            ? $sorted->reverse()->values()
            : $sorted->values();
    }

    private function hasSeasonSnapshotTables(): bool
    {
        return Cache::rememberForever('dashboard:has-season-snapshot-tables', fn () =>
            Schema::hasTable('season_clubs')
            && Schema::hasTable('season_officials')
            && Schema::hasTable('season_players')
        );
    }

    private function dashboardSummaryCacheKey(int $userId, ?int $seasonId, bool $showAdminWorkflow, bool $hasSeasonSnapshots): string
    {
        return implode(':', [
            'dashboard',
            'summary',
            $userId,
            $seasonId ?: 'none',
            $showAdminWorkflow ? 'admin' : 'club',
            $hasSeasonSnapshots ? 'snapshots' : 'legacy',
        ]);
    }

    private function dashboardAdminCacheKey(?int $seasonId): string
    {
        return 'dashboard:admin:'.($seasonId ?: 'none');
    }

    private function publicStandingsMatchesQuery(?Season $season = null): Builder
    {
        $season ??= $this->seasonContext->active();

        return MatchSchedule::query()->forSeason($season?->id)
            ->select([
                'id',
                'age_group_id',
                'club_a_id',
                'club_b_id',
                'club_a_season_id',
                'club_b_season_id',
                'match_date',
                'kickoff_time',
                'score_club_a',
                'score_club_b',
                'is_finished',
            ])
            ->with([
                'ageGroup:id,name',
                'clubA:id,name,short_name,logo_url',
                'clubB:id,name,short_name,logo_url',
                'clubASeason:id,club_id,name,short_name,logo_url',
                'clubBSeason:id,club_id,name,short_name,logo_url',
            ])
            ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->orderBy('age_group_id')
            ->orderBy('match_date')
            ->orderBy('kickoff_time');
    }

    private function buildPublicStandings(Collection $matches): Collection
    {
        if ($matches->isEmpty()) {
            return collect();
        }

        $ageGroups = $matches
            ->pluck('ageGroup')
            ->filter()
            ->keyBy('id');

        $matchIds = $matches->pluck('id')->map(fn ($id) => (int) $id)->filter()->values();

        $clubAQuery = MatchSchedule::query()
            ->whereIn('id', $matchIds)
            ->selectRaw('age_group_id, club_a_id as club_id, 1 as played, score_club_a as goals_for, score_club_b as goals_against, CASE WHEN score_club_a > score_club_b THEN 1 ELSE 0 END as won, CASE WHEN score_club_a = score_club_b THEN 1 ELSE 0 END as drawn, CASE WHEN score_club_a < score_club_b THEN 1 ELSE 0 END as lost, CASE WHEN score_club_a > score_club_b THEN 3 WHEN score_club_a = score_club_b THEN 1 ELSE 0 END as points');

        $clubBQuery = MatchSchedule::query()
            ->whereIn('id', $matchIds)
            ->selectRaw('age_group_id, club_b_id as club_id, 1 as played, score_club_b as goals_for, score_club_a as goals_against, CASE WHEN score_club_b > score_club_a THEN 1 ELSE 0 END as won, CASE WHEN score_club_b = score_club_a THEN 1 ELSE 0 END as drawn, CASE WHEN score_club_b < score_club_a THEN 1 ELSE 0 END as lost, CASE WHEN score_club_b > score_club_a THEN 3 WHEN score_club_b = score_club_a THEN 1 ELSE 0 END as points');

        $standingsRows = DB::query()
            ->fromSub($clubAQuery->unionAll($clubBQuery), 'standings_base')
            ->selectRaw('standings_base.age_group_id, standings_base.club_id, SUM(standings_base.played) as played, SUM(standings_base.won) as won, SUM(standings_base.drawn) as drawn, SUM(standings_base.lost) as lost, SUM(standings_base.goals_for) as goals_for, SUM(standings_base.goals_against) as goals_against, SUM(standings_base.goals_for) - SUM(standings_base.goals_against) as goal_difference, SUM(standings_base.points) as points')
            ->groupBy('standings_base.age_group_id', 'standings_base.club_id')
            ->orderBy('standings_base.age_group_id')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get()
            ->groupBy('age_group_id');

        $metaByGroupClub = [];

        foreach ($matches as $match) {
            foreach ([
                [
                    'club' => $match->clubA,
                    'club_id' => (int) $match->club_a_id,
                    'goals_for' => (int) $match->score_club_a,
                    'goals_against' => (int) $match->score_club_b,
                    'display_name' => $match->club_a_display_name,
                    'display_short_name' => $match->club_a_short_name,
                    'display_public_slug' => $match->club_a_public_slug,
                    'display_logo_url' => $match->club_a_logo_file_url,
                ],
                [
                    'club' => $match->clubB,
                    'club_id' => (int) $match->club_b_id,
                    'goals_for' => (int) $match->score_club_b,
                    'goals_against' => (int) $match->score_club_a,
                    'display_name' => $match->club_b_display_name,
                    'display_short_name' => $match->club_b_short_name,
                    'display_public_slug' => $match->club_b_public_slug,
                    'display_logo_url' => $match->club_b_logo_file_url,
                ],
            ] as $entry) {
                if (! $entry['club']) {
                    continue;
                }

                $groupId = (int) $match->age_group_id;
                $clubId = $entry['club_id'];
                $metaByGroupClub[$groupId] ??= [];
                $row = $metaByGroupClub[$groupId][$clubId] ?? [
                    'club_name' => $entry['display_name'] ?: $entry['club']->name,
                    'club_short_name' => $entry['display_short_name'] ?: $entry['display_name'] ?: $entry['club']->short_name ?: $entry['club']->name,
                    'club_public_slug' => $entry['display_public_slug'] ?: $entry['club']->public_slug,
                    'club_logo_url' => $entry['display_logo_url'] ?: $this->publicClubLogoUrl($entry['club'], 'public-assets/img/inner/flag/b1.png'),
                    'points' => 0,
                    'previous_points' => 0,
                    'points_delta' => 0,
                    'recent_form' => [],
                ];

                $previousPoints = $row['points'];

                if ($entry['goals_for'] > $entry['goals_against']) {
                    $row['points'] += 3;
                    $row['recent_form'][] = 'W';
                    $row['points_delta'] = 3;
                } elseif ($entry['goals_for'] === $entry['goals_against']) {
                    $row['points'] += 1;
                    $row['recent_form'][] = 'D';
                    $row['points_delta'] = 1;
                } else {
                    $row['recent_form'][] = 'L';
                    $row['points_delta'] = 0;
                }

                $row['previous_points'] = $previousPoints;
                $row['recent_form'] = array_slice($row['recent_form'], -2);
                $metaByGroupClub[$groupId][$clubId] = $row;
            }
        }

        return $matches
            ->groupBy('age_group_id')
            ->map(function (Collection $groupMatches, int $groupId) use ($ageGroups, $standingsRows, $metaByGroupClub) {
                $rows = collect($standingsRows->get($groupId, collect()))
                    ->values()
                    ->map(function ($row, int $index) use ($groupId, $metaByGroupClub) {
                        $clubMeta = $metaByGroupClub[$groupId][(int) $row->club_id] ?? [];

                        return [
                            'club_id' => (int) $row->club_id,
                            'club_name' => $clubMeta['club_name'] ?? 'Klub',
                            'club_short_name' => $clubMeta['club_short_name'] ?? ($clubMeta['club_name'] ?? 'Klub'),
                            'club_public_slug' => $clubMeta['club_public_slug'] ?? null,
                            'club_logo_url' => $clubMeta['club_logo_url'] ?? null,
                            'played' => (int) $row->played,
                            'won' => (int) $row->won,
                            'drawn' => (int) $row->drawn,
                            'lost' => (int) $row->lost,
                            'goals_for' => (int) $row->goals_for,
                            'goals_against' => (int) $row->goals_against,
                            'goal_difference' => (int) $row->goal_difference,
                            'points' => (int) $row->points,
                            'previous_points' => (int) ($clubMeta['previous_points'] ?? 0),
                            'points_delta' => (int) ($clubMeta['points_delta'] ?? 0),
                            'recent_form' => $clubMeta['recent_form'] ?? [],
                            'position' => $index + 1,
                        ];
                    })
                    ->take(10)
                    ->values();

                return [
                    'age_group' => $ageGroups->get($groupId),
                    'last_match_date' => $groupMatches->last()?->match_date,
                    'rows' => $rows,
                ];
            })
            ->values();
    }

    private function publicStandings(?Season $season = null): Collection
    {
        return $this->buildPublicStandings($this->publicStandingsMatchesQuery($season)->get());
    }

    private function buildPublicGoalLeaderboard(
        ?Season $season = null,
        string $type = 'scorer',
        ?int $ageGroupId = null,
        ?int $year = null,
        ?string $date = null,
        ?int $clubId = null,
    ): Collection {
        $season ??= $this->seasonContext->active();

        $relationColumn = $type === 'assist' ? 'assist_player_id' : 'player_id';
        $ageGroups = AgeGroup::competition()->get()->keyBy('id');

        return MatchGoal::query()->forSeason($season?->id)
            ->select([
                'match_schedules.age_group_id',
                'players.id as player_id',
                'players.name as player_name',
                'clubs.name as club_name',
                'clubs.short_name as club_short_name',
            ])
            ->selectRaw('COUNT(match_goals.id) as total')
            ->join('match_schedules', 'match_schedules.id', '=', 'match_goals.match_id')
            ->join('players', 'players.id', '=', "match_goals.{$relationColumn}")
            ->leftJoin('clubs', 'clubs.id', '=', 'players.club_id')
            ->where('match_schedules.competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('match_schedules.is_finished', true)
            ->whereNotNull("match_goals.{$relationColumn}")
            ->when($ageGroupId, fn (Builder $query, int $value) => $query->where('match_schedules.age_group_id', $value))
            ->when($year, fn (Builder $query, int $value) => $query->whereYear('match_schedules.match_date', $value))
            ->when($date, fn (Builder $query, string $value) => $query->whereDate('match_schedules.match_date', $value))
            ->when($clubId, function (Builder $query, int $value) {
                $query->where(function (Builder $inner) use ($value) {
                    $inner->where('match_schedules.club_a_id', $value)
                        ->orWhere('match_schedules.club_b_id', $value);
                });
            })
            ->groupBy('match_schedules.age_group_id', 'players.id', 'players.name', 'clubs.name', 'clubs.short_name')
            ->orderBy('match_schedules.age_group_id')
            ->orderByRaw('COUNT(match_goals.id) DESC')
            ->orderBy('players.name')
            ->get()
            ->groupBy('age_group_id')
            ->map(function (Collection $groupRows, int $groupId) use ($ageGroups) {
                return [
                    'age_group' => $ageGroups->get($groupId),
                    'rows' => $groupRows
                        ->values()
                        ->take(5)
                        ->map(function ($row, int $index) {
                            return [
                                'position' => $index + 1,
                                'player_name' => $row->player_name,
                                'club_name' => $row->club_name ?: $row->club_short_name ?: '-',
                                'total' => (int) $row->total,
                            ];
                        }),
                ];
            })
            ->values();
    }

    private function publicKnockoutBrackets(?Season $season = null, array $publicSeasonQuery = []): Collection
    {
        $season ??= $this->seasonContext->active();

        return MatchSchedule::query()->forSeason($season?->id)
            ->with([
                'ageGroup:id,name',
                'clubA:id,name,short_name,logo_url,user_id',
                'clubA.user:id,is_active',
                'clubB:id,name,short_name,logo_url,user_id',
                'clubB.user:id,is_active',
                'clubASeason:id,club_id,name,short_name,logo_url',
                'clubBSeason:id,club_id,name,short_name,logo_url',
                'goalEvents:id,match_id,club_id,player_id,assist_player_id,display_order',
                'goalEvents.scorer:id,name',
                'goalEvents.assistPlayer:id,name',
            ])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->whereHas('ageGroup', fn (Builder $query) => $query->competition())
            ->orderBy('age_group_id')
            ->orderBy('round_order')
            ->orderBy('bracket_slot')
            ->orderBy('match_date')
            ->get()
            ->groupBy('age_group_id')
            ->filter(fn (Collection $matches) => $this->shouldShowPublicKnockoutAgeGroup($matches, $season))
            ->map(function (Collection $matches) {
                $rounds = $matches
                    ->groupBy(fn (MatchSchedule $match) => $match->round_order ?: 1)
                    ->map(function (Collection $roundMatches) {
                        return [
                            'round_order' => (int) ($roundMatches->first()?->round_order ?: 1),
                            'label' => $roundMatches->first()?->round_display_label ?: 'Babak Knockout',
                            'matches' => $roundMatches
                                ->sortBy(fn (MatchSchedule $match) => $match->bracket_slot ?: PHP_INT_MAX)
                                ->values(),
                        ];
                    })
                    ->sortKeys()
                    ->values();

                return [
                    'age_group' => $matches->first()?->ageGroup,
                    'rounds' => $this->trimBracketRoundsAfterFinal(
                        $this->normalizeBracketRoundLabels($rounds)
                    ),
                ];
            })
            ->values();
    }

    private function normalizeBracketRoundLabels(Collection $rounds): Collection
    {
        $seenLabels = [];

        return $rounds->values()->map(function (array $round, int $index) use (&$seenLabels) {
            $label = trim((string) ($round['label'] ?? ''));
            $roundOrder = (int) ($round['round_order'] ?? ($index + 1));

            if ($label === '') {
                $round['label'] = 'Babak '.$roundOrder;

                return $round;
            }

            $seenLabels[$label] = ($seenLabels[$label] ?? 0) + 1;

            if ($seenLabels[$label] > 1) {
                $round['label'] = 'Babak '.$roundOrder;
            }

            return $round;
        })->values();
    }

    private function trimBracketRoundsAfterFinal(Collection $rounds): Collection
    {
        $finalIndex = $rounds->search(fn (array $round) => $this->isTerminalFinalRoundLabel($round['label'] ?? null));

        if ($finalIndex === false) {
            return $rounds->values();
        }

        return $rounds->take($finalIndex + 1)->values();
    }

    private function isTerminalFinalRoundLabel(?string $label): bool
    {
        $normalized = Str::of((string) $label)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();

        return in_array($normalized, ['final', 'babak final', 'grand final', 'finale'], true);
    }

    private function publicBracketryBrackets(?Season $season = null, array $publicSeasonQuery = [], ?Collection $knockoutBrackets = null): Collection
    {
        $knockoutBrackets ??= $this->publicKnockoutBrackets($season, $publicSeasonQuery);

        return $knockoutBrackets->map(function (array $bracket) use ($publicSeasonQuery): array {
            $allRounds = collect($bracket['rounds'])->values();
            $displayRounds = $allRounds;

            $contestants = [];
            $matches = [];
            $finalMatch = $allRounds->last()['matches']->last() ?? null;

            foreach ($displayRounds as $roundIndex => $round) {
                foreach ($round['matches'] as $matchIndex => $match) {
                    $homeKey = 'club-'.$match->club_a_id;
                    $awayKey = 'club-'.$match->club_b_id;

                    $contestants[$homeKey] = [
                        'players' => [[
                            'title' => $match->club_a_display_name ?: 'Klub A',
                        ]],
                    ];

                    $contestants[$awayKey] = [
                        'players' => [[
                            'title' => $match->club_b_display_name ?: 'Klub B',
                        ]],
                    ];

                    $matches[] = [
                        'roundIndex' => $roundIndex,
                        'order' => $matchIndex,
                        'matchStatus' => filled($match->match_date)
                            ? trim((optional($match->match_date)->format('d M Y') ?: '') . ' · ' . ($match->kickoff_time?->format('H:i') ?: '--') . ' WIB', ' ·')
                            : ($match->is_finished ? 'FT' : 'SCHEDULED'),
                        'detail' => $this->publicBracketMatchDetail($match, $round['label'], $publicSeasonQuery),
                        'sides' => [
                            [
                                'contestantId' => $homeKey,
                                'scores' => filled($match->score_club_a) ? [[
                                    'mainScore' => (int) $match->score_club_a,
                                ]] : [],
                                'isWinner' => $match->score_club_b !== null && $match->score_club_a !== null
                                    ? (int) $match->score_club_a > (int) $match->score_club_b
                                    : false,
                            ],
                            [
                                'contestantId' => $awayKey,
                                'scores' => filled($match->score_club_b) ? [[
                                    'mainScore' => (int) $match->score_club_b,
                                ]] : [],
                                'isWinner' => $match->score_club_b !== null && $match->score_club_a !== null
                                    ? (int) $match->score_club_b > (int) $match->score_club_a
                                    : false,
                            ],
                        ],
                    ];
                }
            }

            $mainBracketMatchCount = count($displayRounds->first()['matches'] ?? []);
            $clubCount = $displayRounds
                ->flatMap(fn (array $round) => collect($round['matches']))
                ->flatMap(fn (MatchSchedule $match) => [$match->club_a_id, $match->club_b_id])
                ->filter()
                ->unique()
                ->count();

            $winner = null;
            if ($finalMatch && $finalMatch->score_club_a !== null && $finalMatch->score_club_b !== null && $finalMatch->score_club_a !== $finalMatch->score_club_b) {
                $winner = [
                    'name' => $finalMatch->score_club_a > $finalMatch->score_club_b ? ($finalMatch->club_a_display_name ?: 'Juara belum tersedia') : ($finalMatch->club_b_display_name ?: 'Juara belum tersedia'),
                    'short_name' => $finalMatch->score_club_a > $finalMatch->score_club_b ? ($finalMatch->club_a_short_name ?: $finalMatch->club_a_display_name ?: 'JUARA') : ($finalMatch->club_b_short_name ?: $finalMatch->club_b_display_name ?: 'JUARA'),
                    'logo_url' => $finalMatch->score_club_a > $finalMatch->score_club_b ? $finalMatch->club_a_logo_file_url : $finalMatch->club_b_logo_file_url,
                    'score_label' => $finalMatch->score_label,
                    'opponent_name' => $finalMatch->score_club_a > $finalMatch->score_club_b ? ($finalMatch->club_b_display_name ?: 'Lawan') : ($finalMatch->club_a_display_name ?: 'Lawan'),
                    'match_label' => $finalMatch->match_day,
                    'match_date' => optional($finalMatch->match_date)->translatedFormat('d F Y'),
                ];
            }

            return [
                'age_group' => $bracket['age_group'],
                'match_count' => count($matches),
                'winner' => $winner,
                'preliminary_rounds' => [],
                'layout' => [
                    'club_count' => $clubCount,
                    'desktop_height' => max(520, min(920, 260 + ($mainBracketMatchCount * 112))),
                    'mobile_height' => max(420, min(680, 250 + ($mainBracketMatchCount * 86))),
                ],
                'data' => [
                    'rounds' => $displayRounds->map(fn (array $round) => [
                        'name' => $round['label'],
                    ])->values()->all(),
                    'matches' => $matches,
                    'contestants' => $contestants,
                ],
            ];
        });
    }

    private function publicBracketMatchDetail(MatchSchedule $match, string $roundLabel, array $publicSeasonQuery = []): array
    {
        $goalReports = [];

        foreach ([(object) ['id' => $match->club_a_id, 'label' => $match->club_a_short_name ?: $match->club_a_display_name], (object) ['id' => $match->club_b_id, 'label' => $match->club_b_short_name ?: $match->club_b_display_name]] as $club) {
            $goalReport = $match->goalReportForClub($club?->id);

            if (! $club || empty($goalReport)) {
                continue;
            }

            $goalReports[] = [
                'club' => $club->label,
                'summary' => implode(', ', $goalReport),
            ];
        }

        $detailUrl = null;
        if (filled($match->public_slug)) {
            $detailUrl = $match->is_finished
                ? route('public.results.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery)
                : route('public.schedule.show', ['matchSlug' => $match->public_slug] + $publicSeasonQuery);
        }

        return [
            'round_label' => $roundLabel,
            'match_day' => $match->match_day ?: 'Pertandingan knockout',
            'state_label' => $match->is_finished ? 'Selesai' : 'Terjadwal',
            'status_label' => filled($match->match_date)
                ? trim((optional($match->match_date)->translatedFormat('d F Y') ?: '').' · '.($match->kickoff_time?->format('H:i') ?: '--').' WIB', ' ·')
                : ($match->is_finished ? 'FT' : 'Belum dijadwalkan'),
            'date_label' => optional($match->match_date)->translatedFormat('d F Y') ?: 'Tanggal belum tersedia',
            'kickoff_label' => $match->kickoff_time?->format('H:i').' WIB' ?: 'Jam belum tersedia',
            'venue' => $match->venue ?: 'Venue belum tersedia',
            'home_name' => $match->club_a_display_name ?: 'Menunggu',
            'away_name' => $match->club_b_display_name ?: 'Menunggu',
            'home_score' => $match->score_club_a,
            'away_score' => $match->score_club_b,
            'result_summary' => $match->is_finished
                ? ($match->result_summary ?: 'Pertandingan selesai')
                : 'Pertandingan belum selesai.',
            'goal_reports' => $goalReports,
            'detail_url' => $detailUrl,
            'detail_cta_label' => $match->is_finished ? 'Buka detail hasil' : 'Buka detail jadwal',
        ];
    }

    private function publicPageData(array $overrides = []): array
    {
        $defaults = [
            'title' => 'Liga Anak Piaman Laweh',
            'activePublicPage' => 'home',
            'bannerTitle' => null,
            'bannerCurrent' => null,
            'publicStats' => [
                'clubs' => 0,
                'officials' => 0,
                'players' => 0,
                'lineups' => 0,
            ],
            'featuredClubs' => collect(),
            'featuredPlayers' => collect(),
            'upcomingMatches' => collect(),
            'recentResults' => collect(),
            'headlineMatch' => null,
            'featuredResult' => null,
            'publicStandings' => collect(),
            'featuredSponsors' => collect(),
            'selectedPublicSeason' => null,
            'publicSeasonOptions' => collect(),
            'publicSeasonQuery' => [],
            'isHistoricalPublicSeason' => false,
        ];

        $data = array_merge($defaults, $overrides);
        $activePublicPage = $data['activePublicPage'] ?? 'home';

        if ($activePublicPage === 'home') {
            $activeSeasonId = $this->seasonContext->activeId() ?: 0;
            $homeCacheKey = 'public:home:payload:'.$activeSeasonId;

            $homePayload = Cache::remember($homeCacheKey, now()->addSeconds(60), function () {
                $upcomingMatches = $this->publicUpcomingMatchesQuery()
                    ->with([
                        'ageGroup:id,name',
                        'clubA:id,name,short_name,logo_url',
                        'clubB:id,name,short_name,logo_url',
                    ])
                    ->limit(12)
                    ->get();

                $recentResults = $this->publicResultsQuery()
                    ->limit(12)
                    ->get();

                $featuredClubs = $this->activePublicClubsQuery()
                    ->latest('updated_at')
                    ->limit(12)
                    ->get();

                $featuredPlayers = $this->activePublicPlayersQuery()
                    ->with([
                        'club:id,name,short_name,logo_url',
                        'primaryAgeGroup:id,name',
                    ])
                    ->latest('updated_at')
                    ->limit(12)
                    ->get();

                return [
                    'publicStats' => [
                        'clubs' => $this->activePublicClubsQuery()->count(),
                        'officials' => $this->activePublicOfficialsQuery()->count(),
                        'players' => $this->activePublicPlayersQuery()->count(),
                        'lineups' => LineupList::query()->forActiveSeason()->whereHas('club.user', fn (Builder $query) => $query->active())->count(),
                    ],
                    'featuredClubs' => $featuredClubs,
                    'featuredPlayers' => $featuredPlayers,
                    'upcomingMatches' => $upcomingMatches,
                    'recentResults' => $recentResults,
                    'headlineMatch' => $upcomingMatches->first(),
                    'featuredResult' => $recentResults->first(),
                    'publicStandings' => $this->publicStandings(),
                    'featuredSponsors' => $this->publicSponsorsData(),
                ];
            });

            $data = array_merge($homePayload, $data);
        }

        $defaultSeoTitle = ($data['title'] ?? 'Liga Anak Piaman Laweh') === 'Liga Anak Piaman Laweh'
            ? 'Liga Anak Pariaman | Portal Sepak Bola Anak Piaman Laweh'
            : ($data['title'] ?? 'Liga Anak Piaman Laweh').' | Liga Anak Pariaman';

        $data['seoTitle'] = $data['seoTitle'] ?? $defaultSeoTitle;
        $data['seoDescription'] = $data['seoDescription'] ?? 'Platform resmi Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, untuk jadwal, hasil pertandingan, klasemen, data klub peserta, sponsor, dan kontak panitia.';
        $data['seoImage'] = $data['seoImage'] ?? $this->defaultSeoImageUrl();
        $data['seoUrl'] = $data['seoUrl'] ?? $this->normalizeAbsoluteUrl(url()->current());
        $data['seoType'] = $data['seoType'] ?? 'website';
        $data['seoRobots'] = $data['seoRobots'] ?? 'index,follow';

        return $data;
    }

    private function sitemapEntry(string $loc, ?string $lastmod = null, ?string $changefreq = null, ?string $priority = null): array
    {
        return array_filter([
            'loc' => $this->normalizeAbsoluteUrl($loc),
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ], fn ($value) => filled($value));
    }

    private function publicResultsQuery(?Season $season = null): Builder
    {
        $season ??= $this->seasonContext->active();

        return MatchSchedule::query()->forSeason($season?->id)
            ->select([
                'id',
                'season_id',
                'age_group_id',
                'club_a_id',
                'club_b_id',
                'club_a_season_id',
                'club_b_season_id',
                'match_date',
                'kickoff_time',
                'venue',
                'score_club_a',
                'score_club_b',
                'is_finished',
            ])
            ->with([
                'ageGroup:id,name',
                'clubA:id,name,short_name,logo_url',
                'clubB:id,name,short_name,logo_url',
                'clubASeason:id,club_id,name,short_name,logo_url',
                'clubBSeason:id,club_id,name,short_name,logo_url',
                'goalEvents:id,match_id,club_id,player_id,assist_player_id,display_order',
                'goalEvents.scorer:id,name',
                'goalEvents.assistPlayer:id,name',
            ])
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->orderByDesc('match_date')
            ->orderByDesc('kickoff_time');
    }

    private function activePublicClubsQuery(): Builder
    {
        return Club::query()->visibleInActiveContext();
    }

    private function activePublicPlayersQuery(): Builder
    {
        return Player::query()->visibleInActiveContext();
    }

    private function activePublicOfficialsQuery(): Builder
    {
        return Official::query()->visibleInActiveContext()->where('is_active', true);
    }

    private function publicUpcomingMatchesQuery(?Season $season = null): Builder
    {
        $season ??= $this->seasonContext->active();

        return MatchSchedule::query()->forSeason($season?->id)
            ->whereDate('match_date', '>=', now()->toDateString())
            ->where('is_finished', false)
            ->whereHas('clubA', fn (Builder $query) => $query->visibleInActiveContext())
            ->whereHas('clubB', fn (Builder $query) => $query->visibleInActiveContext())
            ->orderBy('match_date')
            ->orderBy('kickoff_time');
    }

    private function clubVisibleInActiveContext(?Club $club): bool
    {
        return (bool) $club
            && $club->verification_status === Club::STATUS_APPROVED
            && $club->relationLoaded('user')
            && $club->user?->isActive();
    }

    private function shouldShowPublicKnockoutAgeGroup(Collection $matches, ?Season $season): bool
    {
        if (! $this->seasonContext->isActiveSeason($season)) {
            return true;
        }

        $hasInactiveClub = $matches->contains(function (MatchSchedule $match) {
            return ! $this->clubVisibleInActiveContext($match->clubA)
                || ! $this->clubVisibleInActiveContext($match->clubB);
        });

        if (! $hasInactiveClub) {
            return true;
        }

        return $matches->contains(function (MatchSchedule $match) {
            return $match->is_finished
                && $match->score_club_a !== null
                && $match->score_club_b !== null;
        });
    }

    private function normalizePublicDateFilter(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function selectedPublicSeason(Request $request): ?Season
    {
        return $this->seasonContext->resolvePublic($request->query('season'));
    }

    private function publicSeasonQuery(?Season $season): array
    {
        if (! $season || $this->seasonContext->isActiveSeason($season)) {
            return [];
        }

        return ['season' => $season->slug];
    }

    private function resolvePublicResult(string $matchSlug, ?Season $season = null): MatchSchedule
    {
        $season ??= $this->seasonContext->active();

        preg_match('/(\d+)$/', $matchSlug, $matches);
        $matchId = isset($matches[1]) ? (int) $matches[1] : 0;

        abort_unless($matchId > 0, 404);

        return $this->publicResultsQuery($season)->whereKey($matchId)->firstOrFail();
    }

    private function resolvePublicSchedule(string $matchSlug, ?Season $season = null): MatchSchedule
    {
        $season ??= $this->seasonContext->active();

        preg_match('/(\d+)$/', $matchSlug, $matches);
        $matchId = isset($matches[1]) ? (int) $matches[1] : 0;

        abort_unless($matchId > 0, 404);

        return $this->publicUpcomingMatchesQuery($season)
            ->with(['ageGroup', 'clubA', 'clubB', 'clubASeason', 'clubBSeason'])
            ->whereKey($matchId)
            ->firstOrFail();
    }

    private function buildPublicResultClubStats(MatchSchedule $match, int $clubId): array
    {
        $history = MatchSchedule::query()->forActiveSeason()
            ->select(['id', 'club_a_id', 'club_b_id', 'score_club_a', 'score_club_b', 'match_date', 'kickoff_time'])
            ->where('age_group_id', $match->age_group_id)
            ->where('competition_format', $match->competition_format)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->where(function (Builder $query) use ($clubId) {
                $query->where('club_a_id', $clubId)
                    ->orWhere('club_b_id', $clubId);
            })
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->orderBy('id')
            ->get();

        $played = 0;
        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;
        $cleanSheets = 0;
        $resultStory = 'Belum ada rekap pertandingan.';

        foreach ($history as $historyMatch) {
            $isClubA = (int) $historyMatch->club_a_id === $clubId;
            $clubScore = (int) ($isClubA ? $historyMatch->score_club_a : $historyMatch->score_club_b);
            $opponentScore = (int) ($isClubA ? $historyMatch->score_club_b : $historyMatch->score_club_a);

            $played++;
            $goalsFor += $clubScore;
            $goalsAgainst += $opponentScore;

            if ($opponentScore === 0) {
                $cleanSheets++;
            }

            if ($clubScore > $opponentScore) {
                $wins++;
                $currentOutcome = 'Kemenangan ke-'.$wins;
            } elseif ($clubScore === $opponentScore) {
                $draws++;
                $currentOutcome = 'Hasil imbang ke-'.$draws;
            } else {
                $losses++;
                $currentOutcome = 'Kekalahan ke-'.$losses;
            }

            if ((int) $historyMatch->id === (int) $match->id) {
                $resultStory = $currentOutcome.' pada '.$match->competition_format_label.' '.$match->ageGroup?->name.'.';
                break;
            }
        }

        return [
            'played' => $played,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'clean_sheets' => $cleanSheets,
            'result_story' => $resultStory,
        ];
    }

    private function buildPublicGoalTimeline(MatchSchedule $match): Collection
    {
        $scoreA = 0;
        $scoreB = 0;

        return $match->goalEvents
            ->values()
            ->map(function ($goal, int $index) use ($match, &$scoreA, &$scoreB) {
                $isClubA = (int) $goal->club_id === (int) $match->club_a_id;
                $isClubB = (int) $goal->club_id === (int) $match->club_b_id;

                if ($isClubA) {
                    $scoreA++;
                }

                if ($isClubB) {
                    $scoreB++;
                }

                return [
                    'sequence' => $index + 1,
                    'label' => $index === 0 ? 'Gol pembuka' : 'Gol ke-'.($index + 1),
                    'club_name' => $goal->club?->short_name ?: $goal->club?->name ?: ($isClubA ? ($match->clubA?->short_name ?: 'Klub A') : ($match->clubB?->short_name ?: 'Klub B')),
                    'scorer' => $goal->scorer?->name ?: 'Pemain tidak ditemukan',
                    'assist' => $goal->assistPlayer?->name,
                    'score_after' => $scoreA.' - '.$scoreB,
                    'side' => $isClubA ? 'home' : ($isClubB ? 'away' : 'neutral'),
                ];
            });
    }

    private function normalizeAbsoluteUrl(string $url): string
    {
        if (! $this->shouldForceHttpsUrls()) {
            return $url;
        }

        return preg_replace('/^http:/i', 'https:', $url) ?: $url;
    }

    private function defaultSeoImageUrl(): string
    {
        $path = public_path('og-share-card.jpg');
        $version = is_file($path) ? filemtime($path) : app()->version();

        return $this->normalizeAbsoluteUrl(asset('og-share-card.jpg').'?v='.$version);
    }

    private function publicClubLogoUrl(?Club $club, string $fallback): string
    {
        if (! $club) {
            return asset($fallback);
        }

        if (filled($club->logo_file_url)) {
            return $club->logo_file_url;
        }

        if (filled($club->logo_url)) {
            return str_starts_with($club->logo_url, 'http')
                ? $club->logo_url
                : url('/storage/'.ltrim($club->logo_url, '/'));
        }

        return asset($fallback);
    }

    private function shouldForceHttpsUrls(): bool
    {
        if (Str::startsWith((string) config('app.url'), 'https://')) {
            return true;
        }

        if (app()->runningInConsole() || ! app()->bound('request')) {
            return false;
        }

        $request = request();

        return $request->isSecure() || $request->headers->get('x-forwarded-proto') === 'https';
    }

    private function publicSponsorsData(): Collection
    {
        return Sponsor::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Sponsor $sponsor) => [
                'name' => $sponsor->name,
                'short_name' => $sponsor->short_name,
                'logo_url' => $sponsor->logo_url,
                'website_url' => $sponsor->website_url,
                'tier' => $sponsor->tier,
            ]);
    }

    private function adminReviewStats(): array
    {
        return [
            [
                'label' => 'Menunggu Review',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_SUBMITTED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_SUBMITTED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_SUBMITTED)->count()
                    + LineupList::query()->forActiveSeason()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
                'hint' => 'Total klub, ofisial, pemain, dan DSP yang baru diajukan.',
                'class' => 'border-warning border-opacity-25',
                'tone' => 'pending',
                'href' => route('dashboard.index').'#queue-admin',
            ],
            [
                'label' => 'Perlu Revisi Klub',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_REVISION)->count()
                    + Official::query()->where('verification_status', Official::STATUS_REVISION)->count()
                    + Player::query()->where('verification_status', Player::STATUS_REVISION)->count()
                    + LineupList::query()->forActiveSeason()->where('verification_status', LineupList::STATUS_REVISION)->count(),
                'hint' => 'Data yang sudah dikembalikan ke klub untuk diperbaiki.',
                'class' => 'border-info border-opacity-25',
                'tone' => 'support',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
            [
                'label' => 'Disetujui',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_APPROVED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_APPROVED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_APPROVED)->count()
                    + LineupList::query()->forActiveSeason()->where('verification_status', LineupList::STATUS_APPROVED)->count(),
                'hint' => 'Data yang sudah selesai diverifikasi admin.',
                'class' => 'border-success border-opacity-25',
                'tone' => 'approved',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
            [
                'label' => 'Ditolak',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_REJECTED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_REJECTED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_REJECTED)->count()
                    + LineupList::query()->forActiveSeason()->where('verification_status', LineupList::STATUS_REJECTED)->count(),
                'hint' => 'Data yang ditolak dan butuh tindak lanjut panitia.',
                'class' => 'border-danger border-opacity-25',
                'tone' => 'danger',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
        ];
    }

    private function adminQueues(): array
    {
        return [
            [
                'label' => 'Klub Menunggu Review',
                'count' => Club::query()->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'hint' => 'Lihat daftar klub terfilter',
                'href' => route('clubs.index', ['status' => ClubModel::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Ofisial Menunggu Review',
                'count' => Official::query()->where('verification_status', Official::STATUS_SUBMITTED)->count(),
                'hint' => 'Lihat daftar ofisial terfilter',
                'href' => route('officials.index', ['status' => Official::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Pemain Menunggu Review',
                'count' => Player::query()->where('verification_status', Player::STATUS_SUBMITTED)->count(),
                'hint' => 'Lihat daftar pemain terfilter',
                'href' => route('players.index', ['status' => Player::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'DSP Menunggu Review',
                'count' => LineupList::query()->forActiveSeason()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
                'hint' => 'Lihat daftar DSP terfilter',
                'href' => route('lineup-lists.index', ['status' => LineupList::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Akun Klub Belum Dipakai',
                'count' => User::query()->where('role', 'club')->doesntHave('club')->count(),
                'hint' => 'Buka halaman pembuatan akun',
                'href' => route('club-accounts.create'),
            ],
        ];
    }

    private function recentSubmissions(): Collection
    {
        return collect([
            Club::query()
                ->with('reviewer')
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (Club $club) => [
                    'type' => 'Klub',
                    'name' => $club->name,
                    'status' => $club->verification_status,
                    'club' => $club->name,
                    'submitted_at' => $club->submitted_at,
                    'reviewed_by' => $club->reviewer?->name,
                    'href' => route('clubs.show', $club),
                ]),
            Official::query()
                ->with(['club', 'reviewer'])
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (Official $official) => [
                    'type' => 'Ofisial',
                    'name' => $official->name,
                    'status' => $official->verification_status,
                    'club' => $official->club?->name,
                    'submitted_at' => $official->submitted_at,
                    'reviewed_by' => $official->reviewer?->name,
                    'href' => route('officials.show', $official),
                ]),
            Player::query()
                ->with(['club', 'reviewer'])
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (Player $player) => [
                    'type' => 'Pemain',
                    'name' => $player->name,
                    'status' => $player->verification_status,
                    'club' => $player->club?->name,
                    'submitted_at' => $player->submitted_at,
                    'reviewed_by' => $player->reviewer?->name,
                    'href' => route('players.show', $player),
                ]),
            LineupList::query()->forActiveSeason()
                ->with(['club', 'reviewer'])
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (LineupList $lineup) => [
                    'type' => 'DSP',
                    'name' => $lineup->title,
                    'status' => $lineup->verification_status,
                    'club' => $lineup->club?->name,
                    'submitted_at' => $lineup->submitted_at,
                    'reviewed_by' => $lineup->reviewer?->name,
                    'href' => route('lineup-lists.show', $lineup),
                ]),
        ])
            ->flatten(1)
            ->filter(fn (array $item) => $item['submitted_at'])
            ->sortByDesc('submitted_at')
            ->take(8)
            ->values();
    }

    private function oldestPendingReviews(): Collection
    {
        return collect([
            Club::query()
                ->where('verification_status', ClubModel::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (Club $club) => [
                    'type' => 'Klub',
                    'name' => $club->name,
                    'club' => $club->name,
                    'submitted_at' => $club->submitted_at,
                    'href' => route('clubs.show', $club),
                ]),
            Official::query()
                ->with('club')
                ->where('verification_status', Official::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (Official $official) => [
                    'type' => 'Ofisial',
                    'name' => $official->name,
                    'club' => $official->club?->name,
                    'submitted_at' => $official->submitted_at,
                    'href' => route('officials.show', $official),
                ]),
            Player::query()
                ->with('club')
                ->where('verification_status', Player::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (Player $player) => [
                    'type' => 'Pemain',
                    'name' => $player->name,
                    'club' => $player->club?->name,
                    'submitted_at' => $player->submitted_at,
                    'href' => route('players.show', $player),
                ]),
            LineupList::query()->forActiveSeason()
                ->with('club')
                ->where('verification_status', LineupList::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (LineupList $lineup) => [
                    'type' => 'DSP',
                    'name' => $lineup->title,
                    'club' => $lineup->club?->name,
                    'submitted_at' => $lineup->submitted_at,
                    'href' => route('lineup-lists.show', $lineup),
                ]),
        ])
            ->flatten(1)
            ->filter(fn (array $item) => $item['submitted_at'])
            ->sortBy('submitted_at')
            ->take(6)
            ->values()
            ->map(fn (array $item) => $item + [
                'waiting_label' => $this->formatPendingAge($item['submitted_at']),
            ]);
    }

    private function formatPendingAge($submittedAt): string
    {
        $minutes = (int) $submittedAt->diffInMinutes(now());

        if ($minutes < 60) {
            return $minutes.' menit';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);

        if ($days > 0) {
            return $hours > 0
                ? "{$days} hari {$hours} jam"
                : "{$days} hari";
        }

        return $hours.' jam';
    }
}

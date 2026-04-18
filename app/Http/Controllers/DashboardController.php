<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Club as ClubModel;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\Player;
use App\Models\Sponsor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function publicHome()
    {
        return view('public.home', $this->publicPageData([
            'title' => 'Liga Anak Piaman Laweh',
            'seoTitle' => 'Liga Anak Piaman Laweh | Portal Kompetisi Sepak Bola Anak',
            'activePublicPage' => 'home',
            'seoDescription' => 'Platform resmi Liga Anak Piaman Laweh untuk jadwal pertandingan, hasil, klasemen, daftar klub, sponsor, dan kontak panitia.',
        ]));
    }

    public function publicSchedule()
    {
        return view('public.schedule', $this->publicPageData([
            'title' => 'Jadwal Pertandingan',
            'seoTitle' => 'Jadwal Liga Anak Piaman Laweh | Pertandingan Terbaru',
            'activePublicPage' => 'schedule',
            'bannerTitle' => 'Jadwal Pertandingan',
            'bannerCurrent' => 'Jadwal',
            'seoDescription' => 'Lihat jadwal pertandingan terbaru Liga Anak Piaman Laweh lengkap dengan tanggal, jam kick-off, dan klub yang bertanding.',
        ]));
    }

    public function publicResults(Request $request)
    {
        $resultFormatOptions = [
            MatchSchedule::FORMAT_LEAGUE => 'Liga',
            MatchSchedule::FORMAT_KNOCKOUT => 'Knockout',
        ];
        $selectedAgeGroupId = AgeGroup::competition()->whereKey($request->integer('age_group_id'))->value('id');
        $selectedCompetitionFormat = $request->string('competition_format')->value();
        $selectedCompetitionFormat = array_key_exists($selectedCompetitionFormat, $resultFormatOptions) ? $selectedCompetitionFormat : null;
        $clubKeyword = trim((string) $request->input('q'));
        $resultViewMode = $request->string('view')->value() === 'compact' ? 'compact' : 'grid';
        $dateFrom = $this->normalizePublicDateFilter($request->input('date_from'));
        $dateTo = $this->normalizePublicDateFilter($request->input('date_to'));

        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $resultsQuery = $this->publicResultsQuery()
            ->when($selectedAgeGroupId, fn (Builder $query, int $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($selectedCompetitionFormat, fn (Builder $query, string $competitionFormat) => $query->where('competition_format', $competitionFormat))
            ->when($clubKeyword !== '', function (Builder $query) use ($clubKeyword) {
                $keyword = '%'.addcslashes($clubKeyword, '\\%_').'%';

                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->whereHas('clubA', function (Builder $clubQuery) use ($keyword) {
                        $clubQuery->where('name', 'like', $keyword)
                            ->orWhere('short_name', 'like', $keyword);
                    })->orWhereHas('clubB', function (Builder $clubQuery) use ($keyword) {
                        $clubQuery->where('name', 'like', $keyword)
                            ->orWhere('short_name', 'like', $keyword);
                    });
                });
            })
            ->when($dateFrom, fn (Builder $query, string $from) => $query->whereDate('match_date', '>=', $from))
            ->when($dateTo, fn (Builder $query, string $to) => $query->whereDate('match_date', '<=', $to));

        $resultStats = [
            'matches' => (clone $resultsQuery)->count(),
            'goals' => (int) ((clone $resultsQuery)->selectRaw('COALESCE(SUM(score_club_a + score_club_b), 0) as aggregate')->value('aggregate') ?? 0),
            'clean_sheets' => (clone $resultsQuery)
                ->where(function (Builder $query) {
                    $query->where('score_club_a', 0)
                        ->orWhere('score_club_b', 0);
                })
                ->count(),
        ];

        return view('public.results', $this->publicPageData([
            'title' => 'Hasil Pertandingan',
            'seoTitle' => 'Hasil Liga Anak Piaman Laweh | Skor Pertandingan',
            'activePublicPage' => 'results',
            'bannerTitle' => 'Hasil Pertandingan',
            'bannerCurrent' => 'Hasil',
            'seoDescription' => 'Pantau hasil pertandingan terbaru Liga Anak Piaman Laweh beserta skor akhir dan ringkasan laga.',
            'recentResults' => (clone $resultsQuery)->paginate(12)->withQueryString(),
            'featuredResult' => (clone $resultsQuery)->first(),
            'resultStats' => $resultStats,
            'resultAgeGroups' => AgeGroup::competition()->get(),
            'resultFormatOptions' => $resultFormatOptions,
            'resultFilters' => [
                'age_group_id' => $selectedAgeGroupId,
                'competition_format' => $selectedCompetitionFormat,
                'q' => $clubKeyword,
                'view' => $resultViewMode,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]));
    }

    public function publicResultShow(string $matchSlug)
    {
        $match = $this->resolvePublicResult($matchSlug);
        $match->loadMissing('goalEvents.club');

        if ($matchSlug !== $match->public_slug) {
            return redirect()->route('public.results.show', ['matchSlug' => $match->public_slug], 301);
        }

        $clubALabel = $match->clubA?->name ?: $match->clubA?->short_name ?: 'Klub A';
        $clubBLabel = $match->clubB?->name ?: $match->clubB?->short_name ?: 'Klub B';
        $matchShareUrl = route('public.results.show', ['matchSlug' => $match->public_slug]);
        $cleanSheetLabel = match (true) {
            ! $match->has_clean_sheet => 'Tidak ada clean sheet',
            (int) $match->score_club_a === 0 && (int) $match->score_club_b === 0 => 'Kedua tim clean sheet',
            (int) $match->score_club_a === 0 => ($match->clubB?->short_name ?: $match->clubB?->name ?: 'Klub B').' clean sheet',
            default => ($match->clubA?->short_name ?: $match->clubA?->name ?: 'Klub A').' clean sheet',
        };
        $winnerLabel = $match->score_club_a === $match->score_club_b
            ? 'Laga berakhir imbang'
            : ($match->score_club_a > $match->score_club_b
                ? $clubALabel.' menang'
                : $clubBLabel.' menang');
        $seoImage = $this->normalizeAbsoluteUrl($match->clubA?->logo_file_url ?: $match->clubB?->logo_file_url ?: $this->defaultSeoImageUrl());
        $matchSummary = $winnerLabel.'. Skor akhir '.$match->score_label.' pada '.optional($match->match_date)->translatedFormat('d F Y').' di '.($match->venue ?: 'lokasi pertandingan').'.';

        return view('public.result-show', $this->publicPageData([
            'title' => $clubALabel.' vs '.$clubBLabel,
            'seoTitle' => $clubALabel.' '.$match->score_label.' '.$clubBLabel.' | Hasil Liga Anak Piaman Laweh',
            'activePublicPage' => 'results',
            'bannerTitle' => 'Detail Hasil Pertandingan',
            'bannerCurrent' => 'Detail Hasil',
            'seoDescription' => $matchSummary.' Lihat rincian gol, statistik tim, dan detail laga lengkap.',
            'seoUrl' => $matchShareUrl,
            'seoType' => 'article',
            'seoImage' => $seoImage,
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
            'relatedResults' => $this->publicResultsQuery()
                ->whereKeyNot($match->id)
                ->where('age_group_id', $match->age_group_id)
                ->limit(4)
                ->get(),
        ]));
    }

    public function publicStandingsPage()
    {
        return view('public.standings', $this->publicPageData([
            'title' => 'Klasemen Liga',
            'seoTitle' => 'Klasemen Liga Anak Piaman Laweh | Posisi Klub',
            'activePublicPage' => 'standings',
            'bannerTitle' => 'Klasemen Kompetisi',
            'bannerCurrent' => 'Klasemen',
            'seoDescription' => 'Klasemen sementara Liga Anak Piaman Laweh berdasarkan hasil pertandingan resmi di setiap kelompok usia.',
        ]));
    }

    public function publicClubs()
    {
        return view('public.clubs', $this->publicPageData([
            'title' => 'Klub Peserta',
            'seoTitle' => 'Klub Peserta Liga Anak Piaman Laweh | Profil Tim',
            'activePublicPage' => 'clubs',
            'bannerTitle' => 'Daftar Klub',
            'bannerCurrent' => 'Klub',
            'seoDescription' => 'Daftar klub peserta Liga Anak Piaman Laweh lengkap dengan profil singkat, pemain, dan ofisial terdaftar.',
        ]));
    }

    public function publicSponsors()
    {
        return view('public.sponsors', $this->publicPageData([
            'title' => 'Sponsor Kompetisi',
            'seoTitle' => 'Sponsor Liga Anak Piaman Laweh | Mitra Resmi',
            'activePublicPage' => 'sponsors',
            'bannerTitle' => 'Sponsor Kompetisi',
            'bannerCurrent' => 'Sponsor',
            'featuredSponsors' => $this->publicSponsorsData(),
            'seoDescription' => 'Kenali sponsor dan mitra resmi yang mendukung penyelenggaraan Liga Anak Piaman Laweh.',
        ]));
    }

    public function publicClubShow(string $clubSlug)
    {
        preg_match('/(\d+)$/', $clubSlug, $matches);
        $clubId = isset($matches[1]) ? (int) $matches[1] : 0;

        $club = Club::query()->findOrFail($clubId);

        abort_unless($club->verification_status === Club::STATUS_APPROVED, 404);

        $club->load([
            'players' => fn ($query) => $query
                ->with('primaryAgeGroup')
                ->where('verification_status', Player::STATUS_APPROVED)
                ->orderByDesc('is_captain')
                ->orderBy('name'),
            'officials' => fn ($query) => $query
                ->with('ageGroup')
                ->where('verification_status', Official::STATUS_APPROVED)
                ->where('is_active', true)
                ->orderBy('role')
                ->orderBy('name'),
        ]);

        $clubMatches = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->where(function ($query) use ($club) {
                $query->where('club_a_id', $club->id)
                    ->orWhere('club_b_id', $club->id);
            })
            ->orderByDesc('match_date')
            ->orderByDesc('kickoff_time')
            ->limit(6)
            ->get();

        return view('public.club-show', $this->publicPageData([
            'title' => $club->name.' - Klub Peserta',
            'activePublicPage' => 'clubs',
            'bannerTitle' => $club->name,
            'bannerCurrent' => $club->short_name ?: $club->name,
            'club' => $club,
            'clubPlayers' => $club->players,
            'clubOfficials' => $club->officials,
            'clubRecentMatches' => $clubMatches,
            'seoTitle' => $club->name.' | Liga Anak Piaman Laweh',
            'seoDescription' => 'Profil klub '.$club->name.' di Liga Anak Piaman Laweh, termasuk pemain, ofisial, dan riwayat pertandingan terbaru.',
            'seoImage' => $this->defaultSeoImageUrl(),
        ]));
    }

    public function index()
    {
        $user = auth()->user();
        $clubIds = $user->isAdmin() ? Club::query()->select('id') : $user->clubs()->select('id');

        return view('competition.dashboard', [
            'title' => 'Dashboard Registrasi',
            'stats' => [
                'clubs' => Club::whereIn('id', $clubIds)->count(),
                'officials' => Official::whereIn('club_id', $clubIds)->count(),
                'players' => Player::whereIn('club_id', $clubIds)->count(),
                'lineups' => LineupList::whereIn('club_id', $clubIds)->count(),
                'pending_clubs' => Club::whereIn('id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'pending_officials' => Official::whereIn('club_id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'pending_players' => Player::whereIn('club_id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'pending_lineups' => LineupList::whereIn('club_id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
            ],
            'recentPlayers' => Player::with(['club', 'primaryAgeGroup'])->whereIn('club_id', $clubIds)->latest()->take(5)->get(),
            'recentLineups' => LineupList::with(['club', 'ageGroup'])->whereIn('club_id', $clubIds)->latest()->take(5)->get(),
            'clubSummary' => ! $user->isAdmin() ? Club::whereIn('id', $clubIds)->latest()->first() : null,
            'adminReviewStats' => $user->isAdmin() ? $this->adminReviewStats() : [],
            'adminQueues' => $user->isAdmin() ? $this->adminQueues() : [],
            'recentSubmissions' => $user->isAdmin() ? $this->recentSubmissions() : collect(),
            'oldestPendingReviews' => $user->isAdmin() ? $this->oldestPendingReviews() : collect(),
            'adminResources' => $user->isAdmin() ? [
                'club_accounts' => User::query()->where('role', 'club')->count(),
                'unused_club_accounts' => User::query()->where('role', 'club')->doesntHave('clubs')->count(),
            ] : [],
        ]);
    }

    private function publicStandings(): Collection
    {
        return MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->orderBy('age_group_id')
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->get()
            ->groupBy('age_group_id')
            ->map(function (Collection $matches) {
                $table = collect();

                foreach ($matches as $match) {
                    foreach ([
                        [
                            'club' => $match->clubA,
                            'goals_for' => (int) $match->score_club_a,
                            'goals_against' => (int) $match->score_club_b,
                        ],
                        [
                            'club' => $match->clubB,
                            'goals_for' => (int) $match->score_club_b,
                            'goals_against' => (int) $match->score_club_a,
                        ],
                    ] as $entry) {
                        if (! $entry['club']) {
                            continue;
                        }

                        $clubId = $entry['club']->id;
                        $row = $table->get($clubId, [
                            'club_id' => $clubId,
                            'club_name' => $entry['club']->name,
                            'club_short_name' => $entry['club']->short_name ?: $entry['club']->name,
                            'played' => 0,
                            'won' => 0,
                            'drawn' => 0,
                            'lost' => 0,
                            'goals_for' => 0,
                            'goals_against' => 0,
                            'goal_difference' => 0,
                            'points' => 0,
                        ]);

                        $row['played']++;
                        $row['goals_for'] += $entry['goals_for'];
                        $row['goals_against'] += $entry['goals_against'];

                        if ($entry['goals_for'] > $entry['goals_against']) {
                            $row['won']++;
                            $row['points'] += 3;
                        } elseif ($entry['goals_for'] === $entry['goals_against']) {
                            $row['drawn']++;
                            $row['points'] += 1;
                        } else {
                            $row['lost']++;
                        }

                        $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];

                        $table->put($clubId, $row);
                    }
                }

                return [
                    'age_group' => $matches->first()?->ageGroup,
                    'rows' => $table
                        ->sortBy([
                            ['points', 'desc'],
                            ['goal_difference', 'desc'],
                            ['goals_for', 'desc'],
                            ['club_name', 'asc'],
                        ])
                        ->values()
                        ->map(function (array $row, int $index) {
                            $row['position'] = $index + 1;

                            return $row;
                        })
                        ->take(5)
                        ->values(),
                ];
            })
            ->values();
    }

    private function publicKnockoutBrackets(): Collection
    {
        return MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'goalEvents.scorer', 'goalEvents.assistPlayer'])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->orderBy('age_group_id')
            ->orderBy('round_order')
            ->orderBy('bracket_slot')
            ->orderBy('match_date')
            ->get()
            ->groupBy('age_group_id')
            ->map(function (Collection $matches) {
                return [
                    'age_group' => $matches->first()?->ageGroup,
                    'rounds' => $matches
                        ->groupBy(fn (MatchSchedule $match) => $match->round_order ?: 1)
                        ->map(function (Collection $roundMatches) {
                            return [
                                'label' => $roundMatches->first()?->round_display_label ?: 'Babak Knockout',
                                'matches' => $roundMatches
                                    ->sortBy(fn (MatchSchedule $match) => $match->bracket_slot ?: PHP_INT_MAX)
                                    ->values(),
                            ];
                        })
                        ->sortKeys()
                        ->values(),
                ];
            })
            ->values();
    }

    private function publicPageData(array $overrides = []): array
    {
        $upcomingMatches = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->whereDate('match_date', '>=', now()->toDateString())
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->limit(12)
            ->get();

        $recentResults = $this->publicResultsQuery()
            ->limit(12)
            ->get();

        $featuredClubs = Club::query()
            ->where('verification_status', Club::STATUS_APPROVED)
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $featuredPlayers = Player::query()
            ->with(['club', 'primaryAgeGroup'])
            ->where('verification_status', Player::STATUS_APPROVED)
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $defaults = [
            'title' => 'Liga Anak Piaman Laweh',
            'activePublicPage' => 'home',
            'bannerTitle' => null,
            'bannerCurrent' => null,
            'publicStats' => [
                'clubs' => Club::query()->count(),
                'officials' => Official::query()->count(),
                'players' => Player::query()->count(),
                'lineups' => LineupList::query()->count(),
            ],
            'featuredClubs' => $featuredClubs,
            'featuredPlayers' => $featuredPlayers,
            'upcomingMatches' => $upcomingMatches,
            'recentResults' => $recentResults,
            'headlineMatch' => $upcomingMatches->first(),
            'featuredResult' => $recentResults->first(),
            'publicStandings' => $this->publicStandings(),
            'publicKnockoutBrackets' => $this->publicKnockoutBrackets(),
            'featuredSponsors' => $this->publicSponsorsData(),
        ];

        $data = array_merge($defaults, $overrides);
        $defaultSeoTitle = ($data['title'] ?? 'Liga Anak Piaman Laweh') === 'Liga Anak Piaman Laweh'
            ? 'Liga Anak Piaman Laweh | Portal Kompetisi Sepak Bola Anak'
            : ($data['title'] ?? 'Liga Anak Piaman Laweh').' | Liga Anak Piaman Laweh';

        $data['seoTitle'] = $data['seoTitle'] ?? $defaultSeoTitle;
        $data['seoDescription'] = $data['seoDescription'] ?? 'Platform resmi Liga Anak Piaman Laweh untuk jadwal, hasil pertandingan, klasemen, data klub peserta, sponsor, dan kontak panitia.';
        $data['seoImage'] = $data['seoImage'] ?? $this->defaultSeoImageUrl();
        $data['seoUrl'] = $data['seoUrl'] ?? $this->normalizeAbsoluteUrl(url()->current());
        $data['seoType'] = $data['seoType'] ?? 'website';
        $data['seoRobots'] = $data['seoRobots'] ?? 'index,follow';

        return $data;
    }

    private function publicResultsQuery(): Builder
    {
        return MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'goalEvents.scorer', 'goalEvents.assistPlayer'])
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->orderByDesc('match_date')
            ->orderByDesc('kickoff_time');
    }

    private function normalizePublicDateFilter(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function resolvePublicResult(string $matchSlug): MatchSchedule
    {
        preg_match('/(\d+)$/', $matchSlug, $matches);
        $matchId = isset($matches[1]) ? (int) $matches[1] : 0;

        abort_unless($matchId > 0, 404);

        return $this->publicResultsQuery()->whereKey($matchId)->firstOrFail();
    }

    private function buildPublicResultClubStats(MatchSchedule $match, int $clubId): array
    {
        $history = MatchSchedule::query()
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
        return $this->normalizeAbsoluteUrl(asset('og-share-card.jpg'));
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

    public function workflowPdf(Request $request)
    {
        abort_unless($request->user()?->isClubUser(), 403);

        $pdf = Pdf::loadView('pdf.club-workflow', [
            'generatedAt' => now(),
            'steps' => [
                [
                    'number' => '1',
                    'title' => 'Terima Akun dan Login',
                    'description' => 'Tahap awal untuk akun club adalah menerima akses login dan masuk ke dashboard registrasi.',
                    'screenshot' => [
                        'title' => 'Tampilan dashboard akun club',
                        'caption' => 'Penanda pada sidebar menunjukkan urutan menu utama yang dipakai akun club: 1) Klub, 2) Ofisial, 3) Pemain, dan 4) DSP.',
                        'path' => public_path('workflow-screens/dashboard-annotated.png'),
                    ],
                    'details' => [
                        'Gunakan email akun club dan password awal yang diberikan panitia atau admin.',
                        'Login ke sistem registrasi sampai berhasil masuk ke dashboard akun club.',
                        'Pastikan menu utama untuk registrasi seperti Klub, Ofisial, Pemain, dan DSP dapat diakses dengan benar.',
                    ],
                    'result' => 'Akun club berhasil masuk ke dashboard dan siap mulai mengerjakan registrasi.',
                    'accent' => '#ef6b2e',
                    'icon' => 'LOGIN',
                ],
                [
                    'number' => '2',
                    'title' => 'Lengkapi Data Klub',
                    'description' => 'Akun club membuka menu Klub untuk mengisi identitas utama peserta sebelum melanjutkan ke modul lain.',
                    'screenshot' => [
                        'title' => 'Form edit data klub',
                        'caption' => 'Isi identitas klub, unggah dokumen wajib, simpan perubahan, lalu submit verifikasi hanya setelah seluruh data benar.',
                        'path' => public_path('workflow-screens/club-edit-annotated.png'),
                    ],
                    'details' => [
                        'Isi nama klub, nama singkat, nama manajer, zona, kota, tahun berdiri, dan alamat.',
                        'Unduh template surat pernyataan, isi data klub, tanda tangan, lalu unggah kembali bersama logo klub.',
                        'Periksa ulang apakah seluruh identitas klub sudah benar dan sama dengan dokumen pendukung yang diunggah.',
                    ],
                    'result' => 'Profil klub lengkap dan siap diajukan ke verifikasi atau dilanjutkan ke input ofisial dan pemain.',
                    'accent' => '#ff9f43',
                    'icon' => 'KLUB',
                ],
                [
                    'number' => '3',
                    'title' => 'Input Data Ofisial',
                    'description' => 'Akun club mendaftarkan setiap ofisial secara terpisah lengkap dengan identitas, dokumen, dan penugasan.',
                    'screenshot' => [
                        'title' => 'Form input ofisial',
                        'caption' => 'Pilih klub, isi identitas ofisial, unggah lisensi dan dokumen pendukung, lalu simpan data ofisial.',
                        'path' => public_path('workflow-screens/official-create-annotated.png'),
                    ],
                    'details' => [
                        'Isi klub, peran ofisial, nama, nomor lisensi, telepon, email, tempat lahir, tanggal lahir, dan kewarganegaraan.',
                        'Unggah pas foto 3x4, bukti lisensi, serta KTP atau identitas lain yang diminta.',
                        'Tambahkan kelompok usia yang diikuti, jabatan per kelompok usia, level lisensi, dan catatan bila diperlukan.',
                    ],
                    'result' => 'Data ofisial tersimpan sebagai draft dan dapat diedit, diajukan, atau direview kemudian.',
                    'accent' => '#43aa8b',
                    'icon' => 'OFC',
                ],
                [
                    'number' => '4',
                    'title' => 'Input Data Pemain',
                    'description' => 'Akun club mengisi data pemain satu per satu lengkap dengan dokumen administrasi dan kelompok usia.',
                    'screenshot' => [
                        'title' => 'Form input pemain',
                        'caption' => 'Isi identitas pemain, unggah dokumen administrasi, lalu atur kelompok usia, posisi, dan detail registrasi sebelum menyimpan.',
                        'path' => public_path('workflow-screens/player-create-annotated.png'),
                    ],
                    'details' => [
                        'Isi identitas pemain seperti nama, nama ibu kandung, sekolah, nomor registrasi, tinggi, berat, tempat lahir, tanggal lahir, dan dominant foot.',
                        'Unggah pas foto 3x4, file KK, ijazah, rapor, dan akta kelahiran sesuai kebutuhan verifikasi.',
                        'Tetapkan kelompok usia, musim, nomor punggung, posisi, serta catatan per kelompok usia. Satu pemain dapat tercatat di lebih dari satu kelompok usia.',
                    ],
                    'result' => 'Data pemain masuk ke daftar registrasi dan siap dilanjutkan ke penyusunan roster pertandingan.',
                    'accent' => '#5f6df8',
                    'icon' => 'PLY',
                ],
                [
                    'number' => '5',
                    'title' => 'Susun DSP per Pertandingan',
                    'description' => 'Setelah data pemain tersedia, akun club membuat DSP untuk pertandingan yang akan dijalani.',
                    'screenshot' => [
                        'title' => 'Form penyusunan DSP',
                        'caption' => 'Pilih klub dan kelompok usia, tentukan starter serta cadangan, lalu simpan DSP setelah roster sesuai aturan.',
                        'path' => public_path('workflow-screens/lineup-create-annotated.png'),
                    ],
                    'details' => [
                        'Pilih klub dan kelompok usia, lalu isi judul DSP, match day, tanggal pertandingan, nama pelatih, warna jersey, venue, jam main, dan catatan.',
                        'Pilih pemain yang tersedia ke dalam daftar starter dan cadangan sesuai filter klub dan kelompok usia.',
                        'Isi urutan tampil pemain pada roster DSP. Sistem menampilkan panduan jumlah starter dan batas maksimal cadangan.',
                    ],
                    'result' => 'DSP tersimpan dan dapat diperiksa ulang sebelum diajukan ke verifikasi.',
                    'accent' => '#e65252',
                    'icon' => 'DSP',
                ],
                [
                    'number' => '6',
                    'title' => 'Ajukan Verifikasi',
                    'description' => 'Setelah data dianggap siap, akun club harus mengajukan item terkait ke proses verifikasi.',
                    'screenshot' => [
                        'title' => 'Panel submit verifikasi',
                        'caption' => 'Tombol submit hanya dipakai setelah data lengkap. Area bertanda menunjukkan aksi akhir untuk mengirim item ke proses review.',
                        'path' => public_path('workflow-screens/submit-annotated.png'),
                    ],
                    'details' => [
                        'Pada data klub, ofisial, pemain, dan DSP, club menekan tombol Submit Verifikasi ketika data sudah lengkap.',
                        'Setelah dikirim, status berubah menjadi Dalam Proses atau submitted dan waktu pengajuan tercatat di sistem.',
                        'Data dianggap lengkap bila semua field penting terisi, dokumen wajib sudah diunggah, identitas sesuai, kelompok usia sudah ditetapkan, dan tidak ada bagian data yang masih kosong atau bertentangan.',
                        'Untuk klub, lengkap berarti profil klub, manajer, alamat, logo, dan surat pernyataan sudah siap diperiksa.',
                        'Untuk ofisial, lengkap berarti identitas, peran, lisensi, dokumen pendukung, dan kelompok usia sudah sesuai kebutuhan kompetisi.',
                        'Untuk pemain, lengkap berarti identitas pemain, dokumen administrasi, kelompok usia, posisi, dan nomor punggung sudah benar.',
                        'Untuk DSP, lengkap berarti pertandingan, pelatih, kelompok usia, starter, cadangan, dan urutan roster sudah sesuai aturan sistem.',
                        'Sebelum submit, akun club wajib membuka ulang item terkait dan memastikan tidak ada file salah, file kosong, atau data yang belum diperbarui.',
                    ],
                    'result' => 'Item yang diajukan masuk ke antrian review admin.',
                    'accent' => '#1f7a8c',
                    'icon' => 'SUBMIT',
                ],
                [
                    'number' => '7',
                    'title' => 'Tindak Lanjut Hasil Review',
                    'description' => 'Akun club wajib memantau hasil review dan menindaklanjuti setiap status dengan benar.',
                    'details' => [
                        'Jika status Approved atau Diterima, artinya data dinilai sesuai oleh admin. Akun club tidak perlu submit ulang untuk item tersebut, tetapi harus memastikan data yang sudah disetujui dipakai secara konsisten pada proses berikutnya.',
                        'Sesudah approved, club dapat melanjutkan ke tahapan lanjutan seperti melengkapi modul lain, menyiapkan DSP, atau mengunduh keluaran seperti ID Card bila fitur itu tersedia pada modul terkait.',
                        'Jika status Revision atau Perlu Revisi, artinya data masih bisa diperbaiki oleh club. Akun club harus membuka item yang direvisi, membaca catatan admin secara teliti, memperbaiki bagian yang diminta, memeriksa ulang dokumen, lalu menekan Submit Verifikasi kembali.',
                        'Jika status Rejected atau Ditolak, artinya data tidak diterima dalam kondisi saat ini. Akun club harus menganggap item tersebut belum lolos verifikasi dan wajib meninjau penyebab penolakan sebelum melanjutkan.',
                        'Tindakan saat rejected: baca catatan admin, cocokan dengan field dan dokumen yang ada, perbaiki semua data yang tidak valid atau tidak sesuai, lalu pastikan ke admin atau panitia apakah item boleh diedit langsung atau perlu dibuka ulang secara administratif.',
                        'Bila item rejected masih bisa diedit oleh sistem atau oleh arahan admin, club harus memperbaiki seluruh kekurangan, bukan hanya satu bagian yang paling terlihat, lalu ajukan ulang hanya setelah seluruh syarat benar-benar terpenuhi.',
                        'Jika penolakan terjadi karena dokumen tidak jelas, dokumen salah, data identitas tidak cocok, kelompok usia tidak sesuai, roster DSP tidak memenuhi aturan, atau data penting masih kosong, maka semua sumber masalah itu harus dibereskan sebelum mencoba mengajukan ulang.',
                        'Club tidak boleh menganggap approved berarti pekerjaan selesai total. Semua modul yang masih draft, submitted, revision, atau rejected tetap harus dipantau sampai seluruh kebutuhan kompetisi berstatus diterima.',
                        'Club juga dapat membuka atau mengunduh PDF ini sebagai referensi selama proses verifikasi dan administrasi pertandingan.',
                    ],
                    'result' => 'Workflow dianggap selesai saat seluruh data yang diperlukan berstatus Diterima.',
                    'accent' => '#b5179e',
                    'icon' => 'FINAL',
                ],
            ],
            'completionChecks' => [
                'Semua field utama pada modul yang sedang dikerjakan sudah terisi dan tidak ada data penting yang kosong.',
                'Nama, tanggal lahir, identitas, kelompok usia, jabatan, posisi, dan nomor punggung sudah sesuai dokumen pendukung.',
                'Dokumen wajib sudah diunggah, dapat dibuka, tidak buram, tidak terpotong, dan milik orang atau klub yang benar.',
                'Tidak ada pertentangan data antar field, antar dokumen, atau antar modul yang diinput oleh akun club.',
                'Roster DSP sudah mengikuti aturan jumlah starter, cadangan, urutan pemain, dan filter klub atau kelompok usia.',
                'Semua catatan review sebelumnya sudah diperbaiki seluruhnya sebelum submit ulang.',
            ],
            'statusGuides' => [
                [
                    'label' => 'Approved / Diterima',
                    'color' => '#0f9d58',
                    'body' => 'Item dinyatakan lolos verifikasi. Akun club tidak perlu submit ulang untuk item itu. Yang harus dilakukan adalah melanjutkan pekerjaan ke modul lain yang belum selesai, menjaga konsistensi data yang sudah diterima, dan memakai hasil approved sebagai dasar proses berikutnya.',
                ],
                [
                    'label' => 'Revision / Perlu Revisi',
                    'color' => '#d97706',
                    'body' => 'Item masih bisa diperbaiki. Akun club harus membuka detail data, membaca catatan review, memperbaiki semua bagian yang diminta, mengganti dokumen jika perlu, lalu memeriksa ulang kelengkapan sebelum menekan Submit Verifikasi kembali.',
                ],
                [
                    'label' => 'Rejected / Ditolak',
                    'color' => '#dc2626',
                    'body' => 'Item belum diterima dan tidak boleh dianggap selesai. Akun club harus menelusuri sebab penolakan, memperbaiki seluruh sumber masalah, memastikan apakah item masih dapat diedit langsung atau perlu dibuka ulang, lalu baru mengajukan kembali setelah syarat benar-benar terpenuhi.',
                ],
            ],
            'rejectedActions' => [
                'Baca catatan review sampai jelas field atau dokumen mana yang menjadi penyebab penolakan.',
                'Bandingkan data di sistem dengan dokumen fisik atau dokumen sumber untuk memastikan tidak ada salah identitas atau salah unggah.',
                'Perbaiki seluruh bagian yang tidak valid: data kosong, dokumen salah, dokumen tidak terbaca, kelompok usia tidak cocok, posisi atau jabatan keliru, atau roster DSP tidak sesuai aturan.',
                'Lakukan pengecekan ulang menyeluruh pada item yang ditolak agar masalah yang sama tidak berulang pada submit berikutnya.',
                'Hubungi panitia atau pihak verifikator jika status reject membuat item perlu dibuka ulang atau jika catatan review belum cukup jelas untuk ditindaklanjuti.',
            ],
        ])->setPaper('a4', 'portrait');

        $fileName = 'tahapan-workflow-dashboard-club.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function adminManualPdf(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $pdf = Pdf::loadView('manuals.admin', [
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileName = 'manual-admin-liga-anak-piaman-laweh.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function clubManualPdf(Request $request)
    {
        abort_unless($request->user()?->isClubUser(), 403);

        $pdf = Pdf::loadView('manuals.club', [
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileName = 'manual-club-liga-anak-piaman-laweh.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    private function adminReviewStats(): array
    {
        return [
            [
                'label' => 'Menunggu Review',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_SUBMITTED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_SUBMITTED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_SUBMITTED)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
                'hint' => 'Total klub, ofisial, pemain, dan DSP yang baru diajukan.',
                'class' => 'border-warning border-opacity-25',
                'href' => route('dashboard.index').'#queue-admin',
            ],
            [
                'label' => 'Perlu Revisi Klub',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_REVISION)->count()
                    + Official::query()->where('verification_status', Official::STATUS_REVISION)->count()
                    + Player::query()->where('verification_status', Player::STATUS_REVISION)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_REVISION)->count(),
                'hint' => 'Data yang sudah dikembalikan ke klub untuk diperbaiki.',
                'class' => 'border-info border-opacity-25',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
            [
                'label' => 'Disetujui',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_APPROVED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_APPROVED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_APPROVED)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_APPROVED)->count(),
                'hint' => 'Data yang sudah selesai diverifikasi admin.',
                'class' => 'border-success border-opacity-25',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
            [
                'label' => 'Ditolak',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_REJECTED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_REJECTED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_REJECTED)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_REJECTED)->count(),
                'hint' => 'Data yang ditolak dan butuh tindak lanjut panitia.',
                'class' => 'border-danger border-opacity-25',
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
                'count' => LineupList::query()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
                'hint' => 'Lihat daftar DSP terfilter',
                'href' => route('lineup-lists.index', ['status' => LineupList::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Akun Klub Belum Dipakai',
                'count' => User::query()->where('role', 'club')->doesntHave('clubs')->count(),
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
                    'href' => route('clubs.index', ['search' => $club->name]),
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
            LineupList::query()
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
                    'href' => route('clubs.index', ['search' => $club->name, 'status' => ClubModel::STATUS_SUBMITTED]),
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
            LineupList::query()
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

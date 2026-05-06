<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;
use App\Services\HtmlPdfRenderer;
use App\Services\SeasonContext;
use App\Services\SeasonSnapshotService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MatchScheduleController extends Controller
{
    public function __construct(
        private SeasonContext $seasonContext,
        private SeasonSnapshotService $seasonSnapshotService
    ) {}

    public function index(Request $request)
    {
        return $this->renderMatchIndex($request, MatchSchedule::FORMAT_LEAGUE);
    }

    public function leagueIndex(Request $request)
    {
        return $this->renderMatchIndex($request, MatchSchedule::FORMAT_LEAGUE);
    }

    public function knockoutIndex(Request $request)
    {
        return $this->renderMatchIndex($request, MatchSchedule::FORMAT_KNOCKOUT);
    }

    public function create(Request $request)
    {
        $this->ensureWritableSeasonContext();

        $matchSchedule = new MatchSchedule;
        $requestedCompetitionFormat = $this->normalizeCompetitionFormat($request->input('competition_format'));
        $matchSchedule->competition_format = $requestedCompetitionFormat ?? MatchSchedule::FORMAT_LEAGUE;
        $matchSchedule->age_group_id = $request->integer('age_group_id') ?: null;
        $matchSchedule->round_label = $request->string('round_label')->value() ?: null;
        $matchSchedule->round_order = $request->integer('round_order') ?: null;
        $matchSchedule->bracket_slot = $request->integer('bracket_slot') ?: null;
        $redirectRouteName = $this->resolveMatchIndexRouteName($request, $requestedCompetitionFormat);

        return view('competition.matches.create', [
            'title' => 'Tambah Jadwal Pertandingan',
            'matchSchedule' => $matchSchedule,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
            'knockoutEliminatedClubIdsByAgeGroup' => $this->knockoutEliminatedClubIdsByAgeGroup(),
            'knockoutSourceOptionsByAgeGroup' => $this->buildKnockoutSourceOptionsByAgeGroup(),
            'currentMatchClubIds' => [],
            'backUrl' => route($redirectRouteName),
            'redirectRouteName' => $redirectRouteName,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureWritableSeasonContext();

        $match = MatchSchedule::create($this->validatedData($request));
        $redirectRouteName = $this->resolveMatchIndexRouteName($request, $match->competition_format);

        return redirect()->route($redirectRouteName)->with('status', 'Jadwal pertandingan berhasil ditambahkan.');
    }

    public function edit(Request $request, MatchSchedule $match)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonMatch($match);
        $redirectRouteName = $this->resolveMatchIndexRouteName($request, $match->competition_format);

        return view('competition.matches.edit', [
            'title' => 'Edit Jadwal Pertandingan',
            'matchSchedule' => $match,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
            'knockoutEliminatedClubIdsByAgeGroup' => $this->knockoutEliminatedClubIdsByAgeGroup($match->id),
            'knockoutSourceOptionsByAgeGroup' => $this->buildKnockoutSourceOptionsByAgeGroup($match->id),
            'currentMatchClubIds' => array_values(array_filter([(int) $match->club_a_id, (int) $match->club_b_id])),
            'backUrl' => route($redirectRouteName),
            'redirectRouteName' => $redirectRouteName,
        ]);
    }

    public function update(Request $request, MatchSchedule $match)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonMatch($match);

        if ($match->lineupLists()->exists()) {
            throw ValidationException::withMessages([
                'match' => 'Jadwal ini sudah dipakai di DSP, jadi tidak bisa diubah. Buat jadwal baru atau hapus DSP yang terkait terlebih dahulu.',
            ]);
        }

        $match->update($this->validatedData($request));

        $redirectRouteName = $this->resolveMatchIndexRouteName($request, $match->competition_format);

        return redirect()->route($redirectRouteName)->with('status', 'Jadwal pertandingan berhasil diperbarui.');
    }

    public function destroy(Request $request, MatchSchedule $match)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonMatch($match);

        if ($match->lineupLists()->exists()) {
            throw ValidationException::withMessages([
                'match' => 'Jadwal pertandingan yang sudah dipakai DSP tidak bisa dihapus.',
            ]);
        }

        $competitionFormat = $match->competition_format;
        $match->delete();

        $redirectRouteName = $this->resolveMatchIndexRouteName($request, $competitionFormat);

        return redirect()->route($redirectRouteName)->with('status', 'Jadwal pertandingan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $this->ensureWritableSeasonContext();

        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer', 'exists:match_schedules,id'],
        ]);

        $matches = MatchSchedule::query()->forActiveSeason()
            ->withCount('lineupLists')
            ->whereIn('id', $validated['selected_ids'])
            ->get();

        $deletableIds = $matches
            ->where('lineup_lists_count', 0)
            ->pluck('id');

        $blockedCount = $matches->count() - $deletableIds->count();
        $redirectRouteName = $this->resolveMatchIndexRouteName(
            $request,
            $this->normalizeCompetitionFormat($request->input('competition_format')),
        );
        $redirectParameters = $this->matchIndexQueryParameters($request, $redirectRouteName);

        if ($deletableIds->isEmpty()) {
            return redirect()
                ->route($redirectRouteName, $redirectParameters)
                ->withErrors([
                    'match' => 'Tidak ada jadwal yang bisa dihapus. Jadwal yang sudah dipakai DSP tidak bisa dihapus massal.',
                ]);
        }

        MatchSchedule::query()->forActiveSeason()->whereIn('id', $deletableIds)->delete();

        $message = $deletableIds->count().' jadwal pertandingan berhasil dihapus.';

        if ($blockedCount > 0) {
            $message .= ' '.$blockedCount.' jadwal dilewati karena sudah dipakai DSP.';
        }

        return redirect()
            ->route($redirectRouteName, $redirectParameters)
            ->with('status', $message);
    }

    public function updateKnockoutPosition(Request $request, MatchSchedule $match)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonMatch($match);
        abort_unless($match->competition_format === MatchSchedule::FORMAT_KNOCKOUT, 404);

        $validated = $request->validate([
            'age_group_id' => ['required', 'integer'],
            'round_label' => ['required', 'string', 'max:255'],
            'round_order' => ['required', 'integer', 'min:1', 'max:999'],
            'bracket_slot' => ['required', 'integer', 'min:1', 'max:999'],
            'swap_match_id' => ['nullable', 'integer', 'exists:match_schedules,id'],
            'source_round_label' => ['nullable', 'string', 'max:255', 'required_with:swap_match_id'],
            'source_round_order' => ['nullable', 'integer', 'min:1', 'max:999', 'required_with:swap_match_id'],
            'source_bracket_slot' => ['nullable', 'integer', 'min:1', 'max:999', 'required_with:swap_match_id'],
        ]);

        if ((int) $validated['age_group_id'] !== (int) $match->age_group_id) {
            throw ValidationException::withMessages([
                'age_group_id' => 'Perpindahan antar kelompok usia tidak diizinkan dari board knockout.',
            ]);
        }

        if (filled($validated['swap_match_id'] ?? null)) {
            $swapMatch = MatchSchedule::query()->forActiveSeason()->findOrFail((int) $validated['swap_match_id']);

            if ($swapMatch->competition_format !== MatchSchedule::FORMAT_KNOCKOUT || (int) $swapMatch->age_group_id !== (int) $match->age_group_id) {
                throw ValidationException::withMessages([
                    'swap_match_id' => 'Pertukaran slot hanya bisa dilakukan dalam bracket knockout kelompok usia yang sama.',
                ]);
            }

            $sourcePosition = [
                'round_label' => $validated['source_round_label'],
                'round_order' => (int) $validated['source_round_order'],
                'bracket_slot' => (int) $validated['source_bracket_slot'],
            ];
            $targetPosition = [
                'round_label' => $validated['round_label'],
                'round_order' => (int) $validated['round_order'],
                'bracket_slot' => (int) $validated['bracket_slot'],
            ];
            $roundOrderOverrides = [
                (int) $match->id => $targetPosition['round_order'],
                (int) $swapMatch->id => $sourcePosition['round_order'],
            ];

            $this->validateKnockoutRoundDependencies(
                currentMatch: $match,
                ageGroupId: (int) $match->age_group_id,
                proposedRoundOrder: $targetPosition['round_order'],
                sourceMatchAId: $match->source_match_a_id ? (int) $match->source_match_a_id : null,
                sourceMatchBId: $match->source_match_b_id ? (int) $match->source_match_b_id : null,
                roundOrderOverrides: $roundOrderOverrides,
            );

            $this->validateKnockoutRoundDependencies(
                currentMatch: $swapMatch,
                ageGroupId: (int) $swapMatch->age_group_id,
                proposedRoundOrder: $sourcePosition['round_order'],
                sourceMatchAId: $swapMatch->source_match_a_id ? (int) $swapMatch->source_match_a_id : null,
                sourceMatchBId: $swapMatch->source_match_b_id ? (int) $swapMatch->source_match_b_id : null,
                roundOrderOverrides: $roundOrderOverrides,
            );

            DB::transaction(function () use ($match, $swapMatch, $targetPosition, $sourcePosition) {
                $match->update($targetPosition);
                $swapMatch->update($sourcePosition);
            });

            return response()->json([
                'status' => 'ok',
                'message' => 'Posisi bracket berhasil ditukar.',
            ]);
        }

        $this->validateKnockoutRoundDependencies(
            currentMatch: $match,
            ageGroupId: (int) $match->age_group_id,
            proposedRoundOrder: (int) $validated['round_order'],
            sourceMatchAId: $match->source_match_a_id ? (int) $match->source_match_a_id : null,
            sourceMatchBId: $match->source_match_b_id ? (int) $match->source_match_b_id : null,
        );

        $this->validateKnockoutBracketPlacement([
            'competition_format' => MatchSchedule::FORMAT_KNOCKOUT,
            'age_group_id' => (int) $match->age_group_id,
            'source_match_a_id' => $match->source_match_a_id,
            'source_match_b_id' => $match->source_match_b_id,
            'round_label' => $validated['round_label'],
            'round_order' => (int) $validated['round_order'],
            'bracket_slot' => (int) $validated['bracket_slot'],
        ], $match->id);

        $match->update([
            'round_label' => $validated['round_label'],
            'round_order' => (int) $validated['round_order'],
            'bracket_slot' => (int) $validated['bracket_slot'],
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Posisi bracket berhasil diperbarui.',
        ]);
    }

    private function renderMatchIndex(Request $request, ?string $forcedCompetitionFormat = null): View
    {
        $matchIndexMeta = $this->matchIndexMeta($forcedCompetitionFormat);
        $activeCompetitionFormat = $forcedCompetitionFormat ?: $this->normalizeCompetitionFormat($request->input('competition_format'));

        if ($forcedCompetitionFormat === MatchSchedule::FORMAT_KNOCKOUT) {
            $ageGroupSummaries = $this->buildKnockoutAgeGroupSummaries();
            $requestedAgeGroupId = $request->integer('age_group_id') ?: null;
            $selectedAgeGroupId = $ageGroupSummaries->contains(fn (array $summary) => $summary['id'] === $requestedAgeGroupId)
                ? $requestedAgeGroupId
                : (data_get($ageGroupSummaries->firstWhere('has_matches', true), 'id')
                    ?? data_get($ageGroupSummaries->first(), 'id'));
            $selectedBoard = $selectedAgeGroupId
                ? $this->buildKnockoutAdminBoards($selectedAgeGroupId)->first()
                : null;

            return view('competition.matches.knockout-index', [
                'title' => $matchIndexMeta['title'],
                'pageHeading' => $matchIndexMeta['heading'],
                'pageDescription' => $matchIndexMeta['description'],
                'ageGroupSummaries' => $ageGroupSummaries,
                'selectedAgeGroupId' => $selectedAgeGroupId,
                'selectedBoard' => $selectedBoard,
                'indexRouteName' => $matchIndexMeta['route'],
            ]);
        }

        [$sort, $direction] = $this->resolveSort(
            request: $request,
            defaultSort: 'match_date',
            defaultDirection: 'asc',
            allowedSorts: ['match_day', 'matchup', 'age_group', 'competition_format', 'round_order', 'venue', 'match_date', 'kickoff_time'],
        );

        $matches = MatchSchedule::query()->forActiveSeason()
            ->with(['ageGroup', 'clubA', 'clubB', 'lineupLists:id,match_id,club_id,verification_status'])
            ->when($request->input('club_id'), function ($query, $clubId) {
                $query->where(fn ($inner) => $inner
                    ->where('club_a_id', $clubId)
                    ->orWhere('club_b_id', $clubId));
            })
            ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($activeCompetitionFormat, fn ($query, $format) => $query->where('competition_format', $format))
            ->when($request->input('lineup_status'), function ($query, $status) {
                if ($status === 'complete') {
                    $query->whereHas('lineupLists', fn ($inner) => $inner->whereColumn('lineup_lists.club_id', 'match_schedules.club_a_id'))
                        ->whereHas('lineupLists', fn ($inner) => $inner->whereColumn('lineup_lists.club_id', 'match_schedules.club_b_id'));
                }

                if ($status === 'pending') {
                    $query->where(function ($inner) {
                        $inner->whereDoesntHave('lineupLists', fn ($lineupQuery) => $lineupQuery->whereColumn('lineup_lists.club_id', 'match_schedules.club_a_id'))
                            ->orWhereDoesntHave('lineupLists', fn ($lineupQuery) => $lineupQuery->whereColumn('lineup_lists.club_id', 'match_schedules.club_b_id'));
                    });
                }
            });

        $this->applyMatchSort($matches, $sort, $direction);

        $matches = $matches
            ->paginate(10)
            ->withQueryString();

        return view('competition.matches.index', [
            'title' => $matchIndexMeta['title'],
            'pageHeading' => $matchIndexMeta['heading'],
            'pageDescription' => $matchIndexMeta['description'],
            'matches' => $matches,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
            'fixedCompetitionFormat' => $forcedCompetitionFormat,
            'indexRouteName' => $matchIndexMeta['route'],
            'createUrl' => route('matches.create', array_filter([
                'redirect_route' => $matchIndexMeta['route'],
                'competition_format' => $forcedCompetitionFormat,
            ])),
        ]);
    }

    public function results(Request $request): View
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        [$sort, $direction] = $this->resolveSort(
            request: $request,
            defaultSort: 'match_date',
            defaultDirection: 'desc',
            allowedSorts: ['match_day', 'matchup', 'age_group', 'competition_format', 'round_order', 'match_date', 'venue', 'is_finished'],
        );

        $matches = MatchSchedule::query()->forActiveSeason()
            ->with([
                'ageGroup',
                'clubA',
                'clubB',
                'goalEvents.scorer',
                'goalEvents.assistPlayer',
                'lineupLists' => fn ($query) => $query
                    ->select('id', 'match_id', 'club_id', 'verification_status')
                    ->with(['players' => fn ($playerQuery) => $playerQuery
                        ->select('players.id', 'players.name')
                        ->withPivot(['jersey_number', 'role', 'display_order'])
                        ->orderByPivot('role')
                        ->orderByPivot('display_order')]),
            ])
            ->when(! $user->isAdmin(), function ($query) use ($clubIds) {
                $query->where(function ($inner) use ($clubIds) {
                    $inner->whereIn('club_a_id', $clubIds)
                        ->orWhereIn('club_b_id', $clubIds);
                });
            })
            ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($request->input('competition_format'), fn ($query, $format) => $query->where('competition_format', $format));

        $this->applyMatchSort($matches, $sort, $direction);

        $matches = $matches
            ->paginate(10)
            ->withQueryString();

        return view('competition.matches.results', [
            'title' => 'Hasil Pertandingan',
            'matches' => $matches,
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
        ]);
    }

    public function standings(Request $request): View
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;

        return view('competition.matches.standings', [
            'title' => 'Klasemen',
            'ageGroups' => AgeGroup::competition()->get(),
            'selectedAgeGroup' => $this->selectedReportAgeGroup($ageGroupId),
            'standings' => $this->buildLeagueStandings(
                user: $user,
                clubIds: $clubIds,
                ageGroupId: $ageGroupId,
            ),
        ]);
    }

    public function standingsPdf(Request $request)
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;
        $selectedAgeGroup = $this->selectedReportAgeGroup($ageGroupId);

        return $this->streamReportPdf(
            request: $request,
            view: 'competition.matches.standings-pdf',
            fileName: $this->pdfFileName('laporan-klasemen', $selectedAgeGroup),
            data: [
                'generatedAt' => now(),
                'selectedAgeGroup' => $selectedAgeGroup,
                'standings' => $this->buildLeagueStandings(
                    user: $user,
                    clubIds: $clubIds,
                    ageGroupId: $ageGroupId,
                ),
            ],
        );
    }

    public function topScorers(Request $request): View
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;

        return view('competition.matches.top-scorers', [
            'title' => 'Top Skor',
            'ageGroups' => AgeGroup::competition()->get(),
            'selectedAgeGroup' => $this->selectedReportAgeGroup($ageGroupId),
            'topScorers' => $this->buildGoalLeaderboard(
                user: $user,
                clubIds: $clubIds,
                type: 'scorer',
                ageGroupId: $ageGroupId,
            ),
        ]);
    }

    public function topScorersPdf(Request $request)
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;
        $selectedAgeGroup = $this->selectedReportAgeGroup($ageGroupId);

        return $this->streamReportPdf(
            request: $request,
            view: 'competition.matches.leaderboard-pdf',
            fileName: $this->pdfFileName('laporan-top-skor', $selectedAgeGroup),
            data: [
                'generatedAt' => now(),
                'selectedAgeGroup' => $selectedAgeGroup,
                'title' => 'Top Skor',
                'description' => 'Peringkat pencetak gol terbanyak dari pertandingan yang sudah selesai.',
                'metricLabel' => 'Gol',
                'emptyMessage' => 'Belum ada data top skor untuk filter ini.',
                'leaderboards' => $this->buildGoalLeaderboard(
                    user: $user,
                    clubIds: $clubIds,
                    type: 'scorer',
                    ageGroupId: $ageGroupId,
                ),
            ],
        );
    }

    public function topAssists(Request $request): View
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;

        return view('competition.matches.top-assists', [
            'title' => 'Top Assist',
            'ageGroups' => AgeGroup::competition()->get(),
            'selectedAgeGroup' => $this->selectedReportAgeGroup($ageGroupId),
            'topAssists' => $this->buildGoalLeaderboard(
                user: $user,
                clubIds: $clubIds,
                type: 'assist',
                ageGroupId: $ageGroupId,
            ),
        ]);
    }

    public function topAssistsPdf(Request $request)
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;
        $selectedAgeGroup = $this->selectedReportAgeGroup($ageGroupId);

        return $this->streamReportPdf(
            request: $request,
            view: 'competition.matches.leaderboard-pdf',
            fileName: $this->pdfFileName('laporan-top-assist', $selectedAgeGroup),
            data: [
                'generatedAt' => now(),
                'selectedAgeGroup' => $selectedAgeGroup,
                'title' => 'Top Assist',
                'description' => 'Peringkat pemberi assist terbanyak dari report gol yang sudah tercatat.',
                'metricLabel' => 'Assist',
                'emptyMessage' => 'Belum ada data top assist untuk filter ini.',
                'leaderboards' => $this->buildGoalLeaderboard(
                    user: $user,
                    clubIds: $clubIds,
                    type: 'assist',
                    ageGroupId: $ageGroupId,
                ),
            ],
        );
    }

    public function brackets(Request $request): View
    {
        return view('competition.matches.brackets', $this->bracketReportViewData($request));
    }

    public function bracketsPrint(Request $request): View
    {
        return view('competition.matches.brackets-print', $this->bracketReportViewData($request));
    }

    private function bracketReportViewData(Request $request): array
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $highlightedClubIds = $user->isAdmin() ? collect() : $clubIds->map(fn ($id) => (int) $id)->values();
        $visibleBracketAgeGroupIds = $user->isAdmin() ? collect() : $this->visibleBracketAgeGroupIds($clubIds);
        $requestedAgeGroupId = $request->integer('age_group_id') ?: null;
        $ageGroupId = $user->isAdmin() || ! $requestedAgeGroupId || $visibleBracketAgeGroupIds->contains($requestedAgeGroupId)
            ? $requestedAgeGroupId
            : null;
        $allBrackets = $this->buildKnockoutBrackets(
            user: $user,
            clubIds: $clubIds,
            restrictToVisibleClubs: false,
        );
        if (! $user->isAdmin()) {
            $allBrackets = $allBrackets
                ->filter(fn (array $bracket) => $visibleBracketAgeGroupIds->contains((int) ($bracket['age_group']?->id ?? 0)))
                ->values();
        }

        $brackets = $ageGroupId
            ? $allBrackets->filter(fn (array $bracket) => (int) ($bracket['age_group']?->id ?? 0) === $ageGroupId)->values()
            : $allBrackets;

        return [
            'title' => 'Bagan Knockout',
            'ageGroups' => $user->isAdmin()
                ? AgeGroup::competition()->get()
                : AgeGroup::competition()->whereIn('id', $visibleBracketAgeGroupIds)->get(),
            'ageGroupSummaries' => $allBrackets->map(fn (array $bracket) => [
                'id' => $bracket['age_group']?->id,
                'name' => $bracket['age_group']?->name ?: '-',
                'total_matches' => collect($bracket['rounds'] ?? [])->sum(fn (array $round) => count($round['matches'] ?? [])),
            ])->values(),
            'selectedAgeGroupId' => $ageGroupId,
            'selectedAgeGroup' => $this->selectedReportAgeGroup($ageGroupId),
            'brackets' => $brackets,
            'bracketryBrackets' => $this->buildReportBracketryBrackets($brackets, $highlightedClubIds),
        ];
    }

    public function reports(Request $request): View
    {
        return view('competition.matches.reports', [
            'title' => 'Rekap Laporan',
            ...$this->reportPageData($request),
        ]);
    }

    public function reportsPdf(Request $request)
    {
        $reportData = $this->reportPageData($request);

        return $this->streamReportPdf(
            request: $request,
            view: 'competition.matches.report-pdf',
            fileName: $this->pdfFileName('laporan-pertandingan', $reportData['selectedAgeGroup']),
            data: $reportData,
        );
    }

    public function updateResult(Request $request, MatchSchedule $match)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonMatch($match);

        $validated = $request->validate([
            'score_club_a' => ['nullable', 'integer', 'min:0', 'max:99', 'required_with:score_club_b,is_finished'],
            'score_club_b' => ['nullable', 'integer', 'min:0', 'max:99', 'required_with:score_club_a,is_finished'],
            'is_finished' => ['nullable', 'boolean'],
            'goal_events' => ['nullable', 'array'],
            'goal_events.*.club_id' => ['nullable', 'integer', 'exists:clubs,id'],
            'goal_events.*.player_id' => ['nullable', 'integer', 'exists:players,id'],
            'goal_events.*.assist_player_id' => ['nullable', 'integer', 'exists:players,id'],
        ]);

        $isFinished = (bool) ($validated['is_finished'] ?? false);
        $scoreA = $validated['score_club_a'] ?? null;
        $scoreB = $validated['score_club_b'] ?? null;
        $goalEvents = $this->normalizeGoalEvents($validated['goal_events'] ?? []);

        if ($isFinished && ($scoreA === null || $scoreB === null)) {
            throw ValidationException::withMessages([
                'score_club_a' => 'Skor kedua tim wajib diisi jika pertandingan ditandai selesai.',
            ]);
        }

        $this->validateGoalEvents($match, $goalEvents, $scoreA, $scoreB, $isFinished);

        DB::transaction(function () use ($match, $scoreA, $scoreB, $isFinished, $goalEvents) {
            $match->update([
                'score_club_a' => $scoreA,
                'score_club_b' => $scoreB,
                'is_finished' => $isFinished,
            ]);

            $match->goalEvents()->delete();

            if (! empty($goalEvents)) {
                $match->goalEvents()->createMany($goalEvents);
            }
        });

        return redirect()
            ->route('match-results.index', $request->only(['age_group_id', 'competition_format']))
            ->with('status', 'Hasil pertandingan berhasil diperbarui.');
    }

    private function validatedData(Request $request): array
    {
        $activeSeason = $this->seasonContext->requireActive();
        $validated = $request->validate([
            'age_group_id' => ['required', AgeGroup::competitionExistsRule()],
            'competition_format' => ['required', 'in:league,knockout'],
            'round_label' => ['nullable', 'string', 'max:255', 'required_if:competition_format,knockout'],
            'round_order' => ['nullable', 'integer', 'min:1', 'max:999', 'required_if:competition_format,knockout'],
            'bracket_slot' => ['nullable', 'integer', 'min:1', 'max:999', 'required_if:competition_format,knockout'],
            'source_match_a_id' => ['nullable', 'integer', 'exists:match_schedules,id'],
            'source_match_b_id' => ['nullable', 'integer', 'exists:match_schedules,id'],
            'club_a_id' => ['required', 'exists:clubs,id', 'different:club_b_id'],
            'club_b_id' => ['required', 'exists:clubs,id', 'different:club_a_id'],
            'match_day' => ['required', 'string', 'max:255'],
            'venue' => ['required', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'kickoff_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $matchId = $request->route('match')?->id;
        $clubIds = [(int) $validated['club_a_id'], (int) $validated['club_b_id']];
        sort($clubIds);

        $baseQuery = MatchSchedule::query()->forActiveSeason()
            ->when($matchId, fn ($query) => $query->whereKeyNot($matchId));

        $pairConflict = (clone $baseQuery)
            ->where('age_group_id', $validated['age_group_id'])
            ->whereDate('match_date', $validated['match_date'])
            ->where('kickoff_time', $validated['kickoff_time'])
            ->where(function ($query) use ($clubIds) {
                $query
                    ->where(function ($inner) use ($clubIds) {
                        $inner->where('club_a_id', $clubIds[0])->where('club_b_id', $clubIds[1]);
                    })
                    ->orWhere(function ($inner) use ($clubIds) {
                        $inner->where('club_a_id', $clubIds[1])->where('club_b_id', $clubIds[0]);
                    });
            })
            ->exists();

        if ($pairConflict) {
            throw ValidationException::withMessages([
                'club_a_id' => 'Jadwal untuk pasangan klub, kelompok usia, tanggal, dan jam tersebut sudah ada.',
            ]);
        }

        $slotConflict = (clone $baseQuery)
            ->whereDate('match_date', $validated['match_date'])
            ->where('kickoff_time', $validated['kickoff_time'])
            ->where(function ($query) use ($clubIds) {
                $query
                    ->whereIn('club_a_id', $clubIds)
                    ->orWhereIn('club_b_id', $clubIds);
            })
            ->exists();

        if ($slotConflict) {
            throw ValidationException::withMessages([
                'kickoff_time' => 'Salah satu klub sudah memiliki jadwal lain pada tanggal dan jam tersebut.',
            ]);
        }

        if ($validated['competition_format'] === MatchSchedule::FORMAT_LEAGUE) {
            $validated['round_label'] = null;
            $validated['round_order'] = null;
            $validated['bracket_slot'] = null;
            $validated['source_match_a_id'] = null;
            $validated['source_match_b_id'] = null;
        }

        $this->validateKnockoutClubEligibility(
            validated: $validated,
            currentMatch: $request->route('match'),
        );
        $this->validateKnockoutRoundDependencies(
            currentMatch: $request->route('match'),
            ageGroupId: (int) $validated['age_group_id'],
            proposedRoundOrder: (int) ($validated['round_order'] ?? 0),
            sourceMatchAId: isset($validated['source_match_a_id']) ? (int) $validated['source_match_a_id'] : null,
            sourceMatchBId: isset($validated['source_match_b_id']) ? (int) $validated['source_match_b_id'] : null,
        );
        $this->validateKnockoutBracketPlacement(
            validated: $validated,
            currentMatchId: $matchId,
        );

        $validated['season_id'] = $activeSeason->id;
        $validated['club_a_season_id'] = $this->seasonSnapshotService->seasonClubIdForClub((int) $validated['club_a_id'], $activeSeason->id);
        $validated['club_b_season_id'] = $this->seasonSnapshotService->seasonClubIdForClub((int) $validated['club_b_id'], $activeSeason->id);

        return $validated;
    }

    private function formatOptions(): array
    {
        return [
            MatchSchedule::FORMAT_LEAGUE => 'Liga',
            MatchSchedule::FORMAT_KNOCKOUT => 'Knockout',
        ];
    }

    private function buildKnockoutSourceOptionsByAgeGroup(?int $ignoreMatchId = null): array
    {
        return MatchSchedule::query()->forActiveSeason()
            ->with([
                'clubA:id,name,short_name',
                'clubB:id,name,short_name',
            ])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->when($ignoreMatchId, fn ($query) => $query->whereKeyNot($ignoreMatchId))
            ->orderBy('age_group_id')
            ->orderBy('round_order')
            ->orderBy('bracket_slot')
            ->get(['id', 'age_group_id', 'round_label', 'round_order', 'bracket_slot', 'match_day', 'club_a_id', 'club_b_id'])
            ->groupBy('age_group_id')
            ->mapWithKeys(function (Collection $matches, int $ageGroupId) {
                return [
                    (string) $ageGroupId => $matches->map(function (MatchSchedule $match) {
                        $clubALabel = $match->clubA?->short_name ?: $match->clubA?->name ?: 'Klub A';
                        $clubBLabel = $match->clubB?->short_name ?: $match->clubB?->name ?: 'Klub B';

                        return [
                            'id' => $match->id,
                            'round_order' => (int) ($match->round_order ?: 1),
                            'label' => collect([
                                $match->round_display_label,
                                'Slot '.($match->bracket_slot ?: '-'),
                                $match->match_day,
                                $clubALabel.' vs '.$clubBLabel,
                            ])->filter()->implode(' • '),
                        ];
                    })->all(),
                ];
            })
            ->all();
    }

    private function buildKnockoutAdminBoards(?int $ageGroupId = null): Collection
    {
        $ageGroups = AgeGroup::competition()
            ->when($ageGroupId, fn ($query) => $query->whereKey($ageGroupId))
            ->orderBy('name')
            ->get();

        $matchesByAgeGroup = MatchSchedule::query()->forActiveSeason()
            ->with([
                'clubA',
                'clubB',
                'lineupLists:id,match_id',
                'ageGroup',
                'sourceMatchA:id,round_label,round_order,bracket_slot,match_day,club_a_id,club_b_id',
                'sourceMatchB:id,round_label,round_order,bracket_slot,match_day,club_a_id,club_b_id',
            ])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->when($ageGroupId, fn ($query) => $query->where('age_group_id', $ageGroupId))
            ->orderBy('age_group_id')
            ->orderBy('round_order')
            ->orderBy('bracket_slot')
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->get()
            ->groupBy('age_group_id');

        return $ageGroups->map(function (AgeGroup $ageGroup) use ($matchesByAgeGroup) {
            $ageGroupMatches = $matchesByAgeGroup->get($ageGroup->id, collect());
            $winningPathIds = $this->collectKnockoutWinningPathMatchIds($ageGroupMatches);
            $feedingMatchIds = $ageGroupMatches
                ->flatMap(fn (MatchSchedule $match) => [
                    $match->source_match_a_id ? (int) $match->source_match_a_id : null,
                    $match->source_match_b_id ? (int) $match->source_match_b_id : null,
                ])
                ->filter()
                ->values();

            $existingRounds = $ageGroupMatches
                ->groupBy(fn (MatchSchedule $match) => (string) ($match->round_order ?: 1))
                ->map(fn (Collection $roundMatches) => [
                    'round_order' => (int) ($roundMatches->first()?->round_order ?: 1),
                    'round_label' => $roundMatches->first()?->round_display_label ?: 'Babak '.((int) ($roundMatches->first()?->round_order ?: 1)),
                    'is_new_round' => false,
                ])
                ->sortBy('round_order')
                ->values();

            if ($existingRounds->isEmpty()) {
                $roundDefinitions = collect([
                    [
                        'round_order' => 1,
                        'round_label' => 'Babak 1',
                        'is_new_round' => true,
                    ],
                ]);
            } else {
                $highestRoundOrder = (int) $existingRounds->max('round_order');

                $roundDefinitions = $existingRounds->values();
                $roundDefinitions->push([
                    'round_order' => $highestRoundOrder + 1,
                    'round_label' => 'Babak '.($highestRoundOrder + 1),
                    'is_new_round' => true,
                ]);
            }

            $lastRoundOrder = (int) collect($roundDefinitions)->max('round_order');

            return [
                'age_group' => $ageGroup,
                'total_matches' => $ageGroupMatches->count(),
                'rounds' => $roundDefinitions->map(function (array $round) use ($ageGroup, $ageGroupMatches, $feedingMatchIds, $winningPathIds, $lastRoundOrder) {
                    $matchesBySlot = $ageGroupMatches
                        ->where('round_order', $round['round_order'])
                        ->sortBy('bracket_slot')
                        ->keyBy(fn (MatchSchedule $match) => (int) ($match->bracket_slot ?: 1));

                    $highestSlot = (int) ($matchesBySlot->keys()->max() ?: 0);
                    $slotLimit = max($highestSlot + 1, 1);

                    return [
                        'round_label' => $round['round_label'],
                        'round_order' => $round['round_order'],
                        'is_new_round' => (bool) ($round['is_new_round'] ?? false),
                        'round_display_label' => (bool) ($round['is_new_round'] ?? false)
                            ? 'Babak Baru'
                            : ($round['round_label'] ?: 'Babak '.$round['round_order']),
                        'slots' => collect(range(1, $slotLimit))->map(function (int $slot) use ($matchesBySlot, $ageGroup, $round, $feedingMatchIds, $winningPathIds, $lastRoundOrder) {
                            $match = $matchesBySlot->get($slot);
                            $slotTone = 'empty';

                            if ($match) {
                                $slotTone = $winningPathIds->contains($match->id)
                                    ? 'winner'
                                    : 'occupied';
                            }

                            return [
                                'slot' => $slot,
                                'match' => $match,
                                'has_connector' => (int) $round['round_order'] < $lastRoundOrder,
                                'connector_tone' => $match && $feedingMatchIds->contains($match->id)
                                    ? $slotTone
                                    : 'empty',
                                'create_url' => route('matches.create', [
                                    'redirect_route' => 'matches.knockout.index',
                                    'competition_format' => MatchSchedule::FORMAT_KNOCKOUT,
                                    'age_group_id' => $ageGroup->id,
                                    'round_label' => $round['round_label'],
                                    'round_order' => $round['round_order'],
                                    'bracket_slot' => $slot,
                                ]),
                            ];
                        })->all(),
                    ];
                })->all(),
            ];
        });
    }

    private function buildKnockoutAgeGroupSummaries(): Collection
    {
        $matchCountsByAgeGroup = MatchSchedule::query()->forActiveSeason()
            ->select('age_group_id', DB::raw('COUNT(*) as total_matches'))
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->groupBy('age_group_id')
            ->pluck('total_matches', 'age_group_id');

        return AgeGroup::competition()
            ->orderBy('name')
            ->get()
            ->map(function (AgeGroup $ageGroup) use ($matchCountsByAgeGroup) {
                $totalMatches = (int) ($matchCountsByAgeGroup[$ageGroup->id] ?? 0);

                return [
                    'id' => $ageGroup->id,
                    'name' => $ageGroup->name,
                    'total_matches' => $totalMatches,
                    'has_matches' => $totalMatches > 0,
                ];
            });
    }

    private function collectKnockoutWinningPathMatchIds(Collection $ageGroupMatches): Collection
    {
        $matchesById = $ageGroupMatches->keyBy('id');
        $referencedSourceIds = $ageGroupMatches
            ->flatMap(fn (MatchSchedule $match) => [
                $match->source_match_a_id ? (int) $match->source_match_a_id : null,
                $match->source_match_b_id ? (int) $match->source_match_b_id : null,
            ])
            ->filter()
            ->values();

        $terminalMatches = $ageGroupMatches
            ->filter(fn (MatchSchedule $match) => ! $referencedSourceIds->contains($match->id))
            ->sortByDesc('round_order')
            ->values();

        $winningPathIds = [];

        $walk = function (?MatchSchedule $match) use (&$walk, &$winningPathIds, $matchesById) {
            if (! $match || in_array($match->id, $winningPathIds, true)) {
                return;
            }

            $winningPathIds[] = $match->id;

            if (! $match->winner_club_id) {
                return;
            }

            if ((int) $match->winner_club_id === (int) $match->club_a_id) {
                $walk($matchesById->get((int) $match->source_match_a_id));
            }

            if ((int) $match->winner_club_id === (int) $match->club_b_id) {
                $walk($matchesById->get((int) $match->source_match_b_id));
            }
        };

        $terminalMatches->each(fn (MatchSchedule $match) => $walk($match));

        return collect($winningPathIds);
    }

    private function validateKnockoutRoundDependencies(
        ?MatchSchedule $currentMatch,
        int $ageGroupId,
        int $proposedRoundOrder,
        ?int $sourceMatchAId = null,
        ?int $sourceMatchBId = null,
        array $roundOrderOverrides = [],
    ): void {
        if ($proposedRoundOrder < 1) {
            return;
        }

        $messages = [];
        $sourceSelections = collect([
            'source_match_a_id' => $sourceMatchAId,
            'source_match_b_id' => $sourceMatchBId,
        ])->filter();

        if ($sourceSelections->isNotEmpty()) {
            if ($sourceSelections->values()->unique()->count() !== $sourceSelections->count()) {
                $messages['source_match_b_id'] = 'Sumber Tim A dan Tim B harus berasal dari pertandingan yang berbeda.';
            }

            $sourceMatches = MatchSchedule::query()->forActiveSeason()
                ->whereIn('id', $sourceSelections->values())
                ->get(['id', 'age_group_id', 'competition_format', 'round_order'])
                ->keyBy('id');

            foreach ($sourceSelections as $field => $sourceMatchId) {
                $sourceMatch = $sourceMatches->get((int) $sourceMatchId);

                if (! $sourceMatch) {
                    $messages[$field] = 'Pertandingan sumber tidak ditemukan.';
                    continue;
                }

                if ((int) $sourceMatch->age_group_id !== $ageGroupId) {
                    $messages[$field] = 'Pertandingan sumber harus berasal dari kelompok usia yang sama.';
                    continue;
                }

                if ($sourceMatch->competition_format !== MatchSchedule::FORMAT_KNOCKOUT) {
                    $messages[$field] = 'Pertandingan sumber hanya bisa diambil dari bracket knockout.';
                    continue;
                }

                if ($currentMatch && (int) $sourceMatch->id === (int) $currentMatch->id) {
                    $messages[$field] = 'Pertandingan tidak bisa memakai dirinya sendiri sebagai sumber lawan.';
                    continue;
                }

                $sourceRoundOrder = $roundOrderOverrides[(int) $sourceMatch->id] ?? (int) ($sourceMatch->round_order ?: 1);
                if ($sourceRoundOrder >= $proposedRoundOrder) {
                    $messages[$field] = 'Pertandingan sumber harus berasal dari babak yang lebih awal.';
                }
            }
        }

        if ($currentMatch) {
            $downstreamMatches = MatchSchedule::query()->forActiveSeason()
                ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
                ->where('age_group_id', $ageGroupId)
                ->where(function ($query) use ($currentMatch) {
                    $query->where('source_match_a_id', $currentMatch->id)
                        ->orWhere('source_match_b_id', $currentMatch->id);
                })
                ->get(['id', 'round_order', 'round_label']);

            foreach ($downstreamMatches as $downstreamMatch) {
                $downstreamRoundOrder = $roundOrderOverrides[(int) $downstreamMatch->id] ?? (int) ($downstreamMatch->round_order ?: 1);
                if ($downstreamRoundOrder <= $proposedRoundOrder) {
                    $messages['round_order'] = 'Urutan babak tidak valid karena pertandingan ini sudah menjadi sumber untuk '.($downstreamMatch->round_label ?: 'babak '.$downstreamRoundOrder).'.';
                    break;
                }
            }
        }

        if (! empty($messages)) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function matchIndexMeta(?string $forcedCompetitionFormat): array
    {
        return match ($forcedCompetitionFormat) {
            MatchSchedule::FORMAT_LEAGUE => [
                'route' => 'matches.league.index',
                'title' => 'Jadwal Match Liga',
                'heading' => 'Jadwal Match Liga',
                'description' => 'Kelola jadwal fase liga secara terpisah agar penyusunan pertandingan lebih rapi.',
            ],
            MatchSchedule::FORMAT_KNOCKOUT => [
                'route' => 'matches.knockout.index',
                'title' => 'Jadwal Match Knockout',
                'heading' => 'Jadwal Match Knockout',
                'description' => 'Kelola jadwal fase knockout secara terpisah untuk memudahkan pengaturan babak gugur.',
            ],
            default => [
                'route' => 'matches.league.index',
                'title' => 'Jadwal Match Liga',
                'heading' => 'Jadwal Match Liga',
                'description' => 'Kelola jadwal fase liga secara terpisah agar penyusunan pertandingan lebih rapi.',
            ],
        };
    }

    private function normalizeCompetitionFormat(mixed $competitionFormat): ?string
    {
        return match ($competitionFormat) {
            MatchSchedule::FORMAT_LEAGUE, MatchSchedule::FORMAT_KNOCKOUT => $competitionFormat,
            default => null,
        };
    }

    private function resolveMatchIndexRouteName(Request $request, ?string $fallbackCompetitionFormat = null): string
    {
        $redirectRouteName = $request->string('redirect_route')->value();
        $allowedRouteNames = ['matches.league.index', 'matches.knockout.index'];

        if (in_array($redirectRouteName, $allowedRouteNames, true)) {
            return $redirectRouteName;
        }

        return match ($fallbackCompetitionFormat) {
            MatchSchedule::FORMAT_LEAGUE => 'matches.league.index',
            MatchSchedule::FORMAT_KNOCKOUT => 'matches.knockout.index',
            default => 'matches.league.index',
        };
    }

    private function matchIndexQueryParameters(Request $request, string $routeName): array
    {
        $parameters = collect($request->only(['club_id', 'age_group_id', 'lineup_status', 'sort', 'direction']))
            ->filter(fn ($value) => filled($value))
            ->all();

        return $parameters;
    }

    private function resolveSort(Request $request, string $defaultSort, string $defaultDirection, array $allowedSorts): array
    {
        $sort = $request->string('sort')->value() ?: $defaultSort;
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $allowedSorts, true)) {
            return [$defaultSort, $defaultDirection];
        }

        if (! $request->filled('sort')) {
            $direction = $defaultDirection;
        }

        return [$sort, $direction];
    }

    private function applyMatchSort($query, string $sort, string $direction): void
    {
        match ($sort) {
            'matchup' => $query
                ->orderBy(
                    Club::query()->select('name')->whereColumn('clubs.id', 'match_schedules.club_a_id'),
                    $direction
                )
                ->orderBy(
                    Club::query()->select('name')->whereColumn('clubs.id', 'match_schedules.club_b_id'),
                    $direction
                ),
            'age_group' => $query->orderBy(
                AgeGroup::query()->select('name')->whereColumn('age_groups.id', 'match_schedules.age_group_id'),
                $direction
            ),
            'round_order' => $query
                ->orderByRaw('COALESCE(round_order, 9999) '.$direction)
                ->orderBy('bracket_slot', $direction),
            'match_date' => $query
                ->orderBy('match_date', $direction)
                ->orderBy('kickoff_time', $direction),
            'is_finished' => $query
                ->orderBy('is_finished', $direction)
                ->orderBy('match_date', 'desc')
                ->orderBy('kickoff_time', 'desc'),
            default => $query->orderBy($sort, $direction),
        };
    }

    private function visibleClubIds($user): Collection
    {
        return $user->isAdmin()
            ? Club::query()->pluck('id')
            : $user->clubs()->pluck('id');
    }

    private function visibleBracketAgeGroupIds(Collection $clubIds): Collection
    {
        if ($clubIds->isEmpty()) {
            return collect();
        }

        return MatchSchedule::query()->forActiveSeason()
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->where(function ($query) use ($clubIds) {
                $query->whereIn('club_a_id', $clubIds)
                    ->orWhereIn('club_b_id', $clubIds);
            })
            ->orderBy('age_group_id')
            ->pluck('age_group_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }

    private function selectedReportAgeGroup(?int $ageGroupId): ?AgeGroup
    {
        if (! $ageGroupId) {
            return null;
        }

        return AgeGroup::competition()->find($ageGroupId);
    }

    private function pdfFileName(string $baseName, ?AgeGroup $selectedAgeGroup = null): string
    {
        $suffix = $selectedAgeGroup?->code
            ? '-'.strtolower($selectedAgeGroup->code)
            : '';

        return $baseName.$suffix.'.pdf';
    }

    private function streamReportPdf(
        Request $request,
        string $view,
        string $fileName,
        array $data,
        string $paper = 'a4',
        string $orientation = 'portrait',
        array $options = []
    ) {
        $pdf = app(HtmlPdfRenderer::class)->renderView($view, [
            'generatedAt' => now(),
            ...$data,
        ], $paper, $orientation, $options);

        $response = response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="'.$fileName.'"',
        ]);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }

    private function reportPageData(Request $request): array
    {
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;
        $selectedAgeGroup = $this->selectedReportAgeGroup($ageGroupId);

        return [
            'generatedAt' => now(),
            'ageGroups' => AgeGroup::competition()->get(),
            'selectedAgeGroup' => $selectedAgeGroup,
            'reportSummary' => $this->buildReportSummary(
                user: $user,
                clubIds: $clubIds,
                ageGroupId: $ageGroupId,
            ),
            'topScorers' => $this->buildGoalLeaderboard(
                user: $user,
                clubIds: $clubIds,
                type: 'scorer',
                ageGroupId: $ageGroupId,
            ),
            'topAssists' => $this->buildGoalLeaderboard(
                user: $user,
                clubIds: $clubIds,
                type: 'assist',
                ageGroupId: $ageGroupId,
            ),
            'standings' => $this->buildLeagueStandings(
                user: $user,
                clubIds: $clubIds,
                ageGroupId: $ageGroupId,
            ),
        ];
    }

    private function buildReportSummary($user, Collection $clubIds, ?int $ageGroupId = null): array
    {
        $matchQuery = MatchSchedule::query()->forActiveSeason()
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->when($ageGroupId, fn ($query) => $query->where('age_group_id', $ageGroupId));

        if (! $user->isAdmin()) {
            $matchQuery->where(function ($query) use ($clubIds) {
                $query->whereIn('club_a_id', $clubIds)
                    ->orWhereIn('club_b_id', $clubIds);
            });
        }

        $matches = $matchQuery->get(['id', 'age_group_id', 'score_club_a', 'score_club_b']);
        $assistQuery = MatchGoal::query()->forActiveSeason()
            ->join('match_schedules', 'match_schedules.id', '=', 'match_goals.match_id')
            ->where('match_schedules.is_finished', true)
            ->whereNotNull('match_goals.assist_player_id')
            ->when($ageGroupId, fn ($query) => $query->where('match_schedules.age_group_id', $ageGroupId));

        if (! $user->isAdmin()) {
            $assistQuery->where(function ($query) use ($clubIds) {
                $query->whereIn('match_schedules.club_a_id', $clubIds)
                    ->orWhereIn('match_schedules.club_b_id', $clubIds);
            });
        }

        return [
            [
                'label' => 'Laga Selesai',
                'value' => $matches->count(),
                'hint' => 'Pertandingan yang hasil skornya sudah dikunci.',
            ],
            [
                'label' => 'Total Gol',
                'value' => $matches->sum(fn (MatchSchedule $match) => (int) $match->score_club_a + (int) $match->score_club_b),
                'hint' => 'Akumulasi gol dari semua pertandingan selesai.',
            ],
            [
                'label' => 'Assist Tercatat',
                'value' => $assistQuery->count(),
                'hint' => 'Gol yang sudah memiliki pemberi assist.',
            ],
            [
                'label' => 'Kelompok Usia Aktif',
                'value' => $matches->pluck('age_group_id')->filter()->unique()->count(),
                'hint' => 'Jumlah kelompok usia yang sudah punya hasil.',
            ],
        ];
    }

    private function buildGoalLeaderboard($user, Collection $clubIds, string $type, ?int $ageGroupId = null): Collection
    {
        $relationColumn = $type === 'assist' ? 'assist_player_id' : 'player_id';
        $ageGroups = AgeGroup::competition()->get()->keyBy('id');
        $rows = MatchGoal::query()->forActiveSeason()
            ->select([
                'match_schedules.age_group_id',
                'players.id as player_id',
                'players.name as player_name',
                'clubs.name as club_name',
                'clubs.short_name as club_short_name',
                DB::raw('COUNT(match_goals.id) as total'),
            ])
            ->join('match_schedules', 'match_schedules.id', '=', 'match_goals.match_id')
            ->join('players', 'players.id', '=', "match_goals.{$relationColumn}")
            ->leftJoin('clubs', 'clubs.id', '=', 'players.club_id')
            ->where('match_schedules.is_finished', true)
            ->whereNotNull("match_goals.{$relationColumn}")
            ->when($ageGroupId, fn ($query) => $query->where('match_schedules.age_group_id', $ageGroupId));

        if (! $user->isAdmin()) {
            $rows->where(function ($query) use ($clubIds) {
                $query->whereIn('match_schedules.club_a_id', $clubIds)
                    ->orWhereIn('match_schedules.club_b_id', $clubIds);
            });
        }

        return $rows
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

    private function buildKnockoutBrackets($user, Collection $clubIds, ?int $ageGroupId = null, ?string $competitionFormat = null, bool $restrictToVisibleClubs = true): Collection
    {
        if ($competitionFormat === MatchSchedule::FORMAT_LEAGUE) {
            return collect();
        }

        $query = MatchSchedule::query()->forActiveSeason()
            ->with(['ageGroup', 'clubA', 'clubB', 'goalEvents.scorer', 'goalEvents.assistPlayer'])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->when($ageGroupId, fn ($builder) => $builder->where('age_group_id', $ageGroupId));

        if (! $user->isAdmin() && $restrictToVisibleClubs) {
            $query->where(function ($builder) use ($clubIds) {
                $builder->whereIn('club_a_id', $clubIds)
                    ->orWhereIn('club_b_id', $clubIds);
            });
        }

        return $query
            ->orderBy('age_group_id')
            ->orderBy('round_order')
            ->orderBy('bracket_slot')
            ->orderBy('match_date')
            ->get()
            ->groupBy('age_group_id')
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

    private function buildReportBracketryBrackets(Collection $brackets, Collection $highlightedClubIds): Collection
    {
        $highlightedLookup = $highlightedClubIds
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->flip();

        return $brackets->map(function (array $bracket) use ($highlightedLookup): array {
            $allRounds = collect($bracket['rounds'])->values();
            $displayRounds = $allRounds;

            $contestants = [];
            $matches = [];

            foreach ($displayRounds as $roundIndex => $round) {
                foreach ($round['matches'] as $matchIndex => $match) {
                    $homeKey = 'club-'.$match->club_a_id;
                    $awayKey = 'club-'.$match->club_b_id;

                    $contestants[$homeKey] = [
                        'players' => [[
                            'title' => $match->clubA?->name ?: $match->clubA?->short_name ?: 'Klub A',
                        ]],
                        'isHighlighted' => $highlightedLookup->has((int) $match->club_a_id),
                    ];

                    $contestants[$awayKey] = [
                        'players' => [[
                            'title' => $match->clubB?->name ?: $match->clubB?->short_name ?: 'Klub B',
                        ]],
                        'isHighlighted' => $highlightedLookup->has((int) $match->club_b_id),
                    ];

                    $matches[] = [
                        'roundIndex' => $roundIndex,
                        'order' => $matchIndex,
                        'matchStatus' => filled($match->match_date)
                            ? trim((optional($match->match_date)->format('d M Y') ?: '').' · '.($match->kickoff_time?->format('H:i') ?: '--').' WIB', ' ·')
                            : ($match->is_finished ? 'FT' : 'SCHEDULED'),
                        'detail' => [
                            'round_label' => $round['label'],
                        ],
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

            return [
                'age_group' => $bracket['age_group'],
                'match_count' => count($matches),
                'layout' => [
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
        })->values();
    }

    private function knockoutEliminatedClubIdsByAgeGroup(?int $ignoreMatchId = null): array
    {
        return MatchSchedule::query()->forActiveSeason()
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->when($ignoreMatchId, fn ($query) => $query->whereKeyNot($ignoreMatchId))
            ->get(['id', 'age_group_id', 'club_a_id', 'club_b_id', 'score_club_a', 'score_club_b'])
            ->groupBy('age_group_id')
            ->map(function (Collection $matches) {
                return $matches
                    ->map(function (MatchSchedule $match) {
                        if ($match->score_club_a === $match->score_club_b) {
                            return null;
                        }

                        return $match->score_club_a > $match->score_club_b
                            ? (int) $match->club_b_id
                            : (int) $match->club_a_id;
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            })
            ->toArray();
    }

    private function validateKnockoutClubEligibility(array $validated, ?MatchSchedule $currentMatch = null): void
    {
        if (($validated['competition_format'] ?? null) !== MatchSchedule::FORMAT_KNOCKOUT) {
            return;
        }

        $eliminatedClubIds = collect($this->knockoutEliminatedClubIdsByAgeGroup($currentMatch?->id)[$validated['age_group_id']] ?? []);
        if ($eliminatedClubIds->isEmpty()) {
            return;
        }

        $originalClubIds = collect([
            $currentMatch?->club_a_id ? (int) $currentMatch->club_a_id : null,
            $currentMatch?->club_b_id ? (int) $currentMatch->club_b_id : null,
        ])->filter()->values();

        $blockedSelections = collect([
            'club_a_id' => (int) $validated['club_a_id'],
            'club_b_id' => (int) $validated['club_b_id'],
        ])->filter(function (int $clubId) use ($eliminatedClubIds, $originalClubIds) {
            return $eliminatedClubIds->contains($clubId) && ! $originalClubIds->contains($clubId);
        });

        if ($blockedSelections->isEmpty()) {
            return;
        }

        $clubNames = Club::query()
            ->whereIn('id', $blockedSelections->values())
            ->pluck('name', 'id');

        $messages = [];
        foreach ($blockedSelections as $field => $clubId) {
            $messages[$field] = ($clubNames[$clubId] ?? 'Klub yang dipilih').' sudah gugur di bracket knockout kelompok usia ini dan tidak bisa dijadwalkan lagi.';
        }

        throw ValidationException::withMessages($messages);
    }

    private function validateKnockoutBracketPlacement(array $validated, ?int $currentMatchId = null): void
    {
        if (($validated['competition_format'] ?? null) !== MatchSchedule::FORMAT_KNOCKOUT) {
            return;
        }

        $existingMatch = MatchSchedule::query()->forActiveSeason()
            ->with(['clubA:id,name', 'clubB:id,name'])
            ->when($currentMatchId, fn ($query) => $query->whereKeyNot($currentMatchId))
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->where('age_group_id', $validated['age_group_id'])
            ->where('round_order', $validated['round_order'])
            ->where('bracket_slot', $validated['bracket_slot'])
            ->first();

        if (! $existingMatch) {
            return;
        }

        $existingMatchLabel = trim(collect([
            $existingMatch->clubA?->name ?: 'Klub A',
            'vs',
            $existingMatch->clubB?->name ?: 'Klub B',
        ])->implode(' '));
        $existingMatchDate = optional($existingMatch->match_date)->format('d M Y');

        throw ValidationException::withMessages([
            'bracket_slot' => 'Slot bracket untuk '.$existingMatch->round_display_label.' sudah dipakai oleh '.$existingMatchLabel.($existingMatchDate ? ' pada '.$existingMatchDate : '').'. Gunakan slot lain agar bracket tidak bertumpuk.',
        ]);
    }

    private function buildLeagueStandings($user, Collection $clubIds, ?int $ageGroupId = null, ?string $competitionFormat = null): Collection
    {
        if ($competitionFormat === MatchSchedule::FORMAT_KNOCKOUT) {
            return collect();
        }

        $baseQuery = MatchSchedule::query()->forActiveSeason()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->when($ageGroupId, fn ($query) => $query->where('age_group_id', $ageGroupId));

        if (! $user->isAdmin()) {
            $allowedAgeGroupIds = MatchSchedule::query()->forActiveSeason()
                ->where(function ($query) use ($clubIds) {
                    $query->whereIn('club_a_id', $clubIds)
                        ->orWhereIn('club_b_id', $clubIds);
                })
                ->pluck('age_group_id')
                ->unique()
                ->values();

            $baseQuery->whereIn('age_group_id', $allowedAgeGroupIds);
        }

        return $baseQuery
            ->orderBy('age_group_id')
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->get()
            ->groupBy('age_group_id')
            ->map(function (Collection $matches, int $groupId) {
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

                        if (! $table->has($clubId)) {
                            $table->put($clubId, [
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
                        }

                        $row = $table->get($clubId);
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
                        }),
                ];
            })
            ->values();
    }

    private function normalizeGoalEvents(array $goalEvents): array
    {
        $activeSeasonId = $this->seasonContext->requireActive()->id;

        return collect($goalEvents)
            ->map(function ($goalEvent, int $index) use ($activeSeasonId) {
                $clubId = isset($goalEvent['club_id']) && $goalEvent['club_id'] !== '' ? (int) $goalEvent['club_id'] : null;
                $playerId = isset($goalEvent['player_id']) && $goalEvent['player_id'] !== '' ? (int) $goalEvent['player_id'] : null;
                $assistPlayerId = isset($goalEvent['assist_player_id']) && $goalEvent['assist_player_id'] !== '' ? (int) $goalEvent['assist_player_id'] : null;

                if (! $clubId && ! $playerId && ! $assistPlayerId) {
                    return null;
                }

                return [
                    'season_id' => $activeSeasonId,
                    'club_id' => $clubId,
                    'season_club_id' => $clubId ? $this->seasonSnapshotService->seasonClubIdForClub($clubId, $activeSeasonId) : null,
                    'player_id' => $playerId,
                    'season_player_id' => $playerId ? $this->seasonSnapshotService->seasonPlayerIdForPlayer($playerId, $activeSeasonId) : null,
                    'assist_player_id' => $assistPlayerId,
                    'assist_season_player_id' => $assistPlayerId ? $this->seasonSnapshotService->seasonPlayerIdForPlayer($assistPlayerId, $activeSeasonId) : null,
                    'display_order' => $index + 1,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function validateGoalEvents(MatchSchedule $match, array $goalEvents, ?int $scoreA, ?int $scoreB, bool $isFinished): void
    {
        if (! $isFinished) {
            if (! empty($goalEvents)) {
                throw ValidationException::withMessages([
                    'goal_events' => 'Report pencetak gol hanya bisa diisi jika pertandingan ditandai selesai.',
                ]);
            }

            return;
        }

        $expectedGoalCount = (int) ($scoreA ?? 0) + (int) ($scoreB ?? 0);
        if (count($goalEvents) !== $expectedGoalCount) {
            throw ValidationException::withMessages([
                'goal_events' => 'Jumlah report gol harus sama dengan total skor pertandingan.',
            ]);
        }

        $rosterByClub = $this->matchRosterPlayerIdsByClub($match);
        $clubGoalCounts = [
            (int) $match->club_a_id => 0,
            (int) $match->club_b_id => 0,
        ];

        foreach ($goalEvents as $index => $goalEvent) {
            $row = $index + 1;
            $clubId = (int) ($goalEvent['club_id'] ?? 0);
            $playerId = (int) ($goalEvent['player_id'] ?? 0);
            $assistPlayerId = (int) ($goalEvent['assist_player_id'] ?? 0);

            if (! $clubId || ! $playerId) {
                throw ValidationException::withMessages([
                    'goal_events' => "Baris gol ke-{$row} wajib memilih tim dan pencetak gol.",
                ]);
            }

            if (! in_array($clubId, [(int) $match->club_a_id, (int) $match->club_b_id], true)) {
                throw ValidationException::withMessages([
                    'goal_events' => "Tim pada baris gol ke-{$row} tidak sesuai dengan pertandingan ini.",
                ]);
            }

            $allowedPlayerIds = collect($rosterByClub[$clubId] ?? []);
            if (! $allowedPlayerIds->contains($playerId)) {
                throw ValidationException::withMessages([
                    'goal_events' => "Pencetak gol pada baris ke-{$row} harus berasal dari roster DSP pertandingan.",
                ]);
            }

            if ($assistPlayerId) {
                if (! $allowedPlayerIds->contains($assistPlayerId)) {
                    throw ValidationException::withMessages([
                        'goal_events' => "Assist pada baris ke-{$row} harus berasal dari roster DSP tim yang sama.",
                    ]);
                }

                if ($assistPlayerId === $playerId) {
                    throw ValidationException::withMessages([
                        'goal_events' => "Assist pada baris ke-{$row} tidak boleh sama dengan pencetak gol.",
                    ]);
                }
            }

            $clubGoalCounts[$clubId] = ($clubGoalCounts[$clubId] ?? 0) + 1;
        }

        if (($clubGoalCounts[(int) $match->club_a_id] ?? 0) !== (int) ($scoreA ?? 0)) {
            throw ValidationException::withMessages([
                'goal_events' => 'Jumlah pencetak gol untuk Tim A harus sama dengan skor Tim A.',
            ]);
        }

        if (($clubGoalCounts[(int) $match->club_b_id] ?? 0) !== (int) ($scoreB ?? 0)) {
            throw ValidationException::withMessages([
                'goal_events' => 'Jumlah pencetak gol untuk Tim B harus sama dengan skor Tim B.',
            ]);
        }
    }

    private function matchRosterPlayerIdsByClub(MatchSchedule $match): array
    {
        return $match->lineupLists()
            ->with(['players:id'])
            ->get()
            ->groupBy(fn (LineupList $lineup) => (int) $lineup->club_id)
            ->map(fn (Collection $lineups) => $lineups
                ->flatMap(fn (LineupList $lineup) => $lineup->players->pluck('id'))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values())
            ->toArray();
    }

    private function ensureActiveSeasonMatch(MatchSchedule $match): void
    {
        $activeSeasonId = $this->seasonContext->activeId();

        if ($match->season_id && $activeSeasonId && (int) $match->season_id !== (int) $activeSeasonId) {
            abort(403, 'Data pertandingan dari season yang sudah tidak aktif bersifat read-only.');
        }
    }

    private function ensureWritableSeasonContext(): void
    {
        if ($this->seasonContext->isViewingHistory()) {
            abort(403, 'Kamu sedang melihat histori season. Kembali ke season aktif untuk menambah atau mengubah data pertandingan.');
        }
    }
}

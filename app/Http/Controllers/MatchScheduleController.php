<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchScheduleController extends Controller
{
    public function index(Request $request)
    {
        [$sort, $direction] = $this->resolveSort(
            request: $request,
            defaultSort: 'match_date',
            defaultDirection: 'asc',
            allowedSorts: ['match_day', 'matchup', 'age_group', 'competition_format', 'round_order', 'venue', 'match_date', 'kickoff_time'],
        );

        $matches = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'lineupLists:id,match_id,club_id,verification_status'])
            ->when($request->input('club_id'), function ($query, $clubId) {
                $query->where(fn ($inner) => $inner
                    ->where('club_a_id', $clubId)
                    ->orWhere('club_b_id', $clubId));
            })
            ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($request->input('competition_format'), fn ($query, $format) => $query->where('competition_format', $format))
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
            'title' => 'Jadwal Pertandingan',
            'matches' => $matches,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
        ]);
    }

    public function create()
    {
        return view('competition.matches.create', [
            'title' => 'Tambah Jadwal Pertandingan',
            'matchSchedule' => new MatchSchedule,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
            'knockoutEliminatedClubIdsByAgeGroup' => $this->knockoutEliminatedClubIdsByAgeGroup(),
            'currentMatchClubIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        MatchSchedule::create($this->validatedData($request));

        return redirect()->route('matches.index')->with('status', 'Jadwal pertandingan berhasil ditambahkan.');
    }

    public function edit(MatchSchedule $match)
    {
        return view('competition.matches.edit', [
            'title' => 'Edit Jadwal Pertandingan',
            'matchSchedule' => $match,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::competition()->get(),
            'formatOptions' => $this->formatOptions(),
            'knockoutEliminatedClubIdsByAgeGroup' => $this->knockoutEliminatedClubIdsByAgeGroup($match->id),
            'currentMatchClubIds' => array_values(array_filter([(int) $match->club_a_id, (int) $match->club_b_id])),
        ]);
    }

    public function update(Request $request, MatchSchedule $match)
    {
        if ($match->lineupLists()->exists()) {
            throw ValidationException::withMessages([
                'match' => 'Jadwal ini sudah dipakai di DSP, jadi tidak bisa diubah. Buat jadwal baru atau hapus DSP yang terkait terlebih dahulu.',
            ]);
        }

        $match->update($this->validatedData($request));

        return redirect()->route('matches.index')->with('status', 'Jadwal pertandingan berhasil diperbarui.');
    }

    public function destroy(MatchSchedule $match)
    {
        if ($match->lineupLists()->exists()) {
            throw ValidationException::withMessages([
                'match' => 'Jadwal pertandingan yang sudah dipakai DSP tidak bisa dihapus.',
            ]);
        }

        $match->delete();

        return redirect()->route('matches.index')->with('status', 'Jadwal pertandingan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer', 'exists:match_schedules,id'],
        ]);

        $matches = MatchSchedule::query()
            ->withCount('lineupLists')
            ->whereIn('id', $validated['selected_ids'])
            ->get();

        $deletableIds = $matches
            ->where('lineup_lists_count', 0)
            ->pluck('id');

        $blockedCount = $matches->count() - $deletableIds->count();

        if ($deletableIds->isEmpty()) {
            return redirect()
                ->route('matches.index', $request->only(['club_id', 'age_group_id', 'lineup_status', 'competition_format', 'sort', 'direction']))
                ->withErrors([
                    'match' => 'Tidak ada jadwal yang bisa dihapus. Jadwal yang sudah dipakai DSP tidak bisa dihapus massal.',
                ]);
        }

        MatchSchedule::query()->whereIn('id', $deletableIds)->delete();

        $message = $deletableIds->count().' jadwal pertandingan berhasil dihapus.';

        if ($blockedCount > 0) {
            $message .= ' '.$blockedCount.' jadwal dilewati karena sudah dipakai DSP.';
        }

        return redirect()
            ->route('matches.index', $request->only(['club_id', 'age_group_id', 'lineup_status', 'competition_format', 'sort', 'direction']))
            ->with('status', $message);
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

        $matches = MatchSchedule::query()
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
        $user = $request->user();
        $clubIds = $this->visibleClubIds($user);
        $ageGroupId = $request->integer('age_group_id') ?: null;

        return view('competition.matches.brackets', [
            'title' => 'Bagan Knockout',
            'ageGroups' => AgeGroup::competition()->get(),
            'selectedAgeGroup' => $this->selectedReportAgeGroup($ageGroupId),
            'brackets' => $this->buildKnockoutBrackets(
                user: $user,
                clubIds: $clubIds,
                ageGroupId: $ageGroupId,
            ),
        ]);
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
        $validated = $request->validate([
            'age_group_id' => ['required', AgeGroup::competitionExistsRule()],
            'competition_format' => ['required', 'in:league,knockout'],
            'round_label' => ['nullable', 'string', 'max:255'],
            'round_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'bracket_slot' => ['nullable', 'integer', 'min:1', 'max:999'],
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

        $baseQuery = MatchSchedule::query()
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
        } else {
            $validated['round_label'] = filled($validated['round_label'] ?? null) ? $validated['round_label'] : 'Babak Knockout';
            $validated['round_order'] = $validated['round_order'] ?? 1;
            $validated['bracket_slot'] = $validated['bracket_slot'] ?? 1;
        }

        $this->validateKnockoutClubEligibility(
            validated: $validated,
            currentMatch: $request->route('match'),
        );

        return $validated;
    }

    private function formatOptions(): array
    {
        return [
            MatchSchedule::FORMAT_LEAGUE => 'Liga',
            MatchSchedule::FORMAT_KNOCKOUT => 'Knockout',
        ];
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

    private function streamReportPdf(Request $request, string $view, string $fileName, array $data)
    {
        $pdf = Pdf::loadView($view, [
            'generatedAt' => now(),
            ...$data,
        ])->setPaper('a4', 'portrait');

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
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
        $matchQuery = MatchSchedule::query()
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
        $assistQuery = MatchGoal::query()
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
        $rows = MatchGoal::query()
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
                                'club_name' => $row->club_short_name ?: $row->club_name ?: '-',
                                'total' => (int) $row->total,
                            ];
                        }),
                ];
            })
            ->values();
    }

    private function buildKnockoutBrackets($user, Collection $clubIds, ?int $ageGroupId = null, ?string $competitionFormat = null): Collection
    {
        if ($competitionFormat === MatchSchedule::FORMAT_LEAGUE) {
            return collect();
        }

        $query = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'goalEvents.scorer', 'goalEvents.assistPlayer'])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->when($ageGroupId, fn ($builder) => $builder->where('age_group_id', $ageGroupId));

        if (! $user->isAdmin()) {
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

    private function knockoutEliminatedClubIdsByAgeGroup(?int $ignoreMatchId = null): array
    {
        return MatchSchedule::query()
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

    private function buildLeagueStandings($user, Collection $clubIds, ?int $ageGroupId = null, ?string $competitionFormat = null): Collection
    {
        if ($competitionFormat === MatchSchedule::FORMAT_KNOCKOUT) {
            return collect();
        }

        $baseQuery = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->when($ageGroupId, fn ($query) => $query->where('age_group_id', $ageGroupId));

        if (! $user->isAdmin()) {
            $allowedAgeGroupIds = MatchSchedule::query()
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
        return collect($goalEvents)
            ->map(function ($goalEvent, int $index) {
                $clubId = isset($goalEvent['club_id']) && $goalEvent['club_id'] !== '' ? (int) $goalEvent['club_id'] : null;
                $playerId = isset($goalEvent['player_id']) && $goalEvent['player_id'] !== '' ? (int) $goalEvent['player_id'] : null;
                $assistPlayerId = isset($goalEvent['assist_player_id']) && $goalEvent['assist_player_id'] !== '' ? (int) $goalEvent['assist_player_id'] : null;

                if (! $clubId && ! $playerId && ! $assistPlayerId) {
                    return null;
                }

                return [
                    'club_id' => $clubId,
                    'player_id' => $playerId,
                    'assist_player_id' => $assistPlayerId,
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
}

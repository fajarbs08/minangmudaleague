<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\Player;
use App\Services\SeasonContext;
use App\Services\SeasonSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LineupListController extends Controller
{
    use HandlesVerificationWorkflow;

    public function __construct(
        private SeasonContext $seasonContext,
        private SeasonSnapshotService $seasonSnapshotService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = $request->string('sort')->value() ?: 'match_date';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['title', 'club', 'age_group', 'match_date', 'verification_status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'match_date';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        $lineupLists = LineupList::query()->forActiveSeason()
            ->with(['club', 'ageGroup', 'players', 'match.clubA', 'match.clubB', 'match.lineupLists:id,match_id,club_id'])
            ->whereIn('club_id', $clubs->pluck('id'))
            ->when($request->input('club_id'), fn ($query, $clubId) => $query->where('club_id', $clubId))
            ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->where('age_group_id', $ageGroupId))
            ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('coach_name', 'like', "%{$search}%")
                        ->orWhere('match_day', 'like', "%{$search}%");
                });
            });

        match ($sort) {
            'club' => $lineupLists->orderBy(
                Club::query()->select('name')->whereColumn('clubs.id', 'lineup_lists.club_id'),
                $direction
            ),
            'age_group' => $lineupLists->orderBy(
                AgeGroup::query()->select('name')->whereColumn('age_groups.id', 'lineup_lists.age_group_id'),
                $direction
            ),
            default => $lineupLists->orderBy($sort, $direction),
        };

        $lineupLists = $lineupLists
            ->paginate(10)
            ->withQueryString();

        return view('competition.lineups.index', [
            'title' => 'Daftar Susunan Pemain',
            'lineupLists' => $lineupLists,
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function bulkReview(Request $request)
    {
        $this->ensureWritableSeasonContext();
        $clubs = $this->availableClubs();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,revision,rejected,deleted'],
            'verification_notes' => ['nullable', 'string'],
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer'],
        ]);

        if (in_array($validated['status'], ['revision', 'rejected'], true)) {
            $request->validate([
                'verification_notes' => ['required', 'string'],
            ]);
        }

        $models = LineupList::query()->forActiveSeason()
            ->whereIn('club_id', $clubs->pluck('id'))
            ->whereKey($validated['selected_ids'])
            ->get();

        if ($validated['status'] !== 'deleted') {
            $models = $models->filter(fn (LineupList $lineupList) => $lineupList->canBeReviewedByAdmin())
                ->values();
        }

        if ($models->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_ids' => 'Tidak ada data yang bisa diproses dari pilihan tersebut.',
            ]);
        }

        if ($validated['status'] === 'deleted') {
            $count = $models->count();
            $models->each->delete();

            return redirect()->back()->with('status', $count.' data DSP berhasil dihapus.');
        }

        $models->each(function (LineupList $lineupList) use ($validated) {
            $lineupList->update([
                'verification_status' => $validated['status'],
                'verification_notes' => $validated['verification_notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()->back()->with('status', $models->count().' data DSP berhasil diperbarui.');
    }

    public function create()
    {
        $this->ensureWritableSeasonContext();

        $lineupList = new LineupList([
            'club_id' => request('club_id'),
            'age_group_id' => request('age_group_id'),
        ]);
        $lineupPlayers = $this->lineupPlayers();

        return view('competition.lineups.create', [
            'title' => 'Tambah DSP',
            'lineupList' => $lineupList,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::competition()->get(),
            'availableMatches' => $this->availableMatches(),
            'lineupPlayers' => $lineupPlayers,
            'blockedLineupPlayers' => $this->blockedLineupPlayers(),
            'selectedStarters' => [],
            'selectedSubstitutes' => [],
            'selectedStarterOrders' => [],
            'selectedSubstituteOrders' => [],
            'selectedStarterJerseys' => [],
            'selectedSubstituteJerseys' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureWritableSeasonContext();

        $data = $this->validatedData($request);
        $this->ensureClubAccess($data['club_id']);
        $this->ensureUniqueLineup($data);
        $this->validateLineupRoster($request);

        $lineupList = LineupList::create($data);

        return redirect()->route('lineup-lists.show', $lineupList)->with('status', 'DSP berhasil ditambahkan.');
    }

    public function edit(LineupList $lineupList)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonLineup($lineupList);
        $this->ensureClubAccess($lineupList->club_id);
        abort_unless(auth()->user()->isAdmin() || $lineupList->canBeEditedByClub(), 422);

        return view('competition.lineups.edit', [
            'title' => 'Edit DSP',
            'lineupList' => $lineupList,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::competition()->get(),
            'availableMatches' => $this->availableMatches($lineupList),
        ]);
    }

    public function show(LineupList $lineupList)
    {
        $this->ensureClubAccess($lineupList->club_id);
        $lineupList->load(['club', 'ageGroup', 'match.clubA', 'match.clubB']);

        $activeSeasonId = $this->seasonContext->activeId();
        $isHistory = $lineupList->season_id && $activeSeasonId && (int) $lineupList->season_id !== (int) $activeSeasonId;

        if ($isHistory) {
            $players = \App\Models\SeasonPlayer::query()
                ->where('season_id', $lineupList->season_id)
                ->where('club_id', $lineupList->club_id)
                ->whereJsonContains('registered_age_group_ids', (int) $lineupList->age_group_id)
                ->where('verification_status', Player::STATUS_APPROVED)
                ->orderBy('name')
                ->get();
        } else {
            $players = Player::query()
                ->where('club_id', $lineupList->club_id)
                ->where('verification_status', Player::STATUS_APPROVED)
                ->whereHas('ageRegistrations', function ($query) use ($lineupList) {
                    $query->where('age_group_id', $lineupList->age_group_id);
                })
                ->orderBy('name')
                ->get();
        }

        $officials = Official::query()
            ->with('ageRegistrations.ageGroup')
            ->where('club_id', $lineupList->club_id)
            ->where('is_active', true)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $lineupList->age_group_id))
            ->when(! auth()->user()->isAdmin(), fn ($query) => $query->where('verification_status', Official::STATUS_APPROVED))
            ->orderBy('name')
            ->get();

        return view('competition.lineups.show', [
            'title' => 'Generate DSP',
            'lineupList' => $lineupList,
            'players' => $players,
            'officials' => $officials,
        ]);
    }

    public function update(Request $request, LineupList $lineupList)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonLineup($lineupList);
        $data = $this->validatedData($request);
        $this->ensureClubAccess($lineupList->club_id);
        $this->ensureClubAccess($data['club_id']);
        abort_unless(auth()->user()->isAdmin() || $lineupList->canBeEditedByClub(), 422);
        $this->ensureUniqueLineup($data, $lineupList->id);
        $this->validateLineupRoster($request);

        $lineupList->update($data);

        return redirect()->route('lineup-lists.show', $lineupList)->with('status', 'DSP berhasil diperbarui.');
    }

    public function submit(LineupList $lineupList)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonLineup($lineupList);
        $this->ensureClubAccess($lineupList->club_id);

        return $this->submitForVerification($lineupList, 'DSP berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, LineupList $lineupList)
    {
        $this->ensureWritableSeasonContext();
        $validated = $this->validateReviewPayload($request);

        return $this->reviewSubmission(
            $lineupList,
            $validated['status'],
            $validated['verification_notes'] ?? null,
            'Status verifikasi DSP berhasil diperbarui.'
        );
    }

    public function destroy(LineupList $lineupList)
    {
        $this->ensureWritableSeasonContext();
        $this->ensureActiveSeasonLineup($lineupList);
        $this->ensureClubAccess($lineupList->club_id);
        abort_unless(auth()->user()->isAdmin() || $lineupList->canBeSubmittedByClub(), 403);

        $lineupList->delete();

        return redirect()->route('lineup-lists.index')->with('status', 'DSP berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'match_id' => ['required', 'exists:match_schedules,id'],
            'club_id' => ['required', 'exists:clubs,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'match_date' => ['nullable', 'date'],
            'played_at' => ['nullable', 'string', 'max:255'],
            'played_time' => ['nullable', 'date_format:H:i'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'jersey_color' => ['nullable', 'string', 'max:255'],
            'goalkeeper_jersey_color' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $match = $this->availableMatches()->firstWhere('id', (int) $validated['match_id']);
        abort_unless($match, 403);
        abort_unless($match->includesClub((int) $validated['club_id']), 422);

        $title = trim(($validated['title'] ?? '') ?: sprintf(
            'DSP %s %s vs %s',
            $match->clubA?->short_name ?: $match->clubA?->name ?: 'Klub',
            $match->ageGroup?->name ?: '',
            $match->clubB?->short_name ?: $match->clubB?->name ?: 'Klub'
        ));

        return [
            'season_id' => $this->seasonContext->requireActive()->id,
            'match_id' => $match->id,
            'club_id' => (int) $validated['club_id'],
            'season_club_id' => $this->seasonSnapshotService->seasonClubIdForClub((int) $validated['club_id']),
            'age_group_id' => $match->age_group_id,
            'title' => substr($title, 0, 255),
            'match_day' => $match->match_day,
            'match_date' => ($validated['match_date'] ?? null) ?: optional($match->match_date)->format('Y-m-d'),
            'played_time' => ($validated['played_time'] ?? null) ?: optional($match->kickoff_time)->format('H:i'),
            'coach_name' => ($validated['coach_name'] ?? null) ?: Official::query()
                ->where('club_id', (int) $validated['club_id'])
                ->where('is_active', true)
                ->where('verification_status', Official::STATUS_APPROVED)
                ->where(function ($q) {
                    $q->where('role', 'Head Coach')
                      ->orWhere('role', 'like', '%Pelatih%')
                      ->orWhere('role', 'like', '%Coach%');
                })
                ->whereHas('allAgeRegistrations', function ($query) use ($match) {
                    $query->where('age_group_id', $match->age_group_id);
                })
                ->orderBy('role')
                ->value('name'),
            'jersey_color' => $validated['jersey_color'] ?? null,
            'goalkeeper_jersey_color' => $validated['goalkeeper_jersey_color'] ?? null,
            'played_at' => ($validated['played_at'] ?? null) ?: $match->venue,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function availableClubs()
    {
        $user = auth()->user();

        return Club::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    private function ensureClubAccess(?int $clubId): void
    {
        abort_unless($clubId, 404);

        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            Club::where('id', $clubId)->where('user_id', $user->id)->exists(),
            403
        );
    }

    private function lineupPlayers()
    {
        return $this->eligibleLineupPlayersQuery()
            ->orderBy('name')
            ->get();
    }

    private function blockedLineupPlayers()
    {
        return $this->lineupPlayerBaseQuery()
            ->where('verification_status', '!=', Player::STATUS_APPROVED)
            ->orderBy('name')
            ->get();
    }

    private function availableMatches(?LineupList $lineupList = null)
    {
        $user = auth()->user();
        $clubIds = $this->availableClubs()->pluck('id');
        $currentClubId = ! $user->isAdmin() ? (int) $clubIds->first() : null;

        $query = MatchSchedule::query()->forActiveSeason()
            ->with(['ageGroup', 'clubA', 'clubB', 'lineupLists:id,match_id,club_id'])
            ->when(! $clubIds->isEmpty(), function ($query) use ($clubIds) {
                $query->where(function ($inner) use ($clubIds) {
                    $inner->whereIn('club_a_id', $clubIds)
                        ->orWhereIn('club_b_id', $clubIds);
                });
            })
            ->orderBy('match_date')
            ->orderBy('kickoff_time');

        if ($lineupList?->match_id) {
            $query->where(function ($inner) use ($lineupList, $clubIds) {
                $inner->where(function ($matchQuery) use ($clubIds) {
                    $matchQuery->whereIn('club_a_id', $clubIds)
                        ->orWhereIn('club_b_id', $clubIds);
                })->orWhere('id', $lineupList->match_id);
            });
        }

        $matches = $query->get()->filter(function (MatchSchedule $match) use ($user, $lineupList, $currentClubId) {
            $usedClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id)->all();

            if ($lineupList && $match->id === $lineupList->match_id) {
                return true;
            }

            if ($user->isAdmin()) {
                return ! (
                    in_array((int) $match->club_a_id, $usedClubIds, true)
                    && in_array((int) $match->club_b_id, $usedClubIds, true)
                );
            }

            return $match->includesClub($currentClubId)
                && ! in_array($currentClubId, $usedClubIds, true);
        })->values();

        $coaches = Official::query()
            ->where('is_active', true)
            ->where('verification_status', Official::STATUS_APPROVED)
            ->where(function ($q) {
                $q->where('role', 'Head Coach')
                  ->orWhere('role', 'like', '%Pelatih%')
                  ->orWhere('role', 'like', '%Coach%');
            })
            ->with('allAgeRegistrations')
            ->get();

        foreach ($matches as $match) {
            $coachA = $coaches->filter(function ($official) use ($match) {
                return (int) $official->club_id === (int) $match->club_a_id
                    && $official->allAgeRegistrations->contains('age_group_id', $match->age_group_id);
            })->first();

            $coachB = $coaches->filter(function ($official) use ($match) {
                return (int) $official->club_id === (int) $match->club_b_id
                    && $official->allAgeRegistrations->contains('age_group_id', $match->age_group_id);
            })->first();

            $match->coach_a_name = $coachA?->name ?: '';
            $match->coach_b_name = $coachB?->name ?: '';
        }

        return $matches;
    }

    private function syncPlayers(LineupList $lineupList, Request $request): void
    {
        $starterIds = $this->sortedPlayerIds(
            $request->input('starter_player_ids', []),
            $request->input('starter_orders', [])
        );
        $substituteIds = $this->sortedPlayerIds(
            $request->input('substitute_player_ids', []),
            $request->input('substitute_orders', [])
        )->reject(fn ($id) => $starterIds->contains($id))->values();
        $starterJerseys = $request->input('starter_jerseys', []);
        $substituteJerseys = $request->input('substitute_jerseys', []);

        $allowedIds = $this->eligibleLineupPlayersQuery()
            ->where('club_id', $lineupList->club_id)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $lineupList->age_group_id))
            ->whereIn('id', $starterIds->merge($substituteIds))
            ->pluck('id');

        $syncData = [];

        foreach ($starterIds->values() as $index => $playerId) {
            if ($allowedIds->contains($playerId)) {
                $syncData[$playerId] = [
                    'role' => LineupList::ROLE_STARTER,
                    'display_order' => $index + 1,
                    'jersey_number' => $this->cleanJerseyNumberForStorage($starterJerseys[$playerId] ?? null),
                    'season_player_id' => $this->seasonSnapshotService->seasonPlayerIdForPlayer((int) $playerId),
                ];
            }
        }

        foreach ($substituteIds->values() as $index => $playerId) {
            if ($allowedIds->contains($playerId)) {
                $syncData[$playerId] = [
                    'role' => LineupList::ROLE_SUBSTITUTE,
                    'display_order' => $index + 1,
                    'jersey_number' => $this->cleanJerseyNumberForStorage($substituteJerseys[$playerId] ?? null),
                    'season_player_id' => $this->seasonSnapshotService->seasonPlayerIdForPlayer((int) $playerId),
                ];
            }
        }

        $lineupList->players()->sync($syncData);
    }

    private function sortedPlayerIds(array $playerIds, array $orders): Collection
    {
        return collect($playerIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->sortBy(fn ($id) => [
                (int) ($orders[$id] ?? 999),
                $id,
            ])
            ->values();
    }

    private function validateLineupRoster(Request $request): void
    {
        $match = $this->availableMatches()->firstWhere('id', (int) $request->input('match_id'));

        if (! $match) {
            throw ValidationException::withMessages([
                'match_id' => 'Pertandingan yang dipilih tidak tersedia untuk akun ini.',
            ]);
        }

        if (! $match->includesClub((int) $request->input('club_id'))) {
            throw ValidationException::withMessages([
                'club_id' => 'Klub yang dipilih tidak termasuk dalam pertandingan ini.',
            ]);
        }
    }

    private function normalizeJerseyNumber(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (string) ((int) $value);
        }

        return mb_strtoupper($value);
    }

    private function cleanJerseyNumberForStorage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function ensureUniqueLineup(array $data, ?int $ignoreId = null): void
    {
        $exists = LineupList::query()->forActiveSeason()
            ->where('match_id', $data['match_id'])
            ->where('club_id', $data['club_id'])
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'match_id' => 'Klub ini sudah memiliki DSP untuk pertandingan yang dipilih.',
            ]);
        }
    }

    private function lineupPlayerBaseQuery()
    {
        $clubIds = $this->availableClubs()->pluck('id');

        return Player::query()
            ->with('ageRegistrations.ageGroup')
            ->whereIn('club_id', $clubIds);
    }

    private function eligibleLineupPlayersQuery()
    {
        return $this->lineupPlayerBaseQuery()
            ->where('verification_status', Player::STATUS_APPROVED);
    }

    private function ensureActiveSeasonLineup(LineupList $lineupList): void
    {
        $activeSeasonId = $this->seasonContext->activeId();

        if ($lineupList->season_id && $activeSeasonId && (int) $lineupList->season_id !== (int) $activeSeasonId) {
            abort(403, 'DSP dari season yang sudah tidak aktif bersifat read-only.');
        }
    }

    private function ensureWritableSeasonContext(): void
    {
        if ($this->seasonContext->isViewingHistory()) {
            abort(403, 'Kamu sedang melihat histori season. Kembali ke season aktif untuk menambah atau mengubah DSP.');
        }
    }
}

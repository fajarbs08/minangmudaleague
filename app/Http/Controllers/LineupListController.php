<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LineupListController extends Controller
{
    use HandlesVerificationWorkflow;

    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = $request->string('sort')->value() ?: 'match_date';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['title', 'club', 'age_group', 'match_date', 'verification_status', 'created_at'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'match_date';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        $lineupLists = LineupList::query()
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
            'ageGroups' => AgeGroup::orderBy('min_age')->get(),
        ]);
    }

    public function bulkReview(Request $request)
    {
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

        $models = LineupList::query()
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
        $lineupList = new LineupList([
            'club_id' => request('club_id'),
            'age_group_id' => request('age_group_id'),
        ]);

        return view('competition.lineups.create', [
            'title' => 'Tambah DSP',
            'lineupList' => $lineupList,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::orderBy('min_age')->get(),
            'availableMatches' => $this->availableMatches(),
            'lineupPlayers' => $this->lineupPlayers(),
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
        $data = $this->validatedData($request);
        $this->ensureClubAccess($data['club_id']);
        $this->ensureUniqueLineup($data);
        $this->validateLineupRoster($request);

        $lineupList = LineupList::create($data);
        $this->syncPlayers($lineupList, $request);

        return redirect()->route('lineup-lists.index')->with('status', 'DSP berhasil ditambahkan.');
    }

    public function edit(LineupList $lineupList)
    {
        $this->ensureClubAccess($lineupList->club_id);

        return view('competition.lineups.edit', [
            'title' => 'Edit DSP',
            'lineupList' => $lineupList,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::orderBy('min_age')->get(),
            'availableMatches' => $this->availableMatches($lineupList),
            'lineupPlayers' => $this->lineupPlayers(),
            'selectedStarters' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_STARTER)
                ->pluck('id')
                ->all(),
            'selectedSubstitutes' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_SUBSTITUTE)
                ->pluck('id')
                ->all(),
            'selectedStarterOrders' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_STARTER)
                ->mapWithKeys(fn ($player) => [$player->id => $player->pivot->display_order])
                ->all(),
            'selectedSubstituteOrders' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_SUBSTITUTE)
                ->mapWithKeys(fn ($player) => [$player->id => $player->pivot->display_order])
                ->all(),
            'selectedStarterJerseys' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_STARTER)
                ->mapWithKeys(fn ($player) => [$player->id => $player->pivot->jersey_number])
                ->all(),
            'selectedSubstituteJerseys' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_SUBSTITUTE)
                ->mapWithKeys(fn ($player) => [$player->id => $player->pivot->jersey_number])
                ->all(),
        ]);
    }

    public function show(LineupList $lineupList)
    {
        $this->ensureClubAccess($lineupList->club_id);
        $lineupList->load(['club', 'ageGroup', 'match.clubA', 'match.clubB', 'players.ageRegistrations']);
        $officials = Official::query()
            ->with('ageRegistrations.ageGroup')
            ->where('club_id', $lineupList->club_id)
            ->where('is_active', true)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $lineupList->age_group_id))
            ->when(!auth()->user()->isAdmin(), fn ($query) => $query->where('verification_status', Official::STATUS_APPROVED))
            ->orderBy('name')
            ->get();

        return view('competition.lineups.show', [
            'title' => 'Generate DSP',
            'lineupList' => $lineupList,
            'starters' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_STARTER)
                ->sortBy('pivot.display_order')
                ->values(),
            'substitutes' => $lineupList->players
                ->where('pivot.role', LineupList::ROLE_SUBSTITUTE)
                ->sortBy('pivot.display_order')
                ->values(),
            'officials' => $officials,
        ]);
    }

    public function update(Request $request, LineupList $lineupList)
    {
        $data = $this->validatedData($request);
        $this->ensureClubAccess($lineupList->club_id);
        $this->ensureClubAccess($data['club_id']);
        abort_unless(auth()->user()->isAdmin() || $lineupList->canBeEditedByClub(), 422);
        $this->ensureUniqueLineup($data, $lineupList->id);
        $this->validateLineupRoster($request);

        $lineupList->update($data);
        $this->syncPlayers($lineupList, $request);

        return redirect()->route('lineup-lists.index')->with('status', 'DSP berhasil diperbarui.');
    }

    public function submit(LineupList $lineupList)
    {
        $this->ensureClubAccess($lineupList->club_id);

        return $this->submitForVerification($lineupList, 'DSP berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, LineupList $lineupList)
    {
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
        $this->ensureClubAccess($lineupList->club_id);
        abort_unless(auth()->user()->isAdmin() || $lineupList->canBeSubmittedByClub(), 403);

        $lineupList->delete();

        return redirect()->route('lineup-lists.index')->with('status', 'DSP berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $request->validate([
            'starter_player_ids' => ['nullable', 'array'],
            'starter_player_ids.*' => ['integer', 'exists:players,id'],
            'substitute_player_ids' => ['nullable', 'array'],
            'substitute_player_ids.*' => ['integer', 'exists:players,id'],
            'starter_orders' => ['nullable', 'array'],
            'starter_orders.*' => ['nullable', 'integer', 'min:1', 'max:99'],
            'substitute_orders' => ['nullable', 'array'],
            'substitute_orders.*' => ['nullable', 'integer', 'min:1', 'max:99'],
            'starter_jerseys' => ['nullable', 'array'],
            'starter_jerseys.*' => ['nullable', 'string', 'max:10'],
            'substitute_jerseys' => ['nullable', 'array'],
            'substitute_jerseys.*' => ['nullable', 'string', 'max:10'],
        ]);

        $validated = $request->validate([
            'match_id' => ['required', 'exists:match_schedules,id'],
            'club_id' => ['required', 'exists:clubs,id'],
            'title' => ['nullable', 'string', 'max:255'],
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
            'match_id' => $match->id,
            'club_id' => (int) $validated['club_id'],
            'age_group_id' => $match->age_group_id,
            'title' => substr($title, 0, 255),
            'match_day' => $match->match_day,
            'match_date' => optional($match->match_date)->format('Y-m-d'),
            'played_time' => ($validated['played_time'] ?? null) ?: optional($match->kickoff_time)->format('H:i'),
            'coach_name' => $validated['coach_name'] ?? null,
            'jersey_color' => $validated['jersey_color'] ?? null,
            'goalkeeper_jersey_color' => $validated['goalkeeper_jersey_color'] ?? null,
            'played_at' => $match->venue,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function availableClubs()
    {
        $user = auth()->user();

        return Club::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    private function ensureClubAccess(int $clubId): void
    {
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
        $clubIds = $this->availableClubs()->pluck('id');
        return Player::query()
            ->with('ageRegistrations.ageGroup')
            ->whereIn('club_id', $clubIds)
            ->when(!auth()->user()->isAdmin(), fn ($query) => $query->where('verification_status', Player::STATUS_APPROVED))
            ->orderBy('name')
            ->get();
    }

    private function availableMatches(?LineupList $lineupList = null)
    {
        $user = auth()->user();
        $clubIds = $this->availableClubs()->pluck('id');
        $currentClubId = !$user->isAdmin() ? (int) $clubIds->first() : null;

        $query = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'lineupLists:id,match_id,club_id'])
            ->when(!$clubIds->isEmpty(), function ($query) use ($clubIds) {
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

        return $query->get()->filter(function (MatchSchedule $match) use ($user, $lineupList, $currentClubId) {
            $usedClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id)->all();

            if ($lineupList && $match->id === $lineupList->match_id) {
                return true;
            }

            if ($user->isAdmin()) {
                return !(
                    in_array((int) $match->club_a_id, $usedClubIds, true)
                    && in_array((int) $match->club_b_id, $usedClubIds, true)
                );
            }

            return $match->includesClub($currentClubId)
                && !in_array($currentClubId, $usedClubIds, true);
        })->values();
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

        $allowedIds = Player::query()
            ->where('club_id', $lineupList->club_id)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $lineupList->age_group_id))
            ->when(!auth()->user()->isAdmin(), fn ($query) => $query->where('verification_status', Player::STATUS_APPROVED))
            ->whereIn('id', $starterIds->merge($substituteIds))
            ->pluck('id');

        $syncData = [];

        foreach ($starterIds->values() as $index => $playerId) {
            if ($allowedIds->contains($playerId)) {
                $syncData[$playerId] = [
                    'role' => LineupList::ROLE_STARTER,
                    'display_order' => $index + 1,
                    'jersey_number' => $starterJerseys[$playerId] ?? null,
                ];
            }
        }

        foreach ($substituteIds->values() as $index => $playerId) {
            if ($allowedIds->contains($playerId)) {
                $syncData[$playerId] = [
                    'role' => LineupList::ROLE_SUBSTITUTE,
                    'display_order' => $index + 1,
                    'jersey_number' => $substituteJerseys[$playerId] ?? null,
                ];
            }
        }

        $lineupList->players()->sync($syncData);
    }

    private function sortedPlayerIds(array $playerIds, array $orders): \Illuminate\Support\Collection
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

        if (!$match) {
            throw ValidationException::withMessages([
                'match_id' => 'Pertandingan yang dipilih tidak tersedia untuk akun ini.',
            ]);
        }

        $starterIds = collect($request->input('starter_player_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $substituteIds = collect($request->input('substitute_player_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($starterIds->count() !== LineupList::REQUIRED_STARTERS) {
            throw ValidationException::withMessages([
                'starter_player_ids' => 'DSP harus berisi tepat '.LineupList::REQUIRED_STARTERS.' pemain starter.',
            ]);
        }

        if ($substituteIds->count() > LineupList::MAX_SUBSTITUTES) {
            throw ValidationException::withMessages([
                'substitute_player_ids' => 'DSP maksimal berisi '.LineupList::MAX_SUBSTITUTES.' pemain cadangan.',
            ]);
        }

        if (!$match->includesClub((int) $request->input('club_id'))) {
            throw ValidationException::withMessages([
                'club_id' => 'Klub yang dipilih tidak termasuk dalam pertandingan ini.',
            ]);
        }
    }

    private function ensureUniqueLineup(array $data, ?int $ignoreId = null): void
    {
        $exists = LineupList::query()
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
}

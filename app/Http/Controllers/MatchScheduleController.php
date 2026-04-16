<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\MatchSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MatchScheduleController extends Controller
{
    public function index(Request $request)
    {
        $matches = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'lineupLists:id,match_id,club_id,verification_status'])
            ->when($request->input('club_id'), function ($query, $clubId) {
                $query->where(fn ($inner) => $inner
                    ->where('club_a_id', $clubId)
                    ->orWhere('club_b_id', $clubId));
            })
            ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->where('age_group_id', $ageGroupId))
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
            })
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->paginate(10)
            ->withQueryString();

        return view('competition.matches.index', [
            'title' => 'Jadwal Pertandingan',
            'matches' => $matches,
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::query()->orderBy('min_age')->get(),
        ]);
    }

    public function create()
    {
        return view('competition.matches.create', [
            'title' => 'Tambah Jadwal Pertandingan',
            'matchSchedule' => new MatchSchedule(),
            'clubs' => Club::query()->orderBy('name')->get(),
            'ageGroups' => AgeGroup::query()->orderBy('min_age')->get(),
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
            'ageGroups' => AgeGroup::query()->orderBy('min_age')->get(),
        ]);
    }

    public function update(Request $request, MatchSchedule $match)
    {
        if ($match->lineupLists()->exists()) {
            throw ValidationException::withMessages([
                'match' => 'Jadwal pertandingan yang sudah dipakai DSP tidak bisa diubah. Buat jadwal baru atau hapus DSP terkait terlebih dahulu.',
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

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'age_group_id' => ['required', 'exists:age_groups,id'],
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

        return $validated;
    }
}

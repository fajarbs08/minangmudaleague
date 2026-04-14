<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\LineupList;
use App\Models\Official;
use App\Models\Player;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $clubIds = $this->resolveClubIds($request);

        $clubs = collect();
        $officials = collect();
        $players = collect();
        $lineups = collect();

        if ($query !== '') {
            $clubsQuery = Club::query()->select(['id', 'name', 'city']);
            if ($clubIds) {
                $clubsQuery->whereIn('id', $clubIds);
            }

            $clubs = $this->applySearchTerms($clubsQuery, ['name', 'city'], $query)
                ->orderBy('name')
                ->limit(8)
                ->get();

            $officialsQuery = Official::query()->select(['id', 'club_id', 'name', 'role', 'email']);
            if ($clubIds) {
                $officialsQuery->whereIn('club_id', $clubIds);
            }

            $officials = $this->applySearchTerms($officialsQuery, ['name', 'role', 'email'], $query)
                ->orderBy('name')
                ->limit(8)
                ->get();

            $playersQuery = Player::query()->select(['id', 'club_id', 'name', 'registration_number', 'school_name']);
            if ($clubIds) {
                $playersQuery->whereIn('club_id', $clubIds);
            }

            $players = $this->applySearchTerms($playersQuery, ['name', 'registration_number', 'school_name'], $query)
                ->orderBy('name')
                ->limit(8)
                ->get();

            $lineupsQuery = LineupList::query()->select(['id', 'club_id', 'title', 'match_day', 'match_date']);
            if ($clubIds) {
                $lineupsQuery->whereIn('club_id', $clubIds);
            }

            $lineups = $this->applySearchTerms($lineupsQuery, ['title', 'coach_name', 'match_day'], $query)
                ->orderByDesc('match_date')
                ->limit(8)
                ->get();
        }

        return view('competition.search.index', [
            'title' => 'Pencarian',
            'query' => $query,
            'clubs' => $clubs,
            'officials' => $officials,
            'players' => $players,
            'lineups' => $lineups,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
                'lucky' => null,
            ]);
        }

        $clubIds = $this->resolveClubIds($request);
        $suggestions = collect();

        $clubsQuery = Club::query()->select(['id', 'name', 'city']);
        if ($clubIds) {
            $clubsQuery->whereIn('id', $clubIds);
        }

        $suggestions = $suggestions->merge(
            $this->applySearchTerms($clubsQuery, ['name', 'city'], $query)
                ->orderBy('name')
                ->limit(3)
                ->get()
                ->map(fn (Club $club) => [
                    'type' => 'Klub',
                    'label' => $club->name,
                    'description' => $club->city ?: 'Data klub',
                    'url' => route('clubs.edit', $club),
                ])
        );

        $officialsQuery = Official::query()->select(['id', 'club_id', 'name', 'role', 'email']);
        if ($clubIds) {
            $officialsQuery->whereIn('club_id', $clubIds);
        }

        $suggestions = $suggestions->merge(
            $this->applySearchTerms($officialsQuery, ['name', 'role', 'email'], $query)
                ->orderBy('name')
                ->limit(2)
                ->get()
                ->map(fn (Official $official) => [
                    'type' => 'Official',
                    'label' => $official->name,
                    'description' => $official->role ?: ($official->email ?: 'Data official'),
                    'url' => route('officials.show', $official),
                ])
        );

        $playersQuery = Player::query()->select(['id', 'club_id', 'name', 'registration_number', 'school_name']);
        if ($clubIds) {
            $playersQuery->whereIn('club_id', $clubIds);
        }

        $suggestions = $suggestions->merge(
            $this->applySearchTerms($playersQuery, ['name', 'registration_number', 'school_name'], $query)
                ->orderBy('name')
                ->limit(2)
                ->get()
                ->map(fn (Player $player) => [
                    'type' => 'Pemain',
                    'label' => $player->name,
                    'description' => $player->registration_number ?: ($player->school_name ?: 'Data pemain'),
                    'url' => route('players.show', $player),
                ])
        );

        $lineupsQuery = LineupList::query()->select(['id', 'club_id', 'title', 'match_day', 'match_date']);
        if ($clubIds) {
            $lineupsQuery->whereIn('club_id', $clubIds);
        }

        $suggestions = $suggestions->merge(
            $this->applySearchTerms($lineupsQuery, ['title', 'coach_name', 'match_day'], $query)
                ->orderByDesc('match_date')
                ->limit(1)
                ->get()
                ->map(fn (LineupList $lineup) => [
                    'type' => 'DSP',
                    'label' => $lineup->title,
                    'description' => $lineup->match_day ?: 'Data DSP',
                    'url' => route('lineup-lists.show', $lineup),
                ])
        )->take(8)->values();

        $lucky = $suggestions->first();

        return response()->json([
            'suggestions' => $suggestions,
            'lucky' => $lucky
                ? [
                    'label' => $lucky['label'],
                    'type' => $lucky['type'],
                    'url' => $lucky['url'],
                ]
                : null,
        ]);
    }

    private function resolveClubIds(Request $request): ?Collection
    {
        $user = $request->user();

        return $user->isAdmin()
            ? null
            : $user->clubs()->pluck('id');
    }

    private function applySearchTerms(Builder $query, array $columns, string $keyword): Builder
    {
        $terms = $this->extractTerms($keyword);

        foreach ($terms as $term) {
            $query->where(function (Builder $inner) use ($columns, $term) {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $inner->{$method}($column, 'like', '%'.$term.'%');
                }
            });
        }

        return $query;
    }

    private function extractTerms(string $keyword): array
    {
        return collect(preg_split('/\s+/', trim($keyword)) ?: [])
            ->map(fn (string $term) => trim($term))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

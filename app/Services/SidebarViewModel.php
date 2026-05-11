<?php

namespace App\Services;

use App\Models\Club;
use App\Models\LineupList;
use App\Models\Official;
use App\Models\Player;
use App\Models\SeasonClub;
use App\Models\SeasonOfficial;
use App\Models\SeasonPlayer;
use App\ViewModels\SidebarData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SidebarViewModel
{
    public function __construct(private SeasonContext $seasonContext) {}

    public function current(): SidebarData
    {
        return new SidebarData(
            adminPendingCounts: auth()->user()?->isAdmin()
                ? $this->adminPendingCounts()
                : [],
            navState: $this->navState(),
        );
    }

    private function adminPendingCounts(): array
    {
        $seasonId = $this->seasonContext->currentId();
        $seasonKey = $seasonId ?? 'none';
        $seasonMode = $this->seasonContext->isViewingHistory() ? 'history' : 'active';
        $cacheKey = sprintf('admin_sidebar_counts:season:%s:mode:%s', $seasonKey, $seasonMode);

        return Cache::remember($cacheKey, 45, function () use ($seasonId) {
            if ($seasonId && $this->hasSeasonSnapshotTables()) {
                return [
                    'clubs' => SeasonClub::query()
                        ->where('season_id', $seasonId)
                        ->where('verification_status', Club::STATUS_SUBMITTED)
                        ->count(),
                    'officials' => SeasonOfficial::query()
                        ->where('season_id', $seasonId)
                        ->where('verification_status', Official::STATUS_SUBMITTED)
                        ->count(),
                    'players' => SeasonPlayer::query()
                        ->where('season_id', $seasonId)
                        ->where('verification_status', Player::STATUS_SUBMITTED)
                        ->count(),
                    'lineups' => LineupList::query()
                        ->forSeason($seasonId)
                        ->where('verification_status', LineupList::STATUS_SUBMITTED)
                        ->count(),
                ];
            }

            return [
                'clubs' => Club::query()->where('verification_status', Club::STATUS_SUBMITTED)->count(),
                'officials' => Official::query()->where('verification_status', Official::STATUS_SUBMITTED)->count(),
                'players' => Player::query()->where('verification_status', Player::STATUS_SUBMITTED)->count(),
                'lineups' => LineupList::query()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
            ];
        });
    }

    private function navState(): array
    {
        return [
            'dashboard' => request()->routeIs('dashboard.home') || request()->routeIs('dashboard.index'),
            'account_management' => request()->routeIs('admin-accounts.*') || request()->routeIs('club-accounts.*'),
            'clubs' => request()->routeIs('clubs.*'),
            'officials' => request()->routeIs('officials.*'),
            'players' => request()->routeIs('players.*'),
            'lineups' => request()->routeIs('lineup-lists.*'),
            'match_schedules' => request()->routeIs('matches.*'),
            'seasons' => request()->routeIs('seasons.*'),
            'match_results' => request()->routeIs('match-results.*'),
            'reports' => request()->routeIs('reports.*'),
            'sponsors' => request()->routeIs('sponsors.*'),
        ];
    }

    private function hasSeasonSnapshotTables(): bool
    {
        return Cache::rememberForever('sidebar:has-season-snapshot-tables', fn () =>
            Schema::hasTable('season_clubs')
            && Schema::hasTable('season_officials')
            && Schema::hasTable('season_players')
        );
    }
}

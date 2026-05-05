<?php

namespace App\Models\Concerns;

use App\Services\SeasonContext;
use Illuminate\Database\Eloquent\Builder;

trait HasSeasonScopes
{
    public function scopeForSeason(Builder $query, ?int $seasonId): Builder
    {
        if (! $seasonId) {
            return $query;
        }

        return $query->where($this->qualifyColumn('season_id'), $seasonId);
    }

    public function scopeForActiveSeason(Builder $query): Builder
    {
        return $query->forSeason(app(SeasonContext::class)->currentId());
    }
}

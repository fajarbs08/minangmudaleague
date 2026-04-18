<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class AgeGroup extends Model
{
    public const COMPETITION_CODES = ['U10', 'U12', 'U14', 'U16'];

    protected $fillable = [
        'name',
        'code',
        'min_age',
        'max_age',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'primary_age_group_id');
    }

    public function lineupLists(): HasMany
    {
        return $this->hasMany(LineupList::class);
    }

    public function playerAgeGroups(): HasMany
    {
        return $this->hasMany(PlayerAgeGroup::class);
    }

    public function scopeCompetition(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereIn('code', self::COMPETITION_CODES)
            ->orderBy('min_age');
    }

    public static function competitionExistsRule(): Exists
    {
        return Rule::exists('age_groups', 'id')
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->whereIn('code', self::COMPETITION_CODES);
            });
    }
}

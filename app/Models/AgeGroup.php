<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
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
}

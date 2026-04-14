<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAgeGroup extends Model
{
    protected $fillable = [
        'player_id',
        'age_group_id',
        'season',
        'jersey_number',
        'position',
        'registration_status',
        'status_date',
        'notes',
        'is_starter',
        'is_substitute',
    ];

    protected function casts(): array
    {
        return [
            'status_date' => 'datetime',
            'is_starter' => 'boolean',
            'is_substitute' => 'boolean',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }
}

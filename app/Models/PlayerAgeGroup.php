<?php

namespace App\Models;

use App\Models\Concerns\HasSeasonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAgeGroup extends Model
{
    use HasSeasonScopes;

    protected $fillable = [
        'player_id',
        'age_group_id',
        'season_id',
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
            'season_id' => 'integer',
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

    public function seasonModel(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }
}

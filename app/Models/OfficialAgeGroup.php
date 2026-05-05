<?php

namespace App\Models;

use App\Models\Concerns\HasSeasonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialAgeGroup extends Model
{
    use HasSeasonScopes;

    protected $fillable = [
        'official_id',
        'age_group_id',
        'season_id',
        'season',
        'role',
        'license_levels',
        'registration_status',
        'status_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'season_id' => 'integer',
            'status_date' => 'datetime',
        ];
    }

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
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

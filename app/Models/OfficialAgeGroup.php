<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialAgeGroup extends Model
{
    protected $fillable = [
        'official_id',
        'age_group_id',
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
}

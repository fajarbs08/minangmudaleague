<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'slug',
        'starts_at',
        'ends_at',
        'status',
        'is_active',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function seasonClubs(): HasMany
    {
        return $this->hasMany(SeasonClub::class);
    }

    public function seasonPlayers(): HasMany
    {
        return $this->hasMany(SeasonPlayer::class);
    }

    public function seasonOfficials(): HasMany
    {
        return $this->hasMany(SeasonOfficial::class);
    }

    public function matchSchedules(): HasMany
    {
        return $this->hasMany(MatchSchedule::class);
    }

    public function lineupLists(): HasMany
    {
        return $this->hasMany(LineupList::class);
    }

    public function matchGoals(): HasMany
    {
        return $this->hasMany(MatchGoal::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function allowsWrites(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->is_active === true;
    }
}

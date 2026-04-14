<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchSchedule extends Model
{
    protected $fillable = [
        'age_group_id',
        'club_a_id',
        'club_b_id',
        'match_day',
        'venue',
        'match_date',
        'kickoff_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
            'kickoff_time' => 'datetime:H:i',
        ];
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function clubA(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_a_id');
    }

    public function clubB(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_b_id');
    }

    public function lineupLists(): HasMany
    {
        return $this->hasMany(LineupList::class, 'match_id');
    }

    public function includesClub(?int $clubId): bool
    {
        if (!$clubId) {
            return false;
        }

        return (int) $this->club_a_id === $clubId || (int) $this->club_b_id === $clubId;
    }

    public function opponentForClub(?int $clubId): ?Club
    {
        if (!$clubId) {
            return null;
        }

        if ((int) $this->club_a_id === $clubId) {
            return $this->clubB;
        }

        if ((int) $this->club_b_id === $clubId) {
            return $this->clubA;
        }

        return null;
    }

    public function labelForClub(?int $clubId): string
    {
        $clubName = $clubId && $this->includesClub($clubId)
            ? (string) optional($this->opponentForClub($clubId))->name
            : trim(collect([$this->clubA?->name, 'vs', $this->clubB?->name])->filter()->implode(' '));

        return trim(collect([
            $this->match_day,
            $clubName ? '- '.$clubName : null,
            optional($this->match_date)->format('d M Y'),
            optional($this->kickoff_time)->format('H:i'),
        ])->filter()->implode(' '));
    }
}

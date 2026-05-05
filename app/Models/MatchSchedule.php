<?php

namespace App\Models;

use App\Models\Concerns\HasSeasonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MatchSchedule extends Model
{
    use HasSeasonScopes;

    public const FORMAT_LEAGUE = 'league';

    public const FORMAT_KNOCKOUT = 'knockout';

    protected $fillable = [
        'season_id',
        'age_group_id',
        'competition_format',
        'round_label',
        'round_order',
        'bracket_slot',
        'source_match_a_id',
        'source_match_b_id',
        'club_a_id',
        'club_a_season_id',
        'club_b_id',
        'club_b_season_id',
        'match_day',
        'venue',
        'match_date',
        'kickoff_time',
        'score_club_a',
        'score_club_b',
        'is_finished',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'season_id' => 'integer',
            'match_date' => 'date',
            'kickoff_time' => 'datetime:H:i',
            'is_finished' => 'boolean',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function clubA(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_a_id');
    }

    public function clubASeason(): BelongsTo
    {
        return $this->belongsTo(SeasonClub::class, 'club_a_season_id');
    }

    public function clubB(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_b_id');
    }

    public function clubBSeason(): BelongsTo
    {
        return $this->belongsTo(SeasonClub::class, 'club_b_season_id');
    }

    public function sourceMatchA(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_match_a_id');
    }

    public function sourceMatchB(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_match_b_id');
    }

    public function lineupLists(): HasMany
    {
        return $this->hasMany(LineupList::class, 'match_id')->forSeason($this->season_id);
    }

    public function goalEvents(): HasMany
    {
        return $this->hasMany(MatchGoal::class, 'match_id')->forSeason($this->season_id)->orderBy('display_order');
    }

    public function includesClub(?int $clubId): bool
    {
        if (! $clubId) {
            return false;
        }

        return (int) $this->club_a_id === $clubId || (int) $this->club_b_id === $clubId;
    }

    public function opponentForClub(?int $clubId): ?Club
    {
        if (! $clubId) {
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

    public function getCompetitionFormatLabelAttribute(): string
    {
        return match ($this->competition_format) {
            self::FORMAT_KNOCKOUT => 'Knockout',
            default => 'Liga',
        };
    }

    public function getRoundDisplayLabelAttribute(): string
    {
        if (filled($this->round_label)) {
            return $this->round_label;
        }

        return $this->competition_format === self::FORMAT_KNOCKOUT ? 'Babak Knockout' : 'Liga';
    }

    public function getScoreLabelAttribute(): string
    {
        if ($this->score_club_a === null || $this->score_club_b === null) {
            return '-';
        }

        return $this->score_club_a.' - '.$this->score_club_b;
    }

    public function getTotalGoalsAttribute(): int
    {
        return (int) ($this->score_club_a ?? 0) + (int) ($this->score_club_b ?? 0);
    }

    public function getHasCleanSheetAttribute(): bool
    {
        if ($this->score_club_a === null || $this->score_club_b === null) {
            return false;
        }

        return (int) $this->score_club_a === 0 || (int) $this->score_club_b === 0;
    }

    public function getHasWinnerAttribute(): bool
    {
        return $this->winner_club_id !== null;
    }

    public function getWinnerClubIdAttribute(): ?int
    {
        if (! $this->is_finished || $this->score_club_a === null || $this->score_club_b === null) {
            return null;
        }

        if ((int) $this->score_club_a === (int) $this->score_club_b) {
            return null;
        }

        return (int) $this->score_club_a > (int) $this->score_club_b
            ? (int) $this->club_a_id
            : (int) $this->club_b_id;
    }

    public function getWinnerClubNameAttribute(): ?string
    {
        if (! $this->winner_club_id) {
            return null;
        }

        return (int) $this->winner_club_id === (int) $this->club_a_id
            ? ($this->club_a_display_name ?: $this->clubA?->name ?: $this->clubA?->short_name)
            : ($this->club_b_display_name ?: $this->clubB?->name ?: $this->clubB?->short_name);
    }

    public function getClubADisplayNameAttribute(): ?string
    {
        return $this->clubASeason?->name ?: $this->clubA?->name ?: $this->clubA?->short_name;
    }

    public function getClubBDisplayNameAttribute(): ?string
    {
        return $this->clubBSeason?->name ?: $this->clubB?->name ?: $this->clubB?->short_name;
    }

    public function getClubALogoFileUrlAttribute(): ?string
    {
        return $this->clubASeason?->logo_file_url ?: $this->clubA?->logo_file_url;
    }

    public function getClubBLogoFileUrlAttribute(): ?string
    {
        return $this->clubBSeason?->logo_file_url ?: $this->clubB?->logo_file_url;
    }

    public function getPublicSlugAttribute(): string
    {
        $base = Str::slug(collect([
            $this->clubA?->short_name ?: $this->clubA?->name ?: 'klub-a',
            'vs',
            $this->clubB?->short_name ?: $this->clubB?->name ?: 'klub-b',
            optional($this->match_date)->format('d-m-Y'),
        ])->filter()->implode(' '));

        return trim(($base ?: 'hasil-pertandingan').'-'.$this->id, '-');
    }

    public function getResultSummaryAttribute(): string
    {
        if (! $this->is_finished || $this->score_club_a === null || $this->score_club_b === null) {
            return 'Belum ada hasil';
        }

        if ($this->score_club_a === $this->score_club_b) {
            return 'Imbang';
        }

        return $this->score_club_a > $this->score_club_b
            ? (($this->clubA?->short_name ?: $this->clubA?->name ?: 'Klub A').' menang')
            : (($this->clubB?->short_name ?: $this->clubB?->name ?: 'Klub B').' menang');
    }

    public function goalReportForClub(?int $clubId): array
    {
        if (! $clubId) {
            return [];
        }

        return $this->goalEvents
            ->where('club_id', (int) $clubId)
            ->values()
            ->map(function (MatchGoal $goal) {
                $scorer = $goal->scorer?->name ?: 'Pemain tidak ditemukan';
                $assist = $goal->assistPlayer?->name;

                return $assist ? $scorer.' (assist: '.$assist.')' : $scorer;
            })
            ->all();
    }
}

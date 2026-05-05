<?php

namespace App\Models;

use App\Models\Concerns\HasSeasonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchGoal extends Model
{
    use HasSeasonScopes;

    protected $fillable = [
        'season_id',
        'match_id',
        'club_id',
        'season_club_id',
        'player_id',
        'season_player_id',
        'assist_player_id',
        'assist_season_player_id',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'season_id' => 'integer',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchSchedule::class, 'match_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function seasonClub(): BelongsTo
    {
        return $this->belongsTo(SeasonClub::class);
    }

    public function scorer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function seasonScorer(): BelongsTo
    {
        return $this->belongsTo(SeasonPlayer::class, 'season_player_id');
    }

    public function assistPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'assist_player_id');
    }

    public function seasonAssistPlayer(): BelongsTo
    {
        return $this->belongsTo(SeasonPlayer::class, 'assist_season_player_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchGoal extends Model
{
    protected $fillable = [
        'match_id',
        'club_id',
        'player_id',
        'assist_player_id',
        'display_order',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchSchedule::class, 'match_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function scorer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function assistPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'assist_player_id');
    }
}

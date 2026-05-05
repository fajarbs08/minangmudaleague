<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeasonClub extends Model
{
    protected $fillable = [
        'season_id',
        'club_id',
        'user_id',
        'name',
        'short_name',
        'manager_name',
        'manager_title',
        'zone',
        'founded_year',
        'logo_url',
        'statement_file_path',
        'address',
        'training_address',
        'notes',
        'verification_status',
        'verification_notes',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'snapshot_source_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'snapshot_source_updated_at' => 'datetime',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function seasonPlayers(): HasMany
    {
        return $this->hasMany(SeasonPlayer::class);
    }

    public function seasonOfficials(): HasMany
    {
        return $this->hasMany(SeasonOfficial::class);
    }

    public function lineupLists(): HasMany
    {
        return $this->hasMany(LineupList::class, 'season_club_id');
    }

    public function getLogoFileUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->logo_url);
    }

    public function getStatementFileUrlAttribute(): ?string
    {
        if (! $this->statement_file_path) {
            return null;
        }

        if (str_starts_with($this->statement_file_path, 'http://') || str_starts_with($this->statement_file_path, 'https://')) {
            return $this->statement_file_path;
        }

        $path = ltrim($this->statement_file_path, '/');

        if (! Storage::disk('local')->exists($path) && ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return route('clubs.statement.download', ['club' => $this->club_id]);
    }

    public function getPublicSlugAttribute(): string
    {
        $base = $this->name ?: $this->short_name ?: 'klub';

        return Str::slug($base).'-'.$this->club_id;
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        if (app()->runningInConsole()) {
            return Storage::disk('public')->url($path);
        }

        return url('/storage/'.$path);
    }
}

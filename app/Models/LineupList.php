<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LineupList extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const ROLE_STARTER = 'starter';

    public const ROLE_SUBSTITUTE = 'substitute';

    public const REQUIRED_STARTERS = 11;

    public const MAX_SUBSTITUTES = 9;

    protected $fillable = [
        'club_id',
        'age_group_id',
        'match_id',
        'title',
        'match_day',
        'match_date',
        'played_time',
        'coach_name',
        'jersey_color',
        'goalkeeper_jersey_color',
        'played_at',
        'document_url',
        'notes',
        'verification_status',
        'verification_notes',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
            'played_time' => 'datetime:H:i',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchSchedule::class, 'match_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)
            ->withPivot(['role', 'display_order', 'jersey_number'])
            ->withTimestamps()
            ->orderByPivot('role')
            ->orderByPivot('display_order');
    }

    public function starters(): BelongsToMany
    {
        return $this->players()->wherePivot('role', self::ROLE_STARTER);
    }

    public function substitutes(): BelongsToMany
    {
        return $this->players()->wherePivot('role', self::ROLE_SUBSTITUTE);
    }

    public function canBeEditedByClub(): bool
    {
        return in_array($this->verification_status, [
            self::STATUS_DRAFT,
            self::STATUS_REVISION,
        ], true);
    }

    public function canBeSubmittedByClub(): bool
    {
        return in_array($this->verification_status, [
            self::STATUS_DRAFT,
            self::STATUS_REVISION,
        ], true);
    }

    public function canBeReviewedByAdmin(): bool
    {
        return in_array($this->verification_status, [
            self::STATUS_SUBMITTED,
            self::STATUS_APPROVED,
            self::STATUS_REVISION,
            self::STATUS_REJECTED,
        ], true);
    }

    public function getDocumentFileUrlAttribute(): ?string
    {
        if (! $this->document_url) {
            return null;
        }

        if (str_starts_with($this->document_url, 'http://') || str_starts_with($this->document_url, 'https://')) {
            return $this->document_url;
        }

        $path = ltrim($this->document_url, '/');

        if (app()->runningInConsole()) {
            return Storage::disk('public')->url($path);
        }

        return url('/storage/'.$path);
    }

    public function getStarterCountAttribute(): int
    {
        return $this->players->where('pivot.role', self::ROLE_STARTER)->count();
    }

    public function getSubstituteCountAttribute(): int
    {
        return $this->players->where('pivot.role', self::ROLE_SUBSTITUTE)->count();
    }

    public function opponent(): ?Club
    {
        return $this->match?->opponentForClub($this->club_id);
    }

    public function validateForSubmission(): void
    {
        $errors = [];

        if (! $this->match_id || ! $this->match) {
            $errors['match_id'] = 'DSP harus terhubung ke jadwal pertandingan sebelum submit verifikasi.';
        }
        if (blank($this->played_at)) {
            $errors['played_at'] = 'Venue pertandingan wajib tersedia sebelum submit verifikasi.';
        }
        if (blank($this->match_date)) {
            $errors['match_date'] = 'Tanggal pertandingan wajib tersedia sebelum submit verifikasi.';
        }
        if (blank($this->played_time)) {
            $errors['played_time'] = 'Jam pertandingan wajib tersedia sebelum submit verifikasi.';
        }
        if (! $this->ageGroup || ! $this->ageGroup->is_active || ! in_array($this->ageGroup->code, AgeGroup::COMPETITION_CODES, true)) {
            $errors['age_group_id'] = 'DSP hanya dapat disubmit untuk kelompok umur U-10, U-12, U-14, atau U-16.';
        }

        $this->loadMissing('players');
        if ($this->players->where('pivot.role', self::ROLE_STARTER)->count() !== self::REQUIRED_STARTERS) {
            $errors['starter_player_ids'] = 'DSP harus berisi tepat '.self::REQUIRED_STARTERS.' starter sebelum submit verifikasi.';
        }
        if ($this->players->where('pivot.role', self::ROLE_SUBSTITUTE)->count() > self::MAX_SUBSTITUTES) {
            $errors['substitute_player_ids'] = 'DSP maksimal berisi '.self::MAX_SUBSTITUTES.' cadangan sebelum submit verifikasi.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}

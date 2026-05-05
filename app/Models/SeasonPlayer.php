<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class SeasonPlayer extends Model
{
    protected $fillable = [
        'season_id',
        'season_club_id',
        'club_id',
        'player_id',
        'primary_age_group_id',
        'name',
        'mother_name',
        'school_name',
        'jersey_number',
        'position',
        'citizenship',
        'birth_place',
        'birth_date',
        'height_cm',
        'weight_kg',
        'dominant_foot',
        'is_captain',
        'photo_path',
        'diploma_file_path',
        'report_file_path',
        'birth_certificate_file_path',
        'family_card_file_path',
        'notes',
        'verification_status',
        'verification_notes',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'registered_age_group_ids',
        'age_registration_snapshot',
        'snapshot_source_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_captain' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'registered_age_group_ids' => 'array',
            'age_registration_snapshot' => 'array',
            'snapshot_source_updated_at' => 'datetime',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function seasonClub(): BelongsTo
    {
        return $this->belongsTo(SeasonClub::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function primaryAgeGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class, 'primary_age_group_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getPhotoFileUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->photo_path);
    }

    public function getAgeRegistrationsAttribute(): Collection
    {
        return collect($this->age_registration_snapshot ?? [])
            ->map(fn ($registration) => $this->snapshotRegistration($registration));
    }

    public function registrationForAgeGroup(?int $ageGroupId): ?Fluent
    {
        if (! $ageGroupId) {
            return $this->ageRegistrations->first();
        }

        return $this->ageRegistrations->firstWhere('age_group_id', $ageGroupId);
    }

    public function displayJerseyNumber(?int $ageGroupId = null): ?int
    {
        return $this->registrationForAgeGroup($ageGroupId)?->jersey_number ?? $this->jersey_number;
    }

    public function displayPosition(?int $ageGroupId = null): ?string
    {
        return $this->registrationForAgeGroup($ageGroupId)?->position ?? $this->position;
    }

    public function getFamilyCardFileUrlAttribute(): ?string
    {
        return $this->publicOrStoredFileUrl($this->family_card_file_path);
    }

    public function getDiplomaFileUrlAttribute(): ?string
    {
        return $this->publicOrStoredFileUrl($this->diploma_file_path);
    }

    public function getReportFileUrlAttribute(): ?string
    {
        return $this->publicOrStoredFileUrl($this->report_file_path);
    }

    public function getBirthCertificateFileUrlAttribute(): ?string
    {
        return $this->publicOrStoredFileUrl($this->birth_certificate_file_path);
    }

    public function getPublicSlugAttribute(): string
    {
        $base = $this->name ?: 'pemain';

        return $this->player_id.'-'.Str::slug($base);
    }

    private function snapshotRegistration(array $registration): Fluent
    {
        return new Fluent([
            'age_group_id' => $registration['age_group_id'] ?? null,
            'season' => $registration['season'] ?? null,
            'jersey_number' => $registration['jersey_number'] ?? null,
            'position' => $registration['position'] ?? null,
            'registration_status' => $registration['registration_status'] ?? null,
            'status_date' => filled($registration['status_date'] ?? null) ? Carbon::parse($registration['status_date']) : null,
            'notes' => $registration['notes'] ?? null,
            'is_starter' => (bool) ($registration['is_starter'] ?? false),
            'is_substitute' => (bool) ($registration['is_substitute'] ?? false),
            'ageGroup' => new Fluent([
                'name' => $registration['age_group_name'] ?? null,
                'code' => $registration['age_group_code'] ?? null,
            ]),
        ]);
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

    private function publicOrStoredFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        foreach (['public', 'local'] as $disk) {
            if (! Storage::disk($disk)->exists($normalized)) {
                continue;
            }

            if ($disk === 'public') {
                return app()->runningInConsole()
                    ? Storage::disk('public')->url($normalized)
                    : url('/storage/'.$normalized);
            }

            return match ($path) {
                $this->diploma_file_path => route('players.documents.download', ['player' => $this->player_id, 'document' => 'diploma']),
                $this->report_file_path => route('players.documents.download', ['player' => $this->player_id, 'document' => 'report']),
                $this->birth_certificate_file_path => route('players.documents.download', ['player' => $this->player_id, 'document' => 'birth-certificate']),
                $this->family_card_file_path => route('players.documents.download', ['player' => $this->player_id, 'document' => 'family-card']),
                default => null,
            };
        }

        return null;
    }
}

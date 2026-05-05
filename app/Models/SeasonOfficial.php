<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class SeasonOfficial extends Model
{
    protected $fillable = [
        'season_id',
        'season_club_id',
        'club_id',
        'official_id',
        'age_group_id',
        'name',
        'role',
        'phone',
        'email',
        'birth_place',
        'citizenship',
        'identity_number',
        'birth_date',
        'license_number',
        'license_levels',
        'photo_path',
        'license_file_path',
        'identity_file_path',
        'is_active',
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
            'is_active' => 'boolean',
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

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getPhotoFileUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->photo_path);
    }

    public function getLicenseFileUrlAttribute(): ?string
    {
        return $this->publicOrStoredFileUrl($this->license_file_path);
    }

    public function getIdentityFileUrlAttribute(): ?string
    {
        return $this->publicOrStoredFileUrl($this->identity_file_path);
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

    public function getPublicSlugAttribute(): string
    {
        $base = $this->name ?: 'ofisial';

        return $this->official_id.'-'.Str::slug($base);
    }

    private function snapshotRegistration(array $registration): Fluent
    {
        return new Fluent([
            'age_group_id' => $registration['age_group_id'] ?? null,
            'season' => $registration['season'] ?? null,
            'role' => $registration['role'] ?? null,
            'license_levels' => $registration['license_levels'] ?? null,
            'registration_status' => $registration['registration_status'] ?? null,
            'status_date' => filled($registration['status_date'] ?? null) ? Carbon::parse($registration['status_date']) : null,
            'notes' => $registration['notes'] ?? null,
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
                $this->license_file_path => route('officials.documents.download', ['official' => $this->official_id, 'document' => 'license']),
                $this->identity_file_path => route('officials.documents.download', ['official' => $this->official_id, 'document' => 'identity']),
                default => null,
            };
        }

        return null;
    }
}

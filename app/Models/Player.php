<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Player extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'club_id',
        'primary_age_group_id',
        'name',
        'mother_name',
        'school_name',
        'jersey_number',
        'position',
        'citizenship',
        'birth_place',
        'photo_path',
        'diploma_file_path',
        'report_file_path',
        'birth_certificate_file_path',
        'family_card_file_path',
        'birth_date',
        'height_cm',
        'weight_kg',
        'dominant_foot',
        'is_captain',
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
            'birth_date' => 'date',
            'is_captain' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function primaryAgeGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class, 'primary_age_group_id');
    }

    public function ageRegistrations(): HasMany
    {
        return $this->allAgeRegistrations()->forActiveSeason();
    }

    public function allAgeRegistrations(): HasMany
    {
        return $this->hasMany(PlayerAgeGroup::class)->with('ageGroup')->orderBy('age_group_id');
    }

    public function lineupLists(): BelongsToMany
    {
        return $this->belongsToMany(LineupList::class)
            ->withPivot(['role', 'display_order', 'jersey_number', 'season_player_id'])
            ->withTimestamps();
    }

    public function seasonSnapshots(): HasMany
    {
        return $this->hasMany(SeasonPlayer::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scoredGoals(): HasMany
    {
        return $this->hasMany(MatchGoal::class, 'player_id');
    }

    public function assistedGoals(): HasMany
    {
        return $this->hasMany(MatchGoal::class, 'assist_player_id');
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
        return true;
    }

    public function canClubAccessIdCard(): bool
    {
        return $this->verification_status === self::STATUS_APPROVED;
    }

    public function getPhotoFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->photo_path);
    }

    public function getDiplomaFileUrlAttribute(): ?string
    {
        return $this->documentUrl($this->diploma_file_path, 'diploma');
    }

    public function getReportFileUrlAttribute(): ?string
    {
        return $this->documentUrl($this->report_file_path, 'report');
    }

    public function getBirthCertificateFileUrlAttribute(): ?string
    {
        return $this->documentUrl($this->birth_certificate_file_path, 'birth-certificate');
    }

    public function getFamilyCardFileUrlAttribute(): ?string
    {
        return $this->documentUrl($this->family_card_file_path, 'family-card');
    }

    public function getPublicSlugAttribute(): string
    {
        $base = $this->name ?: 'pemain';

        return $this->id.'-'.Str::slug($base);
    }

    public function registrationForAgeGroup(?int $ageGroupId): ?PlayerAgeGroup
    {
        if (! $ageGroupId) {
            return null;
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

    public function starterCountForAgeGroup(int $ageGroupId): int
    {
        return $this->lineupLists
            ->where('age_group_id', $ageGroupId)
            ->where('pivot.role', LineupList::ROLE_STARTER)
            ->count();
    }

    public function substituteCountForAgeGroup(int $ageGroupId): int
    {
        return $this->lineupLists
            ->where('age_group_id', $ageGroupId)
            ->where('pivot.role', LineupList::ROLE_SUBSTITUTE)
            ->count();
    }

    public function validateForSubmission(): void
    {
        $errors = [];

        if (blank($this->name)) {
            $errors['name'] = 'Nama pemain wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->mother_name)) {
            $errors['mother_name'] = 'Nama ibu kandung pemain wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->school_name)) {
            $errors['school_name'] = 'Sekolah pemain wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->birth_place)) {
            $errors['birth_place'] = 'Tempat lahir pemain wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->birth_date)) {
            $errors['birth_date'] = 'Tanggal lahir pemain wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->citizenship)) {
            $errors['citizenship'] = 'Kewarganegaraan pemain wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->photo_path)) {
            $errors['photo_path'] = 'Foto pemain wajib diunggah sebelum submit verifikasi.';
        }
        if (blank($this->birth_certificate_file_path)) {
            $errors['birth_certificate_file_path'] = 'Akte kelahiran wajib diunggah sebelum submit verifikasi.';
        }
        if (blank($this->family_card_file_path)) {
            $errors['family_card_file_path'] = 'File KK wajib diunggah sebelum submit verifikasi.';
        }
        if (blank($this->diploma_file_path)) {
            $errors['diploma_file_path'] = 'Ijazah wajib diunggah sebelum submit verifikasi.';
        }
        if (blank($this->report_file_path)) {
            $errors['report_file_path'] = 'Rapor wajib diunggah sebelum submit verifikasi.';
        }

        $registrations = $this->ageRegistrations()->get();
        if ($registrations->isEmpty()) {
            $errors['age_registrations'] = 'Pemain harus memiliki minimal satu kelompok usia sebelum submit verifikasi.';
        } else {
            foreach ($registrations as $registration) {
                if (! $registration->ageGroup || ! $registration->ageGroup->is_active || ! in_array($registration->ageGroup->code, AgeGroup::COMPETITION_CODES, true)) {
                    $errors['age_registrations'] = 'Pemain hanya dapat disubmit untuk kelompok umur U-10, U-12, U-14, atau U-16.';
                    break;
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function syncVerificationChildrenStatus(): void
    {
        $this->ageRegistrations()->update([
            'registration_status' => $this->verification_status,
            'status_date' => $this->reviewed_at ?? $this->submitted_at ?? now(),
        ]);
    }

    private function fileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (app()->runningInConsole()) {
            return Storage::disk('public')->url($path);
        }

        return url('/storage/'.$path);
    }

    private function documentUrl(?string $path, string $document): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (! Storage::disk('local')->exists($path) && ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return route('players.documents.download', [
            'player' => $this,
            'document' => $document,
        ]);
    }
}

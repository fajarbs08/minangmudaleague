<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
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
        'registration_number',
        'jersey_number',
        'position',
        'citizenship',
        'nisn',
        'non_nisn',
        'passport_number',
        'birth_place',
        'photo_path',
        'nisn_file_path',
        'diploma_file_path',
        'report_file_path',
        'birth_certificate_file_path',
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
        return $this->hasMany(PlayerAgeGroup::class)->with('ageGroup')->orderBy('age_group_id');
    }

    public function lineupLists(): BelongsToMany
    {
        return $this->belongsToMany(LineupList::class)
            ->withPivot(['role', 'display_order'])
            ->withTimestamps();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function canBeEditedByClub(): bool
    {
        return in_array($this->verification_status, [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
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

    public function getPhotoFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->photo_path);
    }

    public function getNisnFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->nisn_file_path);
    }

    public function getDiplomaFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->diploma_file_path);
    }

    public function getReportFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->report_file_path);
    }

    public function getBirthCertificateFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->birth_certificate_file_path);
    }

    public function registrationForAgeGroup(?int $ageGroupId): ?PlayerAgeGroup
    {
        if (!$ageGroupId) {
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

        if (blank($this->name)) $errors['name'] = 'Nama pemain wajib diisi sebelum submit verifikasi.';
        if (blank($this->registration_number)) $errors['registration_number'] = 'Nomor registrasi pemain wajib diisi sebelum submit verifikasi.';
        if (blank($this->birth_place)) $errors['birth_place'] = 'Tempat lahir pemain wajib diisi sebelum submit verifikasi.';
        if (blank($this->birth_date)) $errors['birth_date'] = 'Tanggal lahir pemain wajib diisi sebelum submit verifikasi.';
        if (blank($this->citizenship)) $errors['citizenship'] = 'Kewarganegaraan pemain wajib diisi sebelum submit verifikasi.';
        if (blank($this->photo_path)) $errors['photo_path'] = 'Foto pemain wajib diunggah sebelum submit verifikasi.';
        if (blank($this->birth_certificate_file_path)) $errors['birth_certificate_file_path'] = 'Akte kelahiran wajib diunggah sebelum submit verifikasi.';
        if (blank($this->nisn_file_path) && blank($this->diploma_file_path) && blank($this->report_file_path)) {
            $errors['school_document'] = 'Minimal satu dokumen sekolah pemain wajib diunggah sebelum submit verifikasi.';
        }

        $registrations = $this->ageRegistrations()->get();
        if ($registrations->isEmpty()) {
            $errors['age_registrations'] = 'Pemain harus memiliki minimal satu kelompok usia sebelum submit verifikasi.';
        } else {
            foreach ($registrations as $registration) {
                if (blank($registration->jersey_number) || blank($registration->position)) {
                    $errors['age_registrations'] = 'Setiap kelompok usia pemain harus memiliki nomor punggung dan posisi sebelum submit verifikasi.';
                    break;
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function fileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (app()->runningInConsole()) {
            return Storage::disk('public')->url($path);
        }

        $base = request()->getSchemeAndHttpHost();

        return $base.'/storage/'.$path;
    }
}

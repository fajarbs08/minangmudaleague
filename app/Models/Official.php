<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Official extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'club_id',
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
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
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

    public function ageRegistrations(): HasMany
    {
        return $this->hasMany(OfficialAgeGroup::class)->with('ageGroup')->orderBy('age_group_id');
    }

    public function registrationForAgeGroup(?int $ageGroupId): ?OfficialAgeGroup
    {
        if (! $ageGroupId) {
            return null;
        }

        return $this->ageRegistrations->firstWhere('age_group_id', $ageGroupId);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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

    public function getLicenseFileUrlAttribute(): ?string
    {
        return $this->documentUrl($this->license_file_path, 'license');
    }

    public function getIdentityFileUrlAttribute(): ?string
    {
        return $this->documentUrl($this->identity_file_path, 'identity');
    }

    public function getPublicSlugAttribute(): string
    {
        $base = $this->name ?: 'ofisial';

        return $this->id.'-'.Str::slug($base);
    }

    public function validateForSubmission(): void
    {
        $errors = [];
        $registrations = $this->ageRegistrations()->get();

        if (blank($this->name)) {
            $errors['name'] = 'Nama official wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->role)) {
            $errors['role'] = 'Peran official wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->birth_place)) {
            $errors['birth_place'] = 'Tempat lahir official wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->birth_date)) {
            $errors['birth_date'] = 'Tanggal lahir official wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->citizenship)) {
            $errors['citizenship'] = 'Kewarganegaraan official wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->identity_number)) {
            $errors['identity_number'] = 'NIK atau nomor identitas official wajib diisi sebelum submit verifikasi.';
        }
        if (blank($this->photo_path)) {
            $errors['photo_path'] = 'Foto official wajib diunggah sebelum submit verifikasi.';
        }
        if ($this->requiresLicenseDetails($registrations) && blank($this->license_file_path) && blank($this->license_number)) {
            $errors['license_number'] = 'Isi nomor lisensi atau unggah bukti lisensi saat memilih level lisensi A, B, C, atau D.';
        }
        if (blank($this->identity_file_path)) {
            $errors['identity_file_path'] = 'Dokumen identitas official wajib diunggah sebelum submit verifikasi.';
        }

        if ($registrations->isEmpty()) {
            $errors['age_registrations'] = 'Official harus memiliki minimal satu kelompok usia sebelum submit verifikasi.';
        } elseif ($registrations->contains(fn ($registration) => ! $registration->ageGroup || ! $registration->ageGroup->is_active || ! in_array($registration->ageGroup->code, AgeGroup::COMPETITION_CODES, true))) {
            $errors['age_registrations'] = 'Official hanya dapat disubmit untuk kelompok umur U-10, U-12, U-14, atau U-16.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function requiresLicenseDetails($registrations): bool
    {
        return collect([$this->license_levels])
            ->merge(collect($registrations)->pluck('license_levels'))
            ->contains(fn ($level) => filled($level) && $level !== 'Non-Lisensi');
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

        return route('officials.documents.download', [
            'official' => $this,
            'document' => $document,
        ]);
    }
}

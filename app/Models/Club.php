<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Club extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVISION = 'revision';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'name',
        'short_name',
        'manager_name',
        'manager_title',
        'zone',
        'city',
        'founded_year',
        'logo_url',
        'deed_file_path',
        'statement_file_path',
        'address',
        'mailing_address',
        'training_address',
        'statement_age_group',
        'statement_contact',
        'statement_witness_name',
        'statement_witness_title',
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
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function officials(): HasMany
    {
        return $this->hasMany(Official::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function lineupLists(): HasMany
    {
        return $this->hasMany(LineupList::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(MatchSchedule::class, 'club_a_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(MatchSchedule::class, 'club_b_id');
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
            self::STATUS_APPROVED,
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

    public function getLogoFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->logo_url);
    }

    public function getDeedFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->deed_file_path);
    }

    public function getStatementFileUrlAttribute(): ?string
    {
        return $this->fileUrl($this->statement_file_path);
    }

    public function validateForSubmission(): void
    {
        $errors = [];

        if (blank($this->name)) $errors['name'] = 'Nama klub wajib diisi sebelum submit verifikasi.';
        if (blank($this->short_name)) $errors['short_name'] = 'Singkatan klub wajib diisi sebelum submit verifikasi.';
        if (blank($this->manager_name)) $errors['manager_name'] = 'Nama manager wajib diisi sebelum submit verifikasi.';
        if (blank($this->manager_title)) $errors['manager_title'] = 'Jabatan penanggung jawab wajib diisi sebelum submit verifikasi.';
        if (blank($this->zone)) $errors['zone'] = 'Zona klub wajib diisi sebelum submit verifikasi.';
        if (blank($this->city)) $errors['city'] = 'Kota klub wajib diisi sebelum submit verifikasi.';
        if (blank($this->founded_year)) $errors['founded_year'] = 'Tahun berdiri klub wajib diisi sebelum submit verifikasi.';
        if (blank($this->logo_url)) $errors['logo_url'] = 'Logo klub wajib diunggah sebelum submit verifikasi.';
        if (blank($this->deed_file_path)) $errors['deed_file_path'] = 'Berkas akta/deed wajib diunggah sebelum submit verifikasi.';
        if (blank($this->statement_file_path)) $errors['statement_file_path'] = 'Surat pernyataan wajib diunggah sebelum submit verifikasi.';
        if (blank($this->address)) $errors['address'] = 'Alamat klub wajib diisi sebelum submit verifikasi.';
        if (blank($this->mailing_address)) $errors['mailing_address'] = 'Alamat bersurat wajib diisi sebelum submit verifikasi.';
        if (blank($this->training_address)) $errors['training_address'] = 'Alamat latihan wajib diisi sebelum submit verifikasi.';
        if (blank($this->statement_age_group)) $errors['statement_age_group'] = 'Kelompok umur surat wajib diisi sebelum submit verifikasi.';
        if (blank($this->statement_contact)) $errors['statement_contact'] = 'Kontak surat wajib diisi sebelum submit verifikasi.';
        if (blank($this->statement_witness_name)) $errors['statement_witness_name'] = 'Nama penandatangan mengetahui wajib diisi sebelum submit verifikasi.';
        if (blank($this->statement_witness_title)) $errors['statement_witness_title'] = 'Jabatan penandatangan mengetahui wajib diisi sebelum submit verifikasi.';

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

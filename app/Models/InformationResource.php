<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InformationResource extends Model
{
    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_CLUB = 'club';

    protected $fillable = [
        'title',
        'category',
        'description',
        'file_path',
        'file_name',
        'file_mime',
        'visibility',
        'sort_order',
        'is_pinned',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public static function visibilityOptions(): array
    {
        return [
            self::VISIBILITY_PUBLIC => 'Publik',
            self::VISIBILITY_CLUB => 'Hanya Club',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFileUrlAttribute(): string
    {
        return route('information-resources.file', $this);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('information-resources.download', $this);
    }

    public function getFileSizeBytesAttribute(): ?int
    {
        return Storage::disk('public')->exists($this->file_path)
            ? Storage::disk('public')->size($this->file_path)
            : null;
    }

    public function getFileSizeLabelAttribute(): string
    {
        $bytes = $this->file_size_bytes;

        if ($bytes === null) {
            return '-';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }

    public function getBadgeLabelAttribute(): string
    {
        return match ($this->category) {
            'template' => 'Template',
            'flow' => 'Flow',
            'rules' => 'Rules',
            'manual' => 'Manual',
            default => 'File',
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'template' => 'bg-primary-subtle text-primary',
            'flow' => 'bg-success-subtle text-success',
            'rules' => 'bg-warning-subtle text-warning',
            'manual' => 'bg-info-subtle text-info',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->file_mime, 'image/');
    }

    public function getTypeLabelAttribute(): string
    {
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'PDF',
            'jpg', 'jpeg', 'png' => 'Gambar',
            'doc', 'docx' => 'Word',
            default => strtoupper($extension ?: 'FILE'),
        };
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function getIsWordAttribute(): bool
    {
        return in_array(strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)), ['doc', 'docx'], true);
    }

    public function getVisibilityLabelAttribute(): string
    {
        return self::visibilityOptions()[$this->visibility ?? self::VISIBILITY_CLUB] ?? 'Hanya Club';
    }

    public function getPublicSlugAttribute(): string
    {
        return Str::slug($this->title).'-'.$this->id;
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->creator?->name ?: 'Admin';
    }

    public function getAuthorAvatarUrlAttribute(): ?string
    {
        return $this->creator?->profile_avatar_url;
    }

    public function getAuthorInitialsAttribute(): string
    {
        return $this->creator?->profile_initials ?: 'AD';
    }
}

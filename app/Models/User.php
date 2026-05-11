<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'is_active',
        'password',
        'club_onboarding_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected bool $clubResolved = false;

    protected ?Club $clubCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'club_onboarding_seen_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->is_active !== false;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function club(): HasOne
    {
        return $this->hasOne(Club::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClubUser(): bool
    {
        return $this->role === 'club';
    }

    public function getProfileAvatarUrlAttribute(): ?string
    {
        if (! $this->isClubUser()) {
            return null;
        }

        $club = $this->linkedClub();

        return $club?->logo_file_url;
    }

    public function getProfileInitialsAttribute(): string
    {
        $source = $this->name;

        if ($this->isClubUser()) {
            $club = $this->linkedClub();
            $source = $club?->name ?: $this->name;
        }

        $initials = Str::of($source)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'U';
    }

    private function linkedClub(): ?Club
    {
        if ($this->relationLoaded('club')) {
            return $this->getRelation('club');
        }

        if (! $this->clubResolved) {
            $this->clubCache = $this->club()->first();
            $this->clubResolved = true;
        }

        return $this->clubCache;
    }
}

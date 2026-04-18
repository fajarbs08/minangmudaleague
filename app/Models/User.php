<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected bool $latestClubResolved = false;

    protected ?Club $latestClubCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'club_onboarding_seen_at' => 'datetime',
        ];
    }

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
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

        $club = $this->latestClub();

        return $club?->logo_file_url;
    }

    public function getProfileInitialsAttribute(): string
    {
        $source = $this->name;

        if ($this->isClubUser()) {
            $club = $this->latestClub();
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

    private function latestClub(): ?Club
    {
        if ($this->relationLoaded('clubs')) {
            return $this->getRelation('clubs')->sortByDesc('id')->first();
        }

        if (! $this->latestClubResolved) {
            $this->latestClubCache = $this->clubs()->latest('id')->first();
            $this->latestClubResolved = true;
        }

        return $this->latestClubCache;
    }
}

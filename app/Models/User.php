<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        if (!$this->isClubUser()) {
            return null;
        }

        $club = $this->clubs()->latest('id')->first();

        return $club?->logo_file_url;
    }

    public function getProfileInitialsAttribute(): string
    {
        $source = $this->name;

        if ($this->isClubUser()) {
            $club = $this->clubs()->latest('id')->first();
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
}

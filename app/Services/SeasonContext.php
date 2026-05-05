<?php

namespace App\Services;

use App\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SeasonContext
{
    public const SESSION_KEY = 'season_context.selected_season_id';

    private bool $resolved = false;

    private ?Season $activeSeason = null;

    private bool $selectedResolved = false;

    private ?Season $selectedSeason = null;

    public function active(): ?Season
    {
        if (! $this->hasSeasonTable()) {
            return null;
        }

        if (! $this->resolved) {
            $this->activeSeason = Season::query()
                ->active()
                ->latest('id')
                ->first()
                ?? Season::query()->latest('id')->first();

            $this->resolved = true;
        }

        return $this->activeSeason;
    }

    public function activeId(): ?int
    {
        return $this->active()?->id;
    }

    public function activeName(): ?string
    {
        return $this->active()?->name;
    }

    public function selected(): ?Season
    {
        if (! $this->selectedResolved) {
            $selectedId = $this->selectedIdFromSession();

            $this->selectedSeason = $selectedId
                ? Season::query()->find($selectedId)
                : null;

            if ($this->selectedSeason && ! $this->canView($this->selectedSeason)) {
                $this->selectedSeason = null;
                session()->forget(self::SESSION_KEY);
            }

            if (! $this->selectedSeason) {
                $this->selectedSeason = $this->active();

                if ($selectedId !== null) {
                    session()->forget(self::SESSION_KEY);
                }
            }

            $this->selectedResolved = true;
        }

        return $this->selectedSeason;
    }

    public function selectedId(): ?int
    {
        return $this->selected()?->id;
    }

    public function selectedName(): ?string
    {
        return $this->selected()?->name;
    }

    public function current(): ?Season
    {
        return $this->selected();
    }

    public function currentId(): ?int
    {
        return $this->current()?->id;
    }

    public function currentName(): ?string
    {
        return $this->current()?->name;
    }

    public function available(): Collection
    {
        if (! $this->hasSeasonTable()) {
            return collect();
        }

        return Season::query()
            ->when(
                ! auth()->user()?->isAdmin(),
                fn ($query) => $query->where('status', '!=', Season::STATUS_DRAFT)
            )
            ->orderByRaw('CASE WHEN is_active = 1 THEN 0 ELSE 1 END')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get();
    }

    public function publicVisible(): Collection
    {
        if (! $this->hasSeasonTable()) {
            return collect();
        }

        return Season::query()
            ->where('status', '!=', Season::STATUS_DRAFT)
            ->orderByRaw('CASE WHEN is_active = 1 THEN 0 ELSE 1 END')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get();
    }

    public function resolvePublic(mixed $identifier = null): ?Season
    {
        if (! $this->hasSeasonTable()) {
            return null;
        }

        if (! filled($identifier)) {
            return $this->active();
        }

        $season = Season::query()
            ->where('status', '!=', Season::STATUS_DRAFT)
            ->where(function ($query) use ($identifier) {
                $query->where('slug', (string) $identifier);

                if (is_numeric($identifier)) {
                    $query->orWhereKey((int) $identifier);
                }
            })
            ->first();

        return $season ?: $this->active();
    }

    public function isActiveSeason(?Season $season): bool
    {
        $activeSeason = $this->active();

        if (! $season || ! $activeSeason) {
            return false;
        }

        return (int) $season->id === (int) $activeSeason->id;
    }

    public function isViewingHistory(): bool
    {
        $selected = $this->selected();
        $active = $this->active();

        if (! $selected || ! $active) {
            return false;
        }

        return (int) $selected->id !== (int) $active->id;
    }

    public function select(?int $seasonId): void
    {
        if (! $this->hasSeasonTable()) {
            return;
        }

        if (! $seasonId) {
            session()->forget(self::SESSION_KEY);
            $this->forget();

            return;
        }

        $season = Season::query()->findOrFail($seasonId);

        session()->put(self::SESSION_KEY, $season->id);
        $this->forget();
    }

    public function requireActive(): Season
    {
        $season = $this->active();

        if (! $season) {
            throw new RuntimeException('Season aktif belum tersedia. Buat season aktif terlebih dahulu sebelum melanjutkan operasi kompetisi.');
        }

        return $season;
    }

    public function forget(): void
    {
        $this->resolved = false;
        $this->activeSeason = null;
        $this->selectedResolved = false;
        $this->selectedSeason = null;
    }

    private function selectedIdFromSession(): ?int
    {
        if (! app()->bound('request')) {
            return null;
        }

        if (! auth()->check()) {
            return null;
        }

        if (request()->routeIs('public.*')) {
            return null;
        }

        $value = session(self::SESSION_KEY);

        return is_numeric($value) ? (int) $value : null;
    }

    private function canView(Season $season): bool
    {
        if (auth()->user()?->isAdmin()) {
            return true;
        }

        return $season->status !== Season::STATUS_DRAFT;
    }

    private function hasSeasonTable(): bool
    {
        return Schema::hasTable('seasons');
    }
}

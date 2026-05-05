<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Services\SeasonContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeasonController extends Controller
{
    public function __construct(private SeasonContext $seasonContext) {}

    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $seasons = Season::query()
            ->withCount(['seasonClubs', 'seasonPlayers', 'seasonOfficials', 'matchSchedules', 'lineupLists'])
            ->orderByRaw('CASE WHEN is_active = 1 THEN 0 ELSE 1 END')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get();

        return view('pages.seasons.index', [
            'title' => 'Season',
            'seasons' => $seasons,
            'selectedSeasonId' => $this->seasonContext->selectedId(),
            'activeSeasonId' => $this->seasonContext->activeId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validatedData($request);

        Season::create($data + [
            'status' => Season::STATUS_DRAFT,
            'is_active' => null,
            'archived_at' => null,
        ]);

        return redirect()->route('seasons.index')->with('status', 'Season baru berhasil dibuat.');
    }

    public function update(Request $request, Season $season): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $season->update($this->validatedData($request, $season));

        return redirect()->route('seasons.index')->with('status', 'Metadata season berhasil diperbarui.');
    }

    public function activate(Season $season): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        DB::transaction(function () use ($season) {
            Season::query()
                ->where('is_active', true)
                ->whereKeyNot($season->id)
                ->update([
                    'is_active' => null,
                    'status' => Season::STATUS_ARCHIVED,
                    'archived_at' => now(),
                ]);

            $season->forceFill([
                'status' => Season::STATUS_ACTIVE,
                'is_active' => true,
                'archived_at' => null,
            ])->save();
        });

        $this->seasonContext->select($season->id);

        return redirect()->route('seasons.index')->with('status', 'Season aktif berhasil diganti ke '.$season->name.'.');
    }

    public function archive(Season $season): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($season->is_active) {
            return redirect()->route('seasons.index')->withErrors([
                'season' => 'Season aktif tidak bisa langsung diarsipkan. Aktifkan season lain terlebih dahulu.',
            ]);
        }

        $season->forceFill([
            'status' => Season::STATUS_ARCHIVED,
            'is_active' => null,
            'archived_at' => now(),
        ])->save();

        return redirect()->route('seasons.index')->with('status', 'Season berhasil diarsipkan dan kini read-only.');
    }

    public function select(Request $request): RedirectResponse
    {
        abort_unless($request->user(), 403);

        $validated = $request->validate([
            'season_id' => ['nullable', 'integer', 'exists:seasons,id'],
            'redirect_to' => ['nullable', 'string', 'max:2000'],
        ]);

        $seasonId = isset($validated['season_id']) ? (int) $validated['season_id'] : null;

        if ($seasonId && ! $request->user()->isAdmin()) {
            $season = Season::query()->findOrFail($seasonId);

            if ($season->status === Season::STATUS_DRAFT) {
                abort(403);
            }
        }

        $this->seasonContext->select($seasonId);

        return redirect($this->safeRedirectTarget($validated['redirect_to'] ?? null))
            ->with('status', 'Konteks season berhasil diperbarui.');
    }

    private function validatedData(Request $request, ?Season $season = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $season?->id);

        return $data;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base !== '' ? $base : 'season';
        $suffix = 2;

        while (Season::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = ($base !== '' ? $base : 'season').'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function safeRedirectTarget(?string $redirectTo): string
    {
        $fallback = route('dashboard.index');

        if (! filled($redirectTo)) {
            return $fallback;
        }

        if (Str::startsWith($redirectTo, ['/dashboard', '/home'])) {
            return $redirectTo;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '' && Str::startsWith($redirectTo, $appUrl.'/')) {
            return $redirectTo;
        }

        return $fallback;
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Player;
use App\Services\IdCards\IdentityCardService;
use App\Services\ImageAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PlayerController extends Controller
{
    use HandlesVerificationWorkflow;

    public function __construct(
        private IdentityCardService $identityCardService,
        private ImageAssetService $imageAssetService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = $request->string('sort')->value() ?: 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'club', 'age_group', 'position', 'jersey_number', 'verification_status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        $players = Player::query()
            ->with(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup'])
            ->whereIn('club_id', $clubs->pluck('id'))
            ->when($request->input('club_id'), fn ($query, $clubId) => $query->where('club_id', $clubId))
            ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->whereHas('ageRegistrations', fn ($inner) => $inner->where('age_group_id', $ageGroupId)))
            ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('school_name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhereHas('ageRegistrations', fn ($registration) => $registration->where('position', 'like', "%{$search}%"));
                });
            });

        match ($sort) {
            'club' => $players->orderBy(
                Club::query()->select('name')->whereColumn('clubs.id', 'players.club_id'),
                $direction
            ),
            'age_group' => $players->orderBy(
                AgeGroup::query()->select('name')->whereColumn('age_groups.id', 'players.primary_age_group_id'),
                $direction
            ),
            'position' => $request->input('age_group_id')
                ? $players->orderBy(
                    \DB::table('player_age_registrations')
                        ->select('position')
                        ->whereColumn('player_age_registrations.player_id', 'players.id')
                        ->where('player_age_registrations.age_group_id', $request->input('age_group_id'))
                        ->limit(1),
                    $direction
                )
                : $players->orderBy('position', $direction),
            'jersey_number' => $request->input('age_group_id')
                ? $players->orderBy(
                    \DB::table('player_age_registrations')
                        ->select('jersey_number')
                        ->whereColumn('player_age_registrations.player_id', 'players.id')
                        ->where('player_age_registrations.age_group_id', $request->input('age_group_id'))
                        ->limit(1),
                    $direction
                )
                : $players->orderBy('jersey_number', $direction),
            default => $players->orderBy($sort, $direction),
        };

        $players = $players
            ->paginate(10)
            ->withQueryString();

        return view('competition.players.index', [
            'title' => 'Pemain',
            'players' => $players,
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function bulkReview(Request $request)
    {
        $clubs = $this->availableClubs();
        $status = $request->input('status');

        if ($status === 'deleted') {
            abort_unless(auth()->user()->isAdmin(), 403);

            $selectedIds = $request->validate([
                'selected_ids' => ['required', 'array', 'min:1'],
                'selected_ids.*' => ['integer'],
            ])['selected_ids'];

            $models = Player::query()
                ->whereIn('club_id', $clubs->pluck('id'))
                ->whereKey($selectedIds)
                ->get();

            if ($models->isEmpty()) {
                throw ValidationException::withMessages([
                    'selected_ids' => 'Tidak ada data yang bisa diproses dari pilihan tersebut.',
                ]);
            }

            $count = $models->count();
            $models->each(function (Player $player) {
                $this->deleteStoredFiles($player);
                $player->delete();
            });

            return redirect()->back()->with('status', $count.' data pemain berhasil dihapus.');
        }

        return $this->bulkReviewSubmissions(
            $request,
            Player::query()->whereIn('club_id', $clubs->pluck('id')),
            ':count data pemain berhasil diperbarui.'
        );
    }

    public function create()
    {
        return view('competition.players.create', [
            'title' => 'Tambah Pemain',
            'player' => new Player,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function store(Request $request)
    {
        [$data, $ageRegistrations] = $this->validatedData($request);
        $this->ensureClubAccess($data['club_id']);

        $player = Player::create($data);
        $this->syncAgeRegistrations($player, $ageRegistrations);

        return redirect()->route('players.index')->with('status', 'Data pemain berhasil ditambahkan.');
    }

    public function edit(Player $player)
    {
        $this->ensureClubAccess($player->club_id);
        abort_unless(auth()->user()->isAdmin() || $player->canBeEditedByClub(), 422);
        $player->load('ageRegistrations.ageGroup');

        return view('competition.players.edit', [
            'title' => 'Edit Pemain',
            'player' => $player,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function show(Player $player)
    {
        $this->ensureClubAccess($player->club_id);
        $player->load(['club', 'primaryAgeGroup', 'reviewer', 'ageRegistrations.ageGroup', 'lineupLists']);

        return view('competition.players.show', [
            'title' => 'Detail Pemain',
            'player' => $player,
        ]);
    }

    public function downloadDocument(Player $player, string $document)
    {
        $this->ensureClubAccess($player->club_id);

        $documents = $this->downloadableDocumentFieldMap();

        abort_unless(array_key_exists($document, $documents), 404);

        $absolutePath = $this->imageAssetService->documentAbsolutePath($player->{$documents[$document]});

        abort_unless($absolutePath, 404);

        return response()->file($absolutePath);
    }

    public function publicShow(string $playerSlug)
    {
        $player = $this->resolveApprovedPublicPlayer($playerSlug);

        if ($playerSlug !== $player->public_slug) {
            return redirect()->route('public.players.show', ['playerSlug' => $player->public_slug], 301);
        }

        $player->load(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup']);

        return view('public.player-show', [
            'title' => $player->name.' - Detail Pemain',
            'player' => $player,
            'activePublicPage' => 'clubs',
            'bannerTitle' => 'Pemain '.$player->name,
            'bannerCurrent' => $player->name,
            'pageHeadingAccentWord' => 'Pemain',
            'breadcrumbItems' => [
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Pemain'],
                ['label' => $player->name],
            ],
            'seoTitle' => $player->name.' | Profil Pemain Liga Anak Piaman Laweh',
            'seoDescription' => 'Profil publik pemain '.$player->name.' dari '.($player->club?->name ?: 'Liga Anak Piaman Laweh').' dengan detail roster dan registrasi kompetisi.',
            'seoImage' => $player->photo_file_url ?: asset('og-share-card.jpg'),
            'seoType' => 'profile',
            'seoSchemaType' => 'ProfilePage',
            'seoUrl' => route('public.players.show', ['playerSlug' => $player->public_slug]),
            'seoStructuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $player->name,
                'url' => route('public.players.show', ['playerSlug' => $player->public_slug]),
                'image' => $player->photo_file_url ?: asset('og-share-card.jpg'),
                'sport' => 'Soccer',
                'memberOf' => $player->club
                    ? array_filter([
                        '@type' => 'SportsTeam',
                        'name' => $player->club->name,
                        'url' => route('public.clubs.show', ['clubSlug' => $player->club->public_slug]),
                    ], fn ($value) => filled($value))
                    : null,
            ]],
        ]);
    }

    public function publicScanShow(string $playerSlug)
    {
        $player = $this->resolveApprovedPublicPlayer($playerSlug);

        if ($playerSlug !== $player->public_slug) {
            return redirect()->route('public.players.scan', ['playerSlug' => $player->public_slug], 301);
        }

        $player->load(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup']);

        return view('public.scan-result-player', [
            'title' => 'Hasil Scan Pemain',
            'player' => $player,
            'canonicalUrl' => route('public.players.show', ['playerSlug' => $player->public_slug]),
            'robotsContent' => 'noindex,nofollow',
        ]);
    }

    public function idCard(Player $player, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureClubAccess($player->club_id);
        $player->load(['club', 'ageRegistrations.ageGroup']);

        if (! $player->registrationForAgeGroup($ageGroup->id)) {
            abort(404);
        }

        return view('competition.id-cards.preview', [
            'document' => $identityCardService->buildPlayerDocument($player->club, $ageGroup, collect([$player])),
            'backUrl' => route('players.show', $player),
            'pdfUrl' => route('players.id-card.export', [$player, $ageGroup]),
            'downloadUrl' => route('players.id-card.export', [$player, $ageGroup]).'?download=1',
        ]);
    }

    public function exportIdCard(Request $request, Player $player, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureClubAccess($player->club_id);
        $player->load(['club', 'ageRegistrations.ageGroup']);

        if (! $player->registrationForAgeGroup($ageGroup->id)) {
            abort(404);
        }

        $document = $identityCardService->buildPlayerDocument($player->club, $ageGroup, collect([$player]));

        $cacheKey = implode('|', [
            'player',
            $player->id,
            'age='.$ageGroup->id,
            'updated='.$player->updated_at?->timestamp,
            'club='.$player->club_id,
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            "id-card-pemain-{$player->id}-{$ageGroup->code}.pdf",
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function updateAgeRegistration(Player $player, AgeGroup $ageGroup, Request $request)
    {
        $this->ensureClubAccess($player->club_id);
        abort_unless(auth()->user()->isAdmin() || $player->canBeEditedByClub(), 422);

        $data = $request->validate([
            'season' => ['nullable', 'string', 'max:255'],
            'jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'position' => ['nullable', 'in:GK,CB,LB,RB,LWB,RWB,DM,CM,AM,LM,RM,LW,RW,ST,CF,SS'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_starter' => ['nullable', 'boolean'],
            'is_substitute' => ['nullable', 'boolean'],
        ]);

        $registration = $player->ageRegistrations()->where('age_group_id', $ageGroup->id)->firstOrFail();

        $isStarter = $request->boolean('is_starter');
        $isSubstitute = $request->boolean('is_substitute');

        if ($isStarter && $isSubstitute) {
            $message = 'Starter dan Cadangan tidak boleh aktif bersamaan.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->withErrors(['age_registration' => $message]);
        }

        $registration->update([
            'season' => $data['season'] ?? $registration->season,
            'jersey_number' => $data['jersey_number'] ?? $registration->jersey_number,
            'position' => $data['position'] ?? $registration->position,
            'notes' => $data['notes'] ?? $registration->notes,
            'is_starter' => $isStarter,
            'is_substitute' => $isSubstitute,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Detail kelompok usia diperbarui.']);
        }

        return redirect()->route('players.show', $player)->with('status', 'Detail kelompok usia diperbarui.');
    }

    public function destroyAgeRegistration(Player $player, AgeGroup $ageGroup)
    {
        $this->ensureClubAccess($player->club_id);
        abort_unless(auth()->user()->isAdmin() || $player->canBeEditedByClub(), 422);
        $this->ensureAgeRegistrationCanBeRemoved($player, $ageGroup);

        $player->ageRegistrations()->where('age_group_id', $ageGroup->id)->delete();

        $remaining = $player->ageRegistrations()->orderBy('age_group_id')->get();
        if ($remaining->isEmpty()) {
            $player->update([
                'primary_age_group_id' => null,
                'jersey_number' => null,
                'position' => null,
            ]);
        } else {
            $primary = $remaining->first();
            $player->update([
                'primary_age_group_id' => $primary->age_group_id,
                'jersey_number' => $primary->jersey_number,
                'position' => $primary->position,
            ]);
        }

        return redirect()->route('players.show', $player)->with('status', 'Kelompok usia berhasil dihapus.');
    }

    public function idCards(Request $request, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $clubs = $this->availableClubs();
        $clubId = (int) ($request->input('club_id') ?: $clubs->value('id'));

        abort_unless($clubId, 404);
        $this->ensureClubAccess($clubId);

        $club = Club::query()->findOrFail($clubId);
        $players = Player::query()
            ->with(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup'])
            ->where('club_id', $clubId)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $ageGroup->id))
            ->orderBy('name')
            ->get();

        return view('competition.id-cards.preview', [
            'document' => $identityCardService->buildPlayerDocument($club, $ageGroup, $players),
            'backUrl' => route('players.index', ['club_id' => $clubId]),
            'pdfUrl' => route('players.id-cards.export', ['ageGroup' => $ageGroup->id, 'club_id' => $clubId]),
            'downloadUrl' => route('players.id-cards.export', ['ageGroup' => $ageGroup->id, 'club_id' => $clubId, 'download' => 1]),
        ]);
    }

    public function exportIdCards(Request $request, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $clubs = $this->availableClubs();
        $clubId = (int) ($request->input('club_id') ?: $clubs->value('id'));

        abort_unless($clubId, 404);
        $this->ensureClubAccess($clubId);

        $club = Club::query()->findOrFail($clubId);
        $players = Player::query()
            ->with(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup'])
            ->where('club_id', $clubId)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $ageGroup->id))
            ->orderBy('name')
            ->get();

        $document = $identityCardService->buildPlayerDocument($club, $ageGroup, $players);

        $cacheKey = implode('|', [
            'players',
            'club='.$club->id,
            'age='.$ageGroup->id,
            'count='.$players->count(),
            'max='.$players->max('updated_at')?->timestamp,
            'clubUpdated='.$club->updated_at?->timestamp,
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            "id-card-pemain-{$club->id}-{$ageGroup->code}.pdf",
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function update(Request $request, Player $player)
    {
        [$data, $ageRegistrations] = $this->validatedData($request, $player);
        $this->ensureClubAccess($player->club_id);
        $this->ensureClubAccess($data['club_id']);
        abort_unless(auth()->user()->isAdmin() || $player->canBeEditedByClub(), 422);

        $this->replaceUploadedFiles($request, $player);

        $player->update($data);
        $this->syncAgeRegistrations($player, $ageRegistrations);

        return redirect()->route('players.index')->with('status', 'Data pemain berhasil diperbarui.');
    }

    public function submit(Player $player)
    {
        $this->ensureClubAccess($player->club_id);

        return $this->submitForVerification($player, 'Data pemain berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, Player $player)
    {
        $validated = $this->validateReviewPayload($request);

        return $this->reviewSubmission(
            $player,
            $validated['status'],
            $validated['verification_notes'] ?? null,
            'Status verifikasi pemain berhasil diperbarui.'
        );
    }

    public function destroy(Player $player)
    {
        $this->ensureClubAccess($player->club_id);
        abort_unless(auth()->user()->isAdmin() || $player->canBeSubmittedByClub(), 403);

        $this->deleteStoredFiles($player);

        $player->delete();

        return redirect()->route('players.index')->with('status', 'Data pemain berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Player $player = null): array
    {
        $data = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'primary_age_group_id' => ['nullable', AgeGroup::competitionExistsRule()],
            'name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
            'jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'position' => ['nullable', 'string', 'max:255'],
            'citizenship' => ['required', 'in:WNI,WNA'],
            'birth_place' => ['required', 'string', 'max:255'],
            'photo_file' => [blank($player?->photo_path) ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'diploma_file' => [blank($player?->diploma_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'report_file' => [blank($player?->report_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'birth_certificate_file' => [blank($player?->birth_certificate_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'family_card_file' => [blank($player?->family_card_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'height_cm' => ['nullable', 'integer', 'min:50', 'max:250'],
            'weight_kg' => ['nullable', 'integer', 'min:10', 'max:200'],
            'dominant_foot' => ['nullable', 'in:Kanan,Kiri,Keduanya'],
            'is_captain' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'age_registrations' => ['nullable', 'array'],
            'age_registrations.*.age_group_id' => ['required_with:age_registrations', AgeGroup::competitionExistsRule()],
            'age_registrations.*.season' => ['nullable', 'string', 'max:255'],
            'age_registrations.*.jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'age_registrations.*.position' => ['nullable', 'in:GK,CB,LB,RB,LWB,RWB,DM,CM,AM,LM,RM,LW,RW,ST,CF,SS'],
            'age_registrations.*.notes' => ['nullable', 'string', 'max:500'],
            'age_registrations.*.is_starter' => ['nullable', 'boolean'],
            'age_registrations.*.is_substitute' => ['nullable', 'boolean'],
        ], [
            'birth_certificate_file.uploaded' => 'Berkas akta kelahiran gagal diunggah.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'height_cm.min' => 'Tinggi badan minimal 50 cm.',
        ], [
            'birth_certificate_file' => 'berkas akta kelahiran',
            'height_cm' => 'tinggi badan',
        ]) + [
            'is_captain' => $request->boolean('is_captain'),
        ];

        if ($request->hasFile('photo_file')) {
            $data['photo_path'] = $this->imageAssetService->storePhoto($request->file('photo_file'), 'players/photos');
        }

        if ($request->hasFile('diploma_file')) {
            $data['diploma_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('diploma_file'), 'players/diplomas');
        }

        if ($request->hasFile('report_file')) {
            $data['report_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('report_file'), 'players/reports');
        }

        if ($request->hasFile('birth_certificate_file')) {
            $data['birth_certificate_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('birth_certificate_file'), 'players/birth-certificates');
        }

        if ($request->hasFile('family_card_file')) {
            $data['family_card_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('family_card_file'), 'players/family-cards');
        }

        unset($data['photo_file'], $data['diploma_file'], $data['report_file'], $data['birth_certificate_file'], $data['family_card_file']);

        $ageRegistrations = collect($request->input('age_registrations', []))
            ->map(fn ($registration) => [
                'age_group_id' => (int) ($registration['age_group_id'] ?? 0),
                'season' => ($registration['season'] ?? '') ?: (string) date('Y'),
                'jersey_number' => isset($registration['jersey_number']) && $registration['jersey_number'] !== '' ? (int) $registration['jersey_number'] : null,
                'position' => ($registration['position'] ?? '') ?: null,
                'notes' => $registration['notes'] ?? null,
                'is_starter' => ! empty($registration['is_starter']),
                'is_substitute' => ! empty($registration['is_substitute']),
            ])
            ->filter(fn ($registration) => $registration['age_group_id'] > 0)
            ->unique('age_group_id')
            ->values();

        if ($ageRegistrations->isEmpty() && ! empty($data['primary_age_group_id'])) {
            $ageRegistrations = collect([[
                'age_group_id' => (int) $data['primary_age_group_id'],
                'season' => (string) date('Y'),
                'jersey_number' => $data['jersey_number'] ?? null,
                'position' => $data['position'] ?? null,
                'notes' => null,
                'is_starter' => false,
                'is_substitute' => false,
            ]]);
        }

        if ($ageRegistrations->isNotEmpty()) {
            $primary = $ageRegistrations->first();
            $data['primary_age_group_id'] = $primary['age_group_id'];
            $data['jersey_number'] = $primary['jersey_number'];
            $data['position'] = $primary['position'];
        }

        if ($ageRegistrations->isEmpty()) {
            throw ValidationException::withMessages([
                'age_registrations' => 'Minimal satu kelompok usia wajib dipilih.',
            ]);
        }

        unset($data['age_registrations']);

        return [$data, $ageRegistrations];
    }

    private function replaceUploadedFiles(Request $request, Player $player): void
    {
        if ($request->hasFile('photo_file') && $player->photo_path) {
            Storage::disk('public')->delete($player->photo_path);
        }

        foreach ([
            'diploma_file' => 'diploma_file_path',
            'report_file' => 'report_file_path',
            'birth_certificate_file' => 'birth_certificate_file_path',
            'family_card_file' => 'family_card_file_path',
        ] as $input => $column) {
            if ($request->hasFile($input) && $player->{$column}) {
                $this->imageAssetService->deleteDocumentUpload($player->{$column});
            }
        }
    }

    private function deleteStoredFiles(Player $player): void
    {
        if ($player->photo_path) {
            Storage::disk('public')->delete($player->photo_path);
        }

        foreach ($this->sensitiveDocumentColumns() as $column) {
            $this->imageAssetService->deleteDocumentUpload($player->{$column});
        }
    }

    private function downloadableDocumentFieldMap(): array
    {
        return [
            'diploma' => 'diploma_file_path',
            'report' => 'report_file_path',
            'birth-certificate' => 'birth_certificate_file_path',
            'family-card' => 'family_card_file_path',
        ];
    }

    private function sensitiveDocumentColumns(): array
    {
        return array_values($this->downloadableDocumentFieldMap());
    }

    private function availableClubs()
    {
        $user = auth()->user();

        return Club::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    private function ensureClubAccess(?int $clubId): void
    {
        abort_unless($clubId, 404);

        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            Club::where('id', $clubId)->where('user_id', $user->id)->exists(),
            403
        );
    }

    private function resolveApprovedPublicPlayer(string $playerSlug): Player
    {
        preg_match('/^(\d+)(?:-|$)/', $playerSlug, $matches);
        $playerId = isset($matches[1]) ? (int) $matches[1] : 0;

        $player = Player::query()->findOrFail($playerId);

        abort_unless($player->verification_status === Player::STATUS_APPROVED, 404);

        return $player;
    }

    private function syncAgeRegistrations(Player $player, $ageRegistrations): void
    {
        $payload = collect($ageRegistrations)->mapWithKeys(function ($registration) use ($player) {
            return [
                $registration['age_group_id'] => [
                    'player_id' => $player->id,
                    'season' => $registration['season'],
                    'jersey_number' => $registration['jersey_number'],
                    'position' => $registration['position'],
                    'registration_status' => $player->verification_status,
                    'status_date' => $player->reviewed_at ?? $player->submitted_at ?? now(),
                    'notes' => $registration['notes'] ?? null,
                    'is_starter' => $registration['is_starter'] ?? false,
                    'is_substitute' => $registration['is_substitute'] ?? false,
                ],
            ];
        });

        $ageGroupIdsToDelete = $player->ageRegistrations()
            ->pluck('age_group_id')
            ->diff($payload->keys())
            ->values();

        if ($ageGroupIdsToDelete->isNotEmpty()) {
            $ageGroups = AgeGroup::query()
                ->whereIn('id', $ageGroupIdsToDelete)
                ->get()
                ->keyBy('id');

            foreach ($ageGroupIdsToDelete as $ageGroupId) {
                if ($ageGroup = $ageGroups->get($ageGroupId)) {
                    $this->ensureAgeRegistrationCanBeRemoved($player, $ageGroup);
                }
            }

            $player->ageRegistrations()->whereIn('age_group_id', $ageGroupIdsToDelete)->delete();
        }

        foreach ($payload as $ageGroupId => $registration) {
            $player->ageRegistrations()->updateOrCreate(
                ['age_group_id' => $ageGroupId],
                $registration
            );
        }
    }

    private function ensureAgeRegistrationCanBeRemoved(Player $player, AgeGroup $ageGroup): void
    {
        if ($player->lineupLists()->where('age_group_id', $ageGroup->id)->exists()) {
            throw ValidationException::withMessages([
                'age_registration' => 'Kelompok usia pemain tidak bisa dihapus karena sudah dipakai di DSP.',
            ]);
        }

        if (in_array($player->verification_status, [Player::STATUS_SUBMITTED, Player::STATUS_APPROVED], true)) {
            throw ValidationException::withMessages([
                'age_registration' => 'Kelompok usia pemain tidak bisa dihapus setelah data dikirim atau disetujui. Kembalikan dulu status verifikasinya ke revisi.',
            ]);
        }
    }
}

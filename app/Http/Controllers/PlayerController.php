<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonClub;
use App\Models\SeasonPlayer;
use App\Services\IdCards\IdentityCardService;
use App\Services\ImageAssetService;
use App\Services\SeasonContext;
use App\Services\SeasonSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PlayerController extends Controller
{
    use HandlesVerificationWorkflow;

    public function __construct(
        private IdentityCardService $identityCardService,
        private ImageAssetService $imageAssetService,
        private SeasonContext $seasonContext,
        private SeasonSnapshotService $seasonSnapshotService
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

        $isHistoryView = $this->isHistoryView();
        $clubs = $isHistoryView
            ? $this->availableSeasonClubsForHistory()
            : Club::query()
                ->select(['id', 'user_id', 'name'])
                ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
                ->orderBy('name')
                ->get();
        $clubIds = $isHistoryView ? $clubs->pluck('id')->all() : $clubs->modelKeys();

        $selectedClubId = (int) $request->input('club_id');
        $selectedAgeGroupId = (int) $request->input('age_group_id');

        if ($isHistoryView) {
            $players = SeasonPlayer::query()
                ->where('season_id', $this->seasonContext->currentId())
                ->with([
                    'seasonClub:id,season_id,club_id,name,logo_url',
                    'primaryAgeGroup:id,name',
                ])
                ->whereIn('club_id', $clubIds)
                ->when($request->input('club_id'), fn ($query, $clubId) => $query->where('club_id', $clubId))
                ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->whereJsonContains('registered_age_group_ids', (int) $ageGroupId))
                ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('school_name', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%");
                    });
                });

            match ($sort) {
                'club' => $players->orderBy(
                    SeasonClub::query()->select('name')->whereColumn('season_clubs.id', 'season_players.season_club_id'),
                    $direction
                ),
                'age_group' => $players->orderBy(
                    AgeGroup::query()->select('name')->whereColumn('age_groups.id', 'season_players.primary_age_group_id'),
                    $direction
                ),
                default => $players->orderBy($sort, $direction),
            };

            $players = $players
                ->paginate(10)
                ->withQueryString();

            $canDownloadIdCards = false;
            $idCardExportUrl = null;
        } else {
            $playerRelations = [
                'club:id,name,logo_url',
                'primaryAgeGroup:id,name',
            ];

            if ($selectedAgeGroupId > 0) {
                $playerRelations['ageRegistrations'] = fn ($query) => $query
                    ->where('age_group_id', $selectedAgeGroupId)
                    ->with('ageGroup:id,name');
            }

            $players = Player::query()
                ->with($playerRelations)
                ->whereIn('club_id', $clubIds)
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

            $idCardFilterParams = collect([
                'club_id' => $selectedClubId > 0 ? $selectedClubId : null,
                'age_group_id' => $selectedAgeGroupId ?: null,
                'status' => $request->input('status'),
                'search' => $request->input('search'),
                'limit' => $selectedAgeGroupId > 0 ? null : $this->allIdCardLimit($request),
            ])->filter(fn ($value) => filled($value))->all();

            $canDownloadIdCards = false;
            $idCardExportUrl = null;
            $clubScopeIds = $this->clubScopeIds($clubs, $selectedClubId);

            if (! empty($clubScopeIds)) {
                if ($selectedAgeGroupId > 0) {
                    $canDownloadIdCards = $this->filteredPlayerIdCardScopeQuery($request, $clubScopeIds, $selectedAgeGroupId)->exists();
                    $idCardExportUrl = route('players.id-cards.export', ['ageGroup' => $selectedAgeGroupId] + $idCardFilterParams + ['download' => 1]);
                } else {
                    $canDownloadIdCards = $this->filteredPlayerCardBaseScopeQuery($request, $clubScopeIds)->exists();
                    $idCardExportUrl = route('players.id-cards.all.export', $idCardFilterParams + ['download' => 1]);
                }
            }
        }

        return view('competition.players.index', [
            'title' => 'Pemain',
            'players' => $players,
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::competition()->get(['id', 'name']),
            'canDownloadIdCards' => $canDownloadIdCards,
            'idCardExportUrl' => $idCardExportUrl,
        ]);
    }

    public function bulkReview(Request $request)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk meninjau data pemain.');
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
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menambah pemain.');

        return view('competition.players.create', [
            'title' => 'Tambah Pemain',
            'player' => new Player,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menambah pemain.');
        [$data, $ageRegistrations] = $this->validatedData($request);
        $this->ensureClubAccess($data['club_id']);

        $player = Player::create($data);
        $this->syncAgeRegistrations($player, $ageRegistrations);
        $this->seasonSnapshotService->syncPlayerSnapshot($player->fresh(['club', 'ageRegistrations.ageGroup']));

        return redirect()->route('players.index')->with('status', 'Data pemain berhasil ditambahkan.');
    }

    public function edit(Player $player)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengubah data pemain.');
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
        if ($this->isHistoryView()) {
            $seasonPlayer = $this->authorizedSeasonPlayerQuery($player->id)
                ->with(['seasonClub', 'primaryAgeGroup'])
                ->firstOrFail();

            return view('competition.players.show', [
                'title' => 'Detail Pemain',
                'player' => $seasonPlayer,
            ]);
        }

        $this->ensureClubAccess($player->club_id);
        $player->load(['club', 'primaryAgeGroup', 'reviewer', 'ageRegistrations.ageGroup', 'lineupLists']);

        return view('competition.players.show', [
            'title' => 'Detail Pemain',
            'player' => $player,
        ]);
    }

    public function downloadDocument(Player $player, string $document)
    {
        $documents = $this->downloadableDocumentFieldMap();

        abort_unless(array_key_exists($document, $documents), 404);

        if ($this->isHistoryView()) {
            $seasonPlayer = $this->authorizedSeasonPlayerQuery($player->id)
                ->firstOrFail();

            $absolutePath = $this->imageAssetService->documentAbsolutePath($seasonPlayer->{$documents[$document]});

            abort_unless($absolutePath, 404);

            return response()->file($absolutePath);
        }

        $this->ensureClubAccess($player->club_id);
        $absolutePath = $this->imageAssetService->documentAbsolutePath($player->{$documents[$document]});

        abort_unless($absolutePath, 404);

        return response()->file($absolutePath);
    }

    public function publicShow(Request $request, string $playerSlug)
    {
        $player = $this->resolveApprovedPublicPlayer($playerSlug);
        $season = $this->selectedPublicSeason($request);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);
        $publicSeasonQuery = $this->publicSeasonQuery($season);

        if ($isHistoricalPublicSeason) {
            $player = SeasonPlayer::query()
                ->with(['seasonClub', 'primaryAgeGroup'])
                ->where('season_id', $season?->id)
                ->where('player_id', $player->id)
                ->where('verification_status', Player::STATUS_APPROVED)
                ->firstOrFail();
        } else {
            $player->load(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup']);
        }

        if ($playerSlug !== $player->public_slug) {
            return redirect()->route('public.players.show', ['playerSlug' => $player->public_slug] + $publicSeasonQuery, 301);
        }

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
            'seoTitle' => $player->name.' | Profil Pemain Liga Anak Pariaman',
            'seoDescription' => 'Profil publik pemain '.$player->name.' dari '.($isHistoricalPublicSeason ? ($player->seasonClub?->name ?: 'Liga Anak Piaman Laweh') : ($player->club?->name ?: 'Liga Anak Piaman Laweh')).' di Liga Anak Piaman Laweh, liga sepak bola anak di Pariaman, dengan detail roster dan registrasi kompetisi.',
            'seoImage' => $player->photo_file_url ?: asset('og-share-card.jpg'),
            'seoType' => 'profile',
            'seoSchemaType' => 'ProfilePage',
            'seoUrl' => route('public.players.show', ['playerSlug' => $player->public_slug] + $publicSeasonQuery),
            'selectedPublicSeason' => $season,
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => $isHistoricalPublicSeason,
            'seoStructuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $player->name,
                'url' => route('public.players.show', ['playerSlug' => $player->public_slug] + $publicSeasonQuery),
                'image' => $player->photo_file_url ?: asset('og-share-card.jpg'),
                'sport' => 'Soccer',
                'memberOf' => ($isHistoricalPublicSeason ? $player->seasonClub : $player->club)
                    ? array_filter([
                        '@type' => 'SportsTeam',
                        'name' => $isHistoricalPublicSeason ? $player->seasonClub->name : $player->club->name,
                        'url' => route('public.clubs.show', ['clubSlug' => $isHistoricalPublicSeason ? $player->seasonClub->public_slug : $player->club->public_slug] + $publicSeasonQuery),
                    ], fn ($value) => filled($value))
                    : null,
            ]],
        ]);
    }

    public function publicScanShow(Request $request, string $playerSlug)
    {
        $player = $this->resolveApprovedPublicPlayer($playerSlug);
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);

        if ($isHistoricalPublicSeason) {
            $player = SeasonPlayer::query()
                ->with(['seasonClub', 'primaryAgeGroup'])
                ->where('season_id', $season?->id)
                ->where('player_id', $player->id)
                ->where('verification_status', Player::STATUS_APPROVED)
                ->firstOrFail();
        } else {
            $player->load(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup']);
        }

        if ($playerSlug !== $player->public_slug) {
            return redirect()->route('public.players.scan', ['playerSlug' => $player->public_slug] + $publicSeasonQuery, 301);
        }

        return view('public.scan-result-player', [
            'title' => 'Hasil Scan Pemain',
            'player' => $player,
            'canonicalUrl' => route('public.players.show', ['playerSlug' => $player->public_slug] + $publicSeasonQuery),
            'robotsContent' => 'noindex,nofollow',
        ]);
    }

    public function idCard(Player $player, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $this->ensureClubAccess($player->club_id);
        $player->load(['club', 'ageRegistrations.ageGroup']);

        abort_unless(auth()->user()->isAdmin() || $player->canClubAccessIdCard(), 403);

        if ((int) $player->preferredIdCardAgeGroupId() !== (int) $ageGroup->id) {
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
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $this->ensureClubAccess($player->club_id);
        $player->load(['club', 'ageRegistrations.ageGroup']);

        abort_unless(auth()->user()->isAdmin() || $player->canClubAccessIdCard(), 403);

        if ((int) $player->preferredIdCardAgeGroupId() !== (int) $ageGroup->id) {
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
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengubah kelompok usia pemain.');
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
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menghapus kelompok usia pemain.');
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
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $clubs = $this->availableClubs();
        $selectedClubId = (int) $request->input('club_id');
        $clubScope = $this->clubScope($clubs, $selectedClubId);
        $filterParams = collect([
            'club_id' => $selectedClubId > 0 ? $selectedClubId : null,
            'age_group_id' => $ageGroup->id,
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ])->filter(fn ($value) => filled($value))->all();

        if ($clubScope->isEmpty()) {
            return redirect()
                ->route('players.index')
                ->with('status', 'Belum ada data klub atau pemain untuk membuat kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedPlayerIdCardDocument($request, $clubScope, $ageGroup, $identityCardService);

        if ($document === null) {
            return redirect()
                ->route('players.index', $filterParams)
                ->with('status', 'ID Card pemain baru tersedia setelah data disetujui admin.');
        }

        return view('competition.id-cards.preview', [
            'document' => $document,
            'backUrl' => route('players.index', $filterParams),
            'pdfUrl' => route('players.id-cards.export', ['ageGroup' => $ageGroup->id] + $filterParams),
            'downloadUrl' => route('players.id-cards.export', ['ageGroup' => $ageGroup->id] + $filterParams + ['download' => 1]),
        ]);
    }

    public function exportIdCards(Request $request, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $clubs = $this->availableClubs();
        $selectedClubId = (int) $request->input('club_id');
        $clubScope = $this->clubScope($clubs, $selectedClubId);
        $filterParams = collect([
            'club_id' => $selectedClubId > 0 ? $selectedClubId : null,
            'age_group_id' => $ageGroup->id,
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ])->filter(fn ($value) => filled($value))->all();

        if ($clubScope->isEmpty()) {
            return redirect()
                ->route('players.index')
                ->with('status', 'Belum ada data klub atau pemain untuk mencetak kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedPlayerIdCardDocument($request, $clubScope, $ageGroup, $identityCardService);

        if ($document === null) {
            return redirect()
                ->route('players.index', $filterParams)
                ->with('status', 'ID Card pemain baru tersedia setelah data disetujui admin.');
        }

        $cacheKey = implode('|', [
            'players',
            'clubs='.implode(',', $clubScope->pluck('id')->all()),
            'age='.$ageGroup->id,
            'count='.$document['count'],
            'max='.collect($document['cards'])->pluck('id')->implode(','),
            'clubUpdated='.($clubScope->max('updated_at')?->timestamp ?: '0'),
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            "id-card-pemain-{$ageGroup->code}.pdf",
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function idCardsAll(Request $request, IdentityCardService $identityCardService)
    {
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $clubs = $this->availableClubs();
        $selectedClubId = (int) $request->input('club_id');
        $clubScope = $this->clubScope($clubs, $selectedClubId);
        $limit = $this->allIdCardLimit($request);
        $filterParams = collect([
            'club_id' => $selectedClubId > 0 ? $selectedClubId : null,
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'limit' => $limit,
        ])->filter(fn ($value) => filled($value))->all();

        if ($clubScope->isEmpty()) {
            return redirect()
                ->route('players.index')
                ->with('status', 'Belum ada data klub atau pemain untuk membuat kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedPlayerIdCardDocument($request, $clubScope, null, $identityCardService, $limit);

        if ($document === null) {
            return redirect()
                ->route('players.index', $filterParams)
                ->with('status', 'Tidak ada data pemain pada filter aktif yang bisa dibuatkan ID Card.');
        }

        return view('competition.id-cards.preview', [
            'document' => $document,
            'backUrl' => route('players.index', $filterParams),
            'pdfUrl' => route('players.id-cards.all.export', $filterParams),
            'downloadUrl' => route('players.id-cards.all.export', $filterParams + ['download' => 1]),
        ]);
    }

    public function exportIdCardsAll(Request $request, IdentityCardService $identityCardService)
    {
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $clubs = $this->availableClubs();
        $selectedClubId = (int) $request->input('club_id');
        $clubScope = $this->clubScope($clubs, $selectedClubId);
        $limit = $this->allIdCardLimit($request);
        $filterParams = collect([
            'club_id' => $selectedClubId > 0 ? $selectedClubId : null,
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'limit' => $limit,
        ])->filter(fn ($value) => filled($value))->all();

        if ($clubScope->isEmpty()) {
            return redirect()
                ->route('players.index')
                ->with('status', 'Belum ada data klub atau pemain untuk mencetak kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedPlayerIdCardDocument($request, $clubScope, null, $identityCardService, $limit);

        if ($document === null) {
            return redirect()
                ->route('players.index', $filterParams)
                ->with('status', 'Tidak ada data pemain pada filter aktif yang bisa dibuatkan ID Card.');
        }

        $cacheKey = implode('|', [
            'players-all',
            'clubs='.implode(',', $clubScope->pluck('id')->all()),
            'status='.($request->input('status') ?: 'all'),
            'search='.md5((string) $request->input('search')),
            'limit='.$limit,
            'count='.$document['count'],
            'cardMax='.collect($document['cards'])->pluck('id')->implode(','),
            'clubUpdated='.($clubScope->max('updated_at')?->timestamp ?: '0'),
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            'id-card-pemain-all.pdf',
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function update(Request $request, Player $player)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengubah data pemain.');
        [$data, $ageRegistrations] = $this->validatedData($request, $player);
        $this->ensureClubAccess($player->club_id);
        $this->ensureClubAccess($data['club_id']);
        abort_unless(auth()->user()->isAdmin() || $player->canBeEditedByClub(), 422);

        $this->replaceUploadedFiles($request, $player);

        $player->update($data);
        $this->syncAgeRegistrations($player, $ageRegistrations);
        $this->seasonSnapshotService->syncPlayerSnapshot($player->fresh(['club', 'ageRegistrations.ageGroup']));

        return redirect()->route('players.show', $player)->with('status', 'Data pemain berhasil diperbarui.');
    }

    public function submit(Player $player)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengajukan verifikasi pemain.');
        $this->ensureClubAccess($player->club_id);

        return $this->submitForVerification($player, 'Data pemain berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, Player $player)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk me-review pemain.');
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
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menghapus pemain.');
        $this->ensureClubAccess($player->club_id);
        abort_unless(auth()->user()->isAdmin() || $player->canBeSubmittedByClub(), 403);

        $this->deleteStoredFiles($player);

        $player->delete();

        return redirect()->route('players.index')->with('status', 'Data pemain berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Player $player = null): array
    {
        $activeSeason = $this->seasonContext->requireActive();

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
            'photo_file' => [blank($player?->photo_path) ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'diploma_file' => [blank($player?->diploma_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:512'],
            'report_file' => [blank($player?->report_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:512'],
            'birth_certificate_file' => [blank($player?->birth_certificate_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:512'],
            'family_card_file' => [blank($player?->family_card_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:512'],
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
                'season_id' => $activeSeason->id,
                'season' => $activeSeason->name,
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
                'season_id' => $activeSeason->id,
                'season' => $activeSeason->name,
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

    private function clubScope($clubs, int $selectedClubId)
    {
        if ($selectedClubId > 0) {
            return $clubs->where('id', $selectedClubId)->values();
        }

        return $clubs->values();
    }

    private function clubScopeIds($clubs, int $selectedClubId): array
    {
        return $this->clubScope($clubs, $selectedClubId)
            ->pluck('id')
            ->all();
    }

    private function approvedPlayersQueryForClub(int $clubId)
    {
        return Player::query()
            ->where('club_id', $clubId)
            ->where('verification_status', Player::STATUS_APPROVED);
    }

    private function playerIdCardQuery(int $clubId, int $ageGroupId)
    {
        return $this->filteredPlayerIdCardScopeQuery(request(), [$clubId], $ageGroupId);
    }

    private function filteredPlayerCardBaseScopeQuery(Request $request, array $clubIds)
    {
        return Player::query()
            ->with(['club', 'primaryAgeGroup', 'ageRegistrations.ageGroup'])
            ->whereIn('club_id', $clubIds)
            ->whereHas('ageRegistrations', fn ($query) => $query->whereHas('ageGroup', fn ($ageGroupQuery) => $ageGroupQuery->competition()))
            ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('school_name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhereHas('ageRegistrations', fn ($registration) => $registration->where('position', 'like', "%{$search}%"));
                });
            })
            ->when(
                ! auth()->user()->isAdmin(),
                fn ($query) => $query->where('verification_status', Player::STATUS_APPROVED)
            )
            ->orderBy('name');
    }

    private function filteredPlayerIdCardScopeQuery(Request $request, array $clubIds, int $ageGroupId)
    {
        return $this->filteredPlayerCardBaseScopeQuery($request, $clubIds)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $ageGroupId))
            ->orderBy('name');
    }

    private function buildScopedPlayerIdCardDocument(Request $request, $clubScope, ?AgeGroup $selectedAgeGroup, IdentityCardService $identityCardService, ?int $cardLimit = null): ?array
    {
        $clubScope = collect($clubScope)->values();

        if ($clubScope->isEmpty()) {
            return null;
        }

        $clubIds = $clubScope->pluck('id')->all();
        $players = ($selectedAgeGroup
            ? $this->filteredPlayerIdCardScopeQuery($request, $clubIds, $selectedAgeGroup->id)
            : $this->filteredPlayerCardBaseScopeQuery($request, $clubIds))
            ->get();

        if ($players->isEmpty()) {
            return null;
        }

        $ageGroups = $selectedAgeGroup
            ? collect([$selectedAgeGroup])
            : AgeGroup::competition()
                ->get()
                ->filter(fn (AgeGroup $ageGroup) => $players->contains(fn (Player $player) => $player->registrationForAgeGroup($ageGroup->id)))
                ->values();

        if ($ageGroups->isEmpty()) {
            return null;
        }

        $baseClub = $clubScope->first();
        $document = $identityCardService->buildPlayerDocument($baseClub, $ageGroups->first(), collect());
        $cards = [];
        $remainingCards = $selectedAgeGroup ? null : $cardLimit;
        $isLimited = false;

        foreach ($clubScope as $club) {
            $clubPlayers = $players->where('club_id', $club->id)->values();

            foreach ($ageGroups as $ageGroup) {
                if ($remainingCards !== null && $remainingCards <= 0) {
                    $isLimited = true;

                    break 2;
                }

                $groupPlayers = $clubPlayers
                    ->filter(fn (Player $player) => $player->registrationForAgeGroup($ageGroup->id))
                    ->values();

                if ($groupPlayers->isEmpty()) {
                    continue;
                }

                if ($remainingCards !== null && $groupPlayers->count() > $remainingCards) {
                    $groupPlayers = $groupPlayers->take($remainingCards)->values();
                    $isLimited = true;
                }

                $groupDocument = $identityCardService->buildPlayerDocument($club, $ageGroup, $groupPlayers);
                $cards = array_merge($cards, $groupDocument['cards']);

                if ($remainingCards !== null) {
                    $remainingCards -= count($groupDocument['cards']);
                }
            }
        }

        if (empty($cards)) {
            return null;
        }

        if ($clubScope->count() > 1) {
            $document['club'] = [
                'name' => 'Semua Klub',
                'shortName' => 'SEMUA',
                'zone' => null,
                'logoSrc' => $document['club']['logoSrc'],
                'initials' => 'SK',
            ];
        }

        if (! $selectedAgeGroup) {
            $document['ageGroup'] = [
                'id' => null,
                'name' => 'Semua Kelompok Umur',
                'code' => 'ALL',
            ];
        }

        $document['cards'] = $cards;
        $document['count'] = count($cards);

        if ($cardLimit !== null) {
            $document['limit'] = $cardLimit;
            $document['isLimited'] = $isLimited;
        }

        return $document;
    }

    private function allIdCardLimit(Request $request): int
    {
        return $this->identityCardService->normalizeBatchExportLimit($request->input('limit'));
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

    private function availableSeasonClubsForHistory()
    {
        $user = auth()->user();

        return SeasonClub::query()
            ->select(['club_id', 'name'])
            ->where('season_id', $this->seasonContext->currentId())
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get()
            ->map(fn (SeasonClub $seasonClub) => (object) [
                'id' => $seasonClub->club_id,
                'name' => $seasonClub->name,
            ])
            ->values();
    }

    private function authorizedSeasonPlayerQuery(int $playerId)
    {
        $user = auth()->user();

        return SeasonPlayer::query()
            ->where('season_id', $this->seasonContext->currentId())
            ->where('player_id', $playerId)
            ->when(! $user->isAdmin(), fn ($query) => $query->whereHas(
                'seasonClub',
                fn ($seasonClubQuery) => $seasonClubQuery->where('user_id', $user->id)
            ));
    }

    private function isHistoryView(): bool
    {
        return $this->seasonContext->isViewingHistory();
    }

    private function selectedPublicSeason(Request $request): ?Season
    {
        return $this->seasonContext->resolvePublic($request->query('season'));
    }

    private function publicSeasonQuery(?Season $season): array
    {
        if (! $season || $this->seasonContext->isActiveSeason($season)) {
            return [];
        }

        return ['season' => $season->slug];
    }

    private function ensureWritableSeasonContext(string $message): void
    {
        if ($this->isHistoryView()) {
            abort(403, $message);
        }
    }

    private function resolveApprovedPublicPlayer(string $playerSlug): Player
    {
        preg_match('/^(\d+)(?:-|$)/', $playerSlug, $matches);
        $playerId = isset($matches[1]) ? (int) $matches[1] : 0;

        return Player::query()->visibleInActiveContext()->findOrFail($playerId);
    }

    private function syncAgeRegistrations(Player $player, $ageRegistrations): void
    {
        $activeSeason = $this->seasonContext->requireActive();
        $payload = collect($ageRegistrations)->mapWithKeys(function ($registration) use ($player) {
            return [
                $registration['age_group_id'] => [
                    'player_id' => $player->id,
                    'season_id' => $registration['season_id'],
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
            ->forSeason($activeSeason->id)
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

            $player->allAgeRegistrations()->forSeason($activeSeason->id)->whereIn('age_group_id', $ageGroupIdsToDelete)->delete();
        }

        foreach ($payload as $ageGroupId => $registration) {
            $player->allAgeRegistrations()->updateOrCreate(
                [
                    'age_group_id' => $ageGroupId,
                    'season_id' => $activeSeason->id,
                ],
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

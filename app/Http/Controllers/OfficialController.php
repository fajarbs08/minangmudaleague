<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Official;
use App\Models\Season;
use App\Models\SeasonClub;
use App\Models\SeasonOfficial;
use App\Services\IdCards\IdentityCardService;
use App\Services\ImageAssetService;
use App\Services\SeasonContext;
use App\Services\SeasonSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficialController extends Controller
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
        $allowedSorts = ['name', 'club', 'role', 'age_group', 'email', 'is_active', 'verification_status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        $selectedClubId = (int) $request->input('club_id');
        $selectedAgeGroupId = (int) $request->input('age_group_id');
        $idCardFilterParams = [];

        if ($this->isHistoryView()) {
            $officials = SeasonOfficial::query()
                ->where('season_id', $this->seasonContext->currentId())
                ->with(['seasonClub', 'ageGroup'])
                ->whereIn('club_id', $clubs->pluck('id'))
                ->when($request->input('club_id'), fn ($query, $clubId) => $query->where('club_id', $clubId))
                ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->whereJsonContains('registered_age_group_ids', (int) $ageGroupId))
                ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });

            match ($sort) {
                'club' => $officials->orderBy(
                    SeasonClub::query()->select('name')->whereColumn('season_clubs.id', 'season_officials.season_club_id'),
                    $direction
                ),
                'age_group' => $officials->orderBy(
                    AgeGroup::query()->select('name')->whereColumn('age_groups.id', 'season_officials.age_group_id'),
                    $direction
                ),
                default => $officials->orderBy($sort, $direction),
            };

            $officials = $officials
                ->paginate(10)
                ->withQueryString();

            $canDownloadIdCards = false;
            $idCardExportUrl = null;
        } else {
            $officials = Official::query()
                ->with(['club', 'ageGroup', 'ageRegistrations.ageGroup'])
                ->whereIn('club_id', $clubs->pluck('id'))
                ->when($request->input('club_id'), fn ($query, $clubId) => $query->where('club_id', $clubId))
                ->when($request->input('age_group_id'), fn ($query, $ageGroupId) => $query->whereHas('ageRegistrations', fn ($inner) => $inner->where('age_group_id', $ageGroupId)))
                ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });

            match ($sort) {
                'club' => $officials->orderBy(
                    Club::query()->select('name')->whereColumn('clubs.id', 'officials.club_id'),
                    $direction
                ),
                'age_group' => $officials->orderBy(
                    AgeGroup::query()->select('name')->whereColumn('age_groups.id', 'officials.age_group_id'),
                    $direction
                ),
                default => $officials->orderBy($sort, $direction),
            };

            $officials = $officials
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
                    $canDownloadIdCards = $this->filteredOfficialIdCardScopeQuery($request, $clubScopeIds, $selectedAgeGroupId)->exists();
                    $idCardExportUrl = route('officials.id-cards.export', ['ageGroup' => $selectedAgeGroupId] + $idCardFilterParams);
                } else {
                    $canDownloadIdCards = $this->filteredOfficialCardBaseScopeQuery($request, $clubScopeIds)->exists();
                    $idCardExportUrl = route('officials.id-cards.all.export', $idCardFilterParams);
                }
            }
        }

        return view('competition.officials.index', [
            'title' => 'Ofisial',
            'officials' => $officials,
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::competition()->get(),
            'canDownloadIdCards' => $canDownloadIdCards,
            'idCardFilterParams' => $idCardFilterParams,
            'idCardExportUrl' => $idCardExportUrl,
        ]);
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
                ->route('officials.index')
                ->with('status', 'Belum ada data klub atau ofisial untuk membuat kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedOfficialIdCardDocument($request, $clubScope, null, $identityCardService, $limit);

        if ($document === null) {
            return redirect()
                ->route('officials.index', $filterParams)
                ->with('status', 'Tidak ada data ofisial pada filter aktif yang bisa dibuatkan ID Card.');
        }

        return view('competition.id-cards.preview', [
            'document' => $document,
            'backUrl' => route('officials.index', $filterParams),
            'pdfUrl' => route('officials.id-cards.all.export', $filterParams),
            'downloadUrl' => route('officials.id-cards.all.export', $filterParams + ['download' => 1]),
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
                ->route('officials.index')
                ->with('status', 'Belum ada data klub atau ofisial untuk mencetak kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedOfficialIdCardDocument($request, $clubScope, null, $identityCardService, $limit);

        if ($document === null) {
            return redirect()
                ->route('officials.index', $filterParams)
                ->with('status', 'Tidak ada data ofisial pada filter aktif yang bisa dibuatkan ID Card.');
        }

        $cacheKey = implode('|', [
            'officials-all',
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
            'id-card-official-all.pdf',
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function bulkReview(Request $request)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk meninjau data ofisial.');
        $clubs = $this->availableClubs();
        $status = $request->input('status');

        if ($status === 'deleted') {
            abort_unless(auth()->user()->isAdmin(), 403);

            $selectedIds = $request->validate([
                'selected_ids' => ['required', 'array', 'min:1'],
                'selected_ids.*' => ['integer'],
            ])['selected_ids'];

            $models = Official::query()
                ->whereIn('club_id', $clubs->pluck('id'))
                ->whereKey($selectedIds)
                ->get();

            if ($models->isEmpty()) {
                throw ValidationException::withMessages([
                    'selected_ids' => 'Tidak ada data yang bisa diproses dari pilihan tersebut.',
                ]);
            }

            $count = $models->count();
            $models->each(function (Official $official) {
                $this->deleteStoredFiles($official);
                $official->delete();
            });

            return redirect()->back()->with('status', $count.' data ofisial berhasil dihapus.');
        }

        return $this->bulkReviewSubmissions(
            $request,
            Official::query()->whereIn('club_id', $clubs->pluck('id')),
            ':count data ofisial berhasil diperbarui.'
        );
    }

    public function create()
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menambah ofisial.');
        $clubs = $this->availableClubs();

        return view('competition.officials.create', [
            'title' => 'Tambah Ofisial',
            'official' => new Official,
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menambah ofisial.');
        [$data, $ageRegistrations] = $this->validatedData($request);
        $this->ensureClubAccess($data['club_id']);

        $official = Official::create($data);
        $this->syncAgeRegistrations($official, $ageRegistrations);
        $this->seasonSnapshotService->syncOfficialSnapshot($official->fresh(['club', 'ageRegistrations.ageGroup']));

        return redirect()
            ->route('officials.edit', $official)
            ->with('status', 'Data ofisial berhasil ditambahkan.');
    }

    public function edit(Official $official)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengubah data ofisial.');
        $this->ensureClubAccess($official->club_id);
        abort_unless(auth()->user()->isAdmin() || $official->canBeEditedByClub(), 422);
        $official->load('ageRegistrations.ageGroup');

        return view('competition.officials.edit', [
            'title' => 'Edit Ofisial',
            'official' => $official,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::competition()->get(),
        ]);
    }

    public function show(Official $official)
    {
        $this->ensureClubAccess($official->club_id);

        if ($this->isHistoryView()) {
            $seasonOfficial = SeasonOfficial::query()
                ->with(['seasonClub', 'ageGroup'])
                ->where('season_id', $this->seasonContext->currentId())
                ->where('official_id', $official->id)
                ->firstOrFail();

            return view('competition.officials.show', [
                'title' => 'Detail Ofisial',
                'official' => $seasonOfficial,
            ]);
        }

        $official->load(['club', 'reviewer', 'ageRegistrations.ageGroup']);

        return view('competition.officials.show', [
            'title' => 'Detail Ofisial',
            'official' => $official,
        ]);
    }

    public function downloadDocument(Official $official, string $document)
    {
        $this->ensureClubAccess($official->club_id);

        $documents = $this->downloadableDocumentFieldMap();

        abort_unless(array_key_exists($document, $documents), 404);

        if ($this->isHistoryView()) {
            $seasonOfficial = SeasonOfficial::query()
                ->where('season_id', $this->seasonContext->currentId())
                ->where('official_id', $official->id)
                ->firstOrFail();

            $absolutePath = $this->imageAssetService->documentAbsolutePath($seasonOfficial->{$documents[$document]});

            abort_unless($absolutePath, 404);

            return response()->file($absolutePath);
        }

        $absolutePath = $this->imageAssetService->documentAbsolutePath($official->{$documents[$document]});

        abort_unless($absolutePath, 404);

        return response()->file($absolutePath);
    }

    public function publicShow(Request $request, string $officialSlug)
    {
        $official = $this->resolveApprovedPublicOfficial($officialSlug);
        $season = $this->selectedPublicSeason($request);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);
        $publicSeasonQuery = $this->publicSeasonQuery($season);

        if ($isHistoricalPublicSeason) {
            $official = SeasonOfficial::query()
                ->with(['seasonClub', 'ageGroup'])
                ->where('season_id', $season?->id)
                ->where('official_id', $official->id)
                ->where('verification_status', Official::STATUS_APPROVED)
                ->firstOrFail();
        } else {
            $official->load(['club', 'ageGroup', 'ageRegistrations.ageGroup']);
        }

        if ($officialSlug !== $official->public_slug) {
            return redirect()->route('public.officials.show', ['officialSlug' => $official->public_slug] + $publicSeasonQuery, 301);
        }

        return view('public.official-show', [
            'title' => $official->name.' - Detail Ofisial',
            'official' => $official,
            'activePublicPage' => 'clubs',
            'bannerTitle' => 'Ofisial '.$official->name,
            'bannerCurrent' => $official->name,
            'pageHeadingAccentWord' => 'Ofisial',
            'breadcrumbItems' => [
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Ofisial'],
                ['label' => $official->name],
            ],
            'seoTitle' => $official->name.' | Profil Ofisial Liga Anak Piaman Laweh',
            'seoDescription' => 'Profil publik ofisial '.$official->name.' dari '.($isHistoricalPublicSeason ? ($official->seasonClub?->name ?: 'Liga Anak Piaman Laweh') : ($official->club?->name ?: 'Liga Anak Piaman Laweh')).' dengan detail peran dan registrasi kompetisi.',
            'seoImage' => $official->photo_file_url ?: asset('og-share-card.jpg'),
            'seoType' => 'profile',
            'seoSchemaType' => 'ProfilePage',
            'seoUrl' => route('public.officials.show', ['officialSlug' => $official->public_slug] + $publicSeasonQuery),
            'selectedPublicSeason' => $season,
            'publicSeasonQuery' => $publicSeasonQuery,
            'isHistoricalPublicSeason' => $isHistoricalPublicSeason,
            'seoStructuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $official->name,
                'jobTitle' => $official->role ?: 'Ofisial',
                'url' => route('public.officials.show', ['officialSlug' => $official->public_slug] + $publicSeasonQuery),
                'image' => $official->photo_file_url ?: asset('og-share-card.jpg'),
                'sport' => 'Soccer',
                'memberOf' => ($isHistoricalPublicSeason ? $official->seasonClub : $official->club)
                    ? array_filter([
                        '@type' => 'SportsTeam',
                        'name' => $isHistoricalPublicSeason ? $official->seasonClub->name : $official->club->name,
                        'url' => route('public.clubs.show', ['clubSlug' => $isHistoricalPublicSeason ? $official->seasonClub->public_slug : $official->club->public_slug] + $publicSeasonQuery),
                    ], fn ($value) => filled($value))
                    : null,
            ]],
        ]);
    }

    public function publicScanShow(Request $request, string $officialSlug)
    {
        $official = $this->resolveApprovedPublicOfficial($officialSlug);
        $season = $this->selectedPublicSeason($request);
        $publicSeasonQuery = $this->publicSeasonQuery($season);
        $isHistoricalPublicSeason = ! $this->seasonContext->isActiveSeason($season);

        if ($isHistoricalPublicSeason) {
            $official = SeasonOfficial::query()
                ->with(['seasonClub', 'ageGroup'])
                ->where('season_id', $season?->id)
                ->where('official_id', $official->id)
                ->where('verification_status', Official::STATUS_APPROVED)
                ->firstOrFail();
        } else {
            $official->load(['club', 'ageGroup', 'ageRegistrations.ageGroup']);
        }

        if ($officialSlug !== $official->public_slug) {
            return redirect()->route('public.officials.scan', ['officialSlug' => $official->public_slug] + $publicSeasonQuery, 301);
        }

        return view('public.scan-result-official', [
            'title' => 'Hasil Scan Ofisial',
            'official' => $official,
            'canonicalUrl' => route('public.officials.show', ['officialSlug' => $official->public_slug] + $publicSeasonQuery),
            'robotsContent' => 'noindex,nofollow',
        ]);
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
                ->route('officials.index')
                ->with('status', 'Belum ada data klub atau ofisial untuk membuat kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedOfficialIdCardDocument($request, $clubScope, $ageGroup, $identityCardService);

        if ($document === null) {
            return redirect()
                ->route('officials.index', $filterParams)
                ->with('status', 'ID Card ofisial baru tersedia setelah data disetujui admin.');
        }

        return view('competition.id-cards.preview', [
            'document' => $document,
            'backUrl' => route('officials.index', $filterParams),
            'pdfUrl' => route('officials.id-cards.export', ['ageGroup' => $ageGroup->id] + $filterParams),
            'downloadUrl' => route('officials.id-cards.export', ['ageGroup' => $ageGroup->id] + $filterParams + ['download' => 1]),
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
                ->route('officials.index')
                ->with('status', 'Belum ada data klub atau ofisial untuk mencetak kartu identitas.');
        }

        if ($selectedClubId > 0) {
            $this->ensureClubAccess($selectedClubId);
        }

        $document = $this->buildScopedOfficialIdCardDocument($request, $clubScope, $ageGroup, $identityCardService);

        if ($document === null) {
            return redirect()
                ->route('officials.index', $filterParams)
                ->with('status', 'ID Card ofisial baru tersedia setelah data disetujui admin.');
        }

        $cacheKey = implode('|', [
            'officials',
            'clubs='.implode(',', $clubScope->pluck('id')->all()),
            'age='.$ageGroup->id,
            'count='.$document['count'],
            'max='.collect($document['cards'])->pluck('id')->implode(','),
            'clubUpdated='.($clubScope->max('updated_at')?->timestamp ?: '0'),
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            "id-card-official-{$ageGroup->code}.pdf",
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function idCard(Official $official, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $this->ensureClubAccess($official->club_id);
        $official->load(['club', 'ageRegistrations.ageGroup']);

        abort_unless(auth()->user()->isAdmin() || $official->canClubAccessIdCard(), 403);

        if (! $official->registrationForAgeGroup($ageGroup->id)) {
            abort(404);
        }

        return view('competition.id-cards.preview', [
            'document' => $identityCardService->buildOfficialDocument($official->club, $ageGroup, collect([$official])),
            'backUrl' => route('officials.show', $official),
            'pdfUrl' => route('officials.id-card.export', [$official, $ageGroup]),
            'downloadUrl' => route('officials.id-card.export', [$official, $ageGroup]).'?download=1',
        ]);
    }

    public function exportIdCard(Request $request, Official $official, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureWritableSeasonContext('ID Card histori belum tersedia. Kembali ke season aktif untuk mencetak kartu.');
        $this->ensureClubAccess($official->club_id);
        $official->load(['club', 'ageRegistrations.ageGroup']);

        abort_unless(auth()->user()->isAdmin() || $official->canClubAccessIdCard(), 403);

        if (! $official->registrationForAgeGroup($ageGroup->id)) {
            abort(404);
        }

        $document = $identityCardService->buildOfficialDocument($official->club, $ageGroup, collect([$official]));

        $cacheKey = implode('|', [
            'official',
            $official->id,
            'age='.$ageGroup->id,
            'updated='.$official->updated_at?->timestamp,
            'club='.$official->club_id,
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            "id-card-official-{$official->id}-{$ageGroup->code}.pdf",
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function destroyAgeRegistration(Official $official, AgeGroup $ageGroup)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menghapus kelompok usia ofisial.');
        $this->ensureClubAccess($official->club_id);
        abort_unless(auth()->user()->isAdmin() || $official->canBeEditedByClub(), 422);
        $this->ensureAgeRegistrationCanBeRemoved($official, $ageGroup);

        $official->ageRegistrations()->where('age_group_id', $ageGroup->id)->delete();

        $remaining = $official->ageRegistrations()->orderBy('age_group_id')->get();
        if ($remaining->isEmpty()) {
            $official->update([
                'age_group_id' => null,
            ]);
        } else {
            $official->update([
                'age_group_id' => $remaining->first()->age_group_id,
            ]);
        }

        return redirect()->route('officials.show', $official)->with('status', 'Kelompok usia berhasil dihapus.');
    }

    public function update(Request $request, Official $official)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengubah data ofisial.');
        [$data, $ageRegistrations] = $this->validatedData($request, $official);
        $this->ensureClubAccess($official->club_id);
        $this->ensureClubAccess($data['club_id']);
        abort_unless(auth()->user()->isAdmin() || $official->canBeEditedByClub(), 422);

        $this->replaceUploadedFiles($request, $official);

        $official->update($data);
        $this->syncAgeRegistrations($official, $ageRegistrations);
        $this->seasonSnapshotService->syncOfficialSnapshot($official->fresh(['club', 'ageRegistrations.ageGroup']));

        return redirect()->route('officials.index')->with('status', 'Data ofisial berhasil diperbarui.');
    }

    public function submit(Official $official)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk mengajukan verifikasi ofisial.');
        $this->ensureClubAccess($official->club_id);

        return $this->submitForVerification($official, 'Data ofisial berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, Official $official)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk me-review ofisial.');
        $validated = $this->validateReviewPayload($request);

        return $this->reviewSubmission(
            $official,
            $validated['status'],
            $validated['verification_notes'] ?? null,
            'Status verifikasi ofisial berhasil diperbarui.'
        );
    }

    public function destroy(Official $official)
    {
        $this->ensureWritableSeasonContext('Kembali ke season aktif untuk menghapus ofisial.');
        $this->ensureClubAccess($official->club_id);
        abort_unless(auth()->user()->isAdmin() || $official->canBeSubmittedByClub(), 403);

        $this->deleteStoredFiles($official);

        $official->delete();

        return redirect()->route('officials.index')->with('status', 'Data ofisial berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Official $official = null): array
    {
        $activeSeason = $this->seasonContext->requireActive();

        $data = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'age_group_id' => ['nullable', AgeGroup::competitionExistsRule()],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_place' => ['required', 'string', 'max:255'],
            'citizenship' => ['required', 'in:WNI,WNA'],
            'identity_number' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'license_levels' => ['nullable', 'in:A,B,C,D,Non-Lisensi'],
            'photo_file' => [blank($official?->photo_path) ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'license_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'identity_file' => [blank($official?->identity_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'age_registrations' => ['nullable', 'array'],
            'age_registrations.*.age_group_id' => ['required_with:age_registrations', AgeGroup::competitionExistsRule()],
            'age_registrations.*.season' => ['nullable', 'string', 'max:255'],
            'age_registrations.*.role' => ['nullable', 'string', 'max:255'],
            'age_registrations.*.license_levels' => ['nullable', 'in:A,B,C,D,Non-Lisensi'],
            'age_registrations.*.notes' => ['nullable', 'string', 'max:500'],
        ], [
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('photo_file')) {
            $data['photo_path'] = $this->imageAssetService->storePhoto($request->file('photo_file'), 'officials/photos');
        }

        if ($request->hasFile('license_file')) {
            $data['license_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('license_file'), 'officials/licenses');
        }

        if ($request->hasFile('identity_file')) {
            $data['identity_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('identity_file'), 'officials/identity');
        }

        unset($data['photo_file'], $data['license_file'], $data['identity_file']);

        $ageRegistrations = collect($request->input('age_registrations', []))
            ->map(fn ($registration) => [
                'age_group_id' => (int) ($registration['age_group_id'] ?? 0),
                'season_id' => $activeSeason->id,
                'season' => $activeSeason->name,
                'role' => ($registration['role'] ?? '') ?: $data['role'],
                'license_levels' => ($registration['license_levels'] ?? '') ?: ($data['license_levels'] ?? null),
                'notes' => $registration['notes'] ?? null,
            ])
            ->filter(fn ($registration) => $registration['age_group_id'] > 0)
            ->unique('age_group_id')
            ->values();

        if ($ageRegistrations->isEmpty() && ! empty($data['age_group_id'])) {
            $ageRegistrations = collect([[
                'age_group_id' => (int) $data['age_group_id'],
                'season_id' => $activeSeason->id,
                'season' => $activeSeason->name,
                'role' => $data['role'],
                'license_levels' => $data['license_levels'],
                'notes' => null,
            ]]);
        }

        if ($ageRegistrations->isNotEmpty()) {
            $primary = $ageRegistrations->first();
            $data['age_group_id'] = $primary['age_group_id'];
        }

        if ($ageRegistrations->isEmpty()) {
            throw ValidationException::withMessages([
                'age_registrations' => 'Minimal satu kelompok usia wajib dipilih.',
            ]);
        }

        if (
            $this->requiresLicenseDetails($data['license_levels'] ?? null, $ageRegistrations)
            && blank($data['license_number'] ?? null)
            && ! $request->hasFile('license_file')
            && blank($official?->license_file_path)
        ) {
            throw ValidationException::withMessages([
                'license_number' => 'Isi nomor lisensi atau unggah bukti lisensi saat memilih level lisensi A, B, C, atau D.',
            ]);
        }

        unset($data['age_registrations']);

        return [$data, $ageRegistrations];
    }

    private function requiresLicenseDetails(?string $licenseLevel, $ageRegistrations): bool
    {
        return collect([$licenseLevel])
            ->merge(collect($ageRegistrations)->pluck('license_levels'))
            ->contains(fn ($level) => filled($level) && $level !== 'Non-Lisensi');
    }

    private function replaceUploadedFiles(Request $request, Official $official): void
    {
        if ($request->hasFile('photo_file') && $official->photo_path) {
            Storage::disk('public')->delete($official->photo_path);
        }

        foreach ([
            'license_file' => 'license_file_path',
            'identity_file' => 'identity_file_path',
        ] as $input => $column) {
            if ($request->hasFile($input) && $official->{$column}) {
                $this->imageAssetService->deleteDocumentUpload($official->{$column});
            }
        }
    }

    private function deleteStoredFiles(Official $official): void
    {
        if ($official->photo_path) {
            Storage::disk('public')->delete($official->photo_path);
        }

        foreach ($this->sensitiveDocumentColumns() as $column) {
            $this->imageAssetService->deleteDocumentUpload($official->{$column});
        }
    }

    private function downloadableDocumentFieldMap(): array
    {
        return [
            'license' => 'license_file_path',
            'identity' => 'identity_file_path',
        ];
    }

    private function sensitiveDocumentColumns(): array
    {
        return array_values($this->downloadableDocumentFieldMap());
    }

    private function syncAgeRegistrations(Official $official, $ageRegistrations): void
    {
        $activeSeason = $this->seasonContext->requireActive();
        $payload = collect($ageRegistrations)->mapWithKeys(function ($registration) use ($official) {
            return [
                $registration['age_group_id'] => [
                    'official_id' => $official->id,
                    'season_id' => $registration['season_id'],
                    'season' => $registration['season'],
                    'role' => $registration['role'],
                    'license_levels' => $registration['license_levels'],
                    'registration_status' => $official->verification_status,
                    'status_date' => $official->reviewed_at ?? $official->submitted_at ?? now(),
                    'notes' => $registration['notes'] ?? null,
                ],
            ];
        });

        $ageGroupIdsToDelete = $official->ageRegistrations()
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
                    $this->ensureAgeRegistrationCanBeRemoved($official, $ageGroup);
                }
            }

            $official->allAgeRegistrations()->forSeason($activeSeason->id)->whereIn('age_group_id', $ageGroupIdsToDelete)->delete();
        }

        foreach ($payload as $ageGroupId => $registration) {
            $official->allAgeRegistrations()->updateOrCreate(
                [
                    'age_group_id' => $ageGroupId,
                    'season_id' => $activeSeason->id,
                ],
                $registration
            );
        }
    }

    private function ensureAgeRegistrationCanBeRemoved(Official $official, AgeGroup $ageGroup): void
    {
        if (in_array($official->verification_status, [Official::STATUS_SUBMITTED, Official::STATUS_APPROVED], true)) {
            throw ValidationException::withMessages([
                'age_registration' => 'Kelompok usia ofisial tidak bisa dihapus setelah data dikirim atau disetujui. Kembalikan dulu status verifikasinya ke revisi.',
            ]);
        }

        if ($official->club?->lineupLists()->where('age_group_id', $ageGroup->id)->exists()) {
            throw ValidationException::withMessages([
                'age_registration' => 'Kelompok usia ofisial tidak bisa dihapus karena klub ini sudah memiliki DSP pada kelompok usia tersebut.',
            ]);
        }
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

    private function filteredOfficialCardBaseQuery(Request $request, int $clubId)
    {
        return $this->filteredOfficialCardBaseScopeQuery($request, [$clubId]);
    }

    private function filteredOfficialCardBaseScopeQuery(Request $request, array $clubIds)
    {
        return Official::query()
            ->with(['club', 'ageRegistrations.ageGroup'])
            ->whereIn('club_id', $clubIds)
            ->whereHas('ageRegistrations', fn ($query) => $query->whereHas('ageGroup', fn ($ageGroupQuery) => $ageGroupQuery->competition()))
            ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                ! auth()->user()->isAdmin(),
                fn ($query) => $query->where('verification_status', Official::STATUS_APPROVED)
            )
            ->orderBy('name');
    }

    private function filteredOfficialIdCardQuery(Request $request, int $clubId, int $ageGroupId)
    {
        return $this->filteredOfficialIdCardScopeQuery($request, [$clubId], $ageGroupId);
    }

    private function filteredOfficialIdCardScopeQuery(Request $request, array $clubIds, int $ageGroupId)
    {
        return $this->filteredOfficialCardBaseScopeQuery($request, $clubIds)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $ageGroupId))
            ->orderBy('name');
    }

    private function buildScopedOfficialIdCardDocument(Request $request, $clubScope, ?AgeGroup $selectedAgeGroup, IdentityCardService $identityCardService, ?int $cardLimit = null): ?array
    {
        $clubScope = collect($clubScope)->values();

        if ($clubScope->isEmpty()) {
            return null;
        }

        $clubIds = $clubScope->pluck('id')->all();
        $officials = ($selectedAgeGroup
            ? $this->filteredOfficialIdCardScopeQuery($request, $clubIds, $selectedAgeGroup->id)
            : $this->filteredOfficialCardBaseScopeQuery($request, $clubIds))
            ->get();

        if ($officials->isEmpty()) {
            return null;
        }

        $ageGroups = $selectedAgeGroup
            ? collect([$selectedAgeGroup])
            : AgeGroup::competition()
                ->get()
                ->filter(fn (AgeGroup $ageGroup) => $officials->contains(fn (Official $official) => $official->registrationForAgeGroup($ageGroup->id)))
                ->values();

        if ($ageGroups->isEmpty()) {
            return null;
        }

        $baseClub = $clubScope->first();
        $document = $identityCardService->buildOfficialDocument($baseClub, $ageGroups->first(), collect());
        $cards = [];
        $remainingCards = $selectedAgeGroup ? null : $cardLimit;
        $isLimited = false;

        foreach ($clubScope as $club) {
            $clubOfficials = $officials->where('club_id', $club->id)->values();

            foreach ($ageGroups as $ageGroup) {
                if ($remainingCards !== null && $remainingCards <= 0) {
                    $isLimited = true;

                    break 2;
                }

                $groupOfficials = $clubOfficials
                    ->filter(fn (Official $official) => $official->registrationForAgeGroup($ageGroup->id))
                    ->values();

                if ($groupOfficials->isEmpty()) {
                    continue;
                }

                if ($remainingCards !== null && $groupOfficials->count() > $remainingCards) {
                    $groupOfficials = $groupOfficials->take($remainingCards)->values();
                    $isLimited = true;
                }

                $groupDocument = $identityCardService->buildOfficialDocument($club, $ageGroup, $groupOfficials);
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

    private function resolveApprovedPublicOfficial(string $officialSlug): Official
    {
        preg_match('/^(\d+)(?:-|$)/', $officialSlug, $matches);
        $officialId = isset($matches[1]) ? (int) $matches[1] : 0;

        $official = Official::query()->findOrFail($officialId);

        abort_unless($official->verification_status === Official::STATUS_APPROVED, 404);

        return $official;
    }
}

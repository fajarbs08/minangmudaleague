<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Official;
use App\Services\IdCards\IdentityCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficialController extends Controller
{
    use HandlesVerificationWorkflow;

    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = $request->string('sort')->value() ?: 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'club', 'role', 'age_group', 'email', 'is_active', 'verification_status', 'created_at'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

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

        return view('competition.officials.index', [
            'title' => 'Official',
            'officials' => $officials,
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::orderBy('min_age')->get(),
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
            $models->each->delete();

            return redirect()->back()->with('status', $count.' data official berhasil dihapus.');
        }

        return $this->bulkReviewSubmissions(
            $request,
            Official::query()->whereIn('club_id', $clubs->pluck('id')),
            ':count data official berhasil diperbarui.'
        );
    }

    public function create()
    {
        $clubs = $this->availableClubs();

        return view('competition.officials.create', [
            'title' => 'Tambah Official',
            'official' => new Official(),
            'clubs' => $clubs,
            'ageGroups' => AgeGroup::orderBy('min_age')->get(),
        ]);
    }

    public function store(Request $request)
    {
        [$data, $ageRegistrations] = $this->validatedData($request);
        $this->ensureClubAccess($data['club_id']);

        $official = Official::create($data);
        $this->syncAgeRegistrations($official, $ageRegistrations);

        return redirect()
            ->route('officials.edit', $official)
            ->with('status', 'Data official berhasil ditambahkan.');
    }

    public function edit(Official $official)
    {
        $this->ensureClubAccess($official->club_id);
        $official->load('ageRegistrations.ageGroup');

        return view('competition.officials.edit', [
            'title' => 'Edit Official',
            'official' => $official,
            'clubs' => $this->availableClubs(),
            'ageGroups' => AgeGroup::orderBy('min_age')->get(),
        ]);
    }

    public function show(Official $official)
    {
        $this->ensureClubAccess($official->club_id);
        $official->load(['club', 'reviewer', 'ageRegistrations.ageGroup']);

        return view('competition.officials.show', [
            'title' => 'Detail Official',
            'official' => $official,
        ]);
    }

    public function scanResult(Official $official)
    {
        $official->load(['club', 'ageRegistrations.ageGroup']);

        return view('public.scan-result-official', [
            'title' => 'Hasil Scan Official',
            'official' => $official,
        ]);
    }

    public function idCards(Request $request, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $clubs = $this->availableClubs();
        $clubId = (int) ($request->input('club_id') ?: $clubs->value('id'));

        abort_unless($clubId, 404);
        $this->ensureClubAccess($clubId);

        $club = Club::query()->findOrFail($clubId);
        $officials = Official::query()
            ->with(['club', 'ageRegistrations.ageGroup'])
            ->where('club_id', $clubId)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $ageGroup->id))
            ->orderBy('name')
            ->get();

        return view('competition.id-cards.preview', [
            'document' => $identityCardService->buildOfficialDocument($club, $ageGroup, $officials),
            'backUrl' => route('officials.index', ['club_id' => $clubId]),
            'pdfUrl' => route('officials.id-cards.export', ['ageGroup' => $ageGroup->id, 'club_id' => $clubId]),
            'downloadUrl' => route('officials.id-cards.export', ['ageGroup' => $ageGroup->id, 'club_id' => $clubId, 'download' => 1]),
        ]);
    }

    public function exportIdCards(Request $request, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $clubs = $this->availableClubs();
        $clubId = (int) ($request->input('club_id') ?: $clubs->value('id'));

        abort_unless($clubId, 404);
        $this->ensureClubAccess($clubId);

        $club = Club::query()->findOrFail($clubId);
        $officials = Official::query()
            ->with(['club', 'ageRegistrations.ageGroup'])
            ->where('club_id', $clubId)
            ->whereHas('ageRegistrations', fn ($query) => $query->where('age_group_id', $ageGroup->id))
            ->orderBy('name')
            ->get();

        $document = $identityCardService->buildOfficialDocument($club, $ageGroup, $officials);

        $cacheKey = implode('|', [
            'officials',
            'club='.$club->id,
            'age='.$ageGroup->id,
            'count='.$officials->count(),
            'max='.$officials->max('updated_at')?->timestamp,
            'clubUpdated='.$club->updated_at?->timestamp,
        ]);

        return $identityCardService->pdfResponseCached(
            $document,
            "id-card-official-{$club->id}-{$ageGroup->code}.pdf",
            $cacheKey,
            $request->boolean('download')
        );
    }

    public function idCard(Official $official, AgeGroup $ageGroup, IdentityCardService $identityCardService)
    {
        $this->ensureClubAccess($official->club_id);
        $official->load(['club', 'ageRegistrations.ageGroup']);

        if (!$official->registrationForAgeGroup($ageGroup->id)) {
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
        $this->ensureClubAccess($official->club_id);
        $official->load(['club', 'ageRegistrations.ageGroup']);

        if (!$official->registrationForAgeGroup($ageGroup->id)) {
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
        $this->ensureClubAccess($official->club_id);
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
        [$data, $ageRegistrations] = $this->validatedData($request);
        $this->ensureClubAccess($official->club_id);
        $this->ensureClubAccess($data['club_id']);
        abort_unless(auth()->user()->isAdmin() || $official->canBeEditedByClub(), 422);

        $this->replaceUploadedFiles($request, $official);

        $official->update($data);
        $this->syncAgeRegistrations($official, $ageRegistrations);

        return redirect()->route('officials.index')->with('status', 'Data official berhasil diperbarui.');
    }

    public function submit(Official $official)
    {
        $this->ensureClubAccess($official->club_id);

        return $this->submitForVerification($official, 'Data official berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, Official $official)
    {
        $validated = $this->validateReviewPayload($request);

        return $this->reviewSubmission(
            $official,
            $validated['status'],
            $validated['verification_notes'] ?? null,
            'Status verifikasi official berhasil diperbarui.'
        );
    }

    public function destroy(Official $official)
    {
        $this->ensureClubAccess($official->club_id);
        abort_unless(auth()->user()->isAdmin() || $official->canBeSubmittedByClub(), 403);

        foreach (['photo_path', 'license_file_path', 'identity_file_path'] as $field) {
            if ($official->{$field}) {
                Storage::disk('public')->delete($official->{$field});
            }
        }

        $official->delete();

        return redirect()->route('officials.index')->with('status', 'Data official berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'age_group_id' => ['nullable', 'exists:age_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'citizenship' => ['nullable', 'in:WNI,WNA'],
            'identity_number' => ['nullable', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'license_levels' => ['nullable', 'string', 'max:255'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'license_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'identity_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'age_registrations' => ['nullable', 'array'],
            'age_registrations.*.age_group_id' => ['required_with:age_registrations', 'exists:age_groups,id'],
            'age_registrations.*.season' => ['nullable', 'string', 'max:255'],
            'age_registrations.*.role' => ['nullable', 'string', 'max:255'],
            'age_registrations.*.license_levels' => ['nullable', 'string', 'max:255'],
            'age_registrations.*.notes' => ['nullable', 'string', 'max:500'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('photo_file')) {
            $data['photo_path'] = $request->file('photo_file')->store('officials/photos', 'public');
        }

        if ($request->hasFile('license_file')) {
            $data['license_file_path'] = $request->file('license_file')->store('officials/licenses', 'public');
        }

        if ($request->hasFile('identity_file')) {
            $data['identity_file_path'] = $request->file('identity_file')->store('officials/identity', 'public');
        }

        unset($data['photo_file'], $data['license_file'], $data['identity_file']);

        $ageRegistrations = collect($request->input('age_registrations', []))
            ->map(fn ($registration) => [
                'age_group_id' => (int) ($registration['age_group_id'] ?? 0),
                'season' => $registration['season'] ?: (string) date('Y'),
                'role' => $registration['role'] ?: $data['role'],
                'license_levels' => $registration['license_levels'] ?: $data['license_levels'],
                'notes' => $registration['notes'] ?? null,
            ])
            ->filter(fn ($registration) => $registration['age_group_id'] > 0)
            ->unique('age_group_id')
            ->values();

        if ($ageRegistrations->isEmpty() && !empty($data['age_group_id'])) {
            $ageRegistrations = collect([[
                'age_group_id' => (int) $data['age_group_id'],
                'season' => (string) date('Y'),
                'role' => $data['role'],
                'license_levels' => $data['license_levels'],
                'notes' => null,
            ]]);
        }

        if ($ageRegistrations->isNotEmpty()) {
            $primary = $ageRegistrations->first();
            $data['age_group_id'] = $primary['age_group_id'];
        }

        unset($data['age_registrations']);

        return [$data, $ageRegistrations];
    }

    private function replaceUploadedFiles(Request $request, Official $official): void
    {
        $map = [
            'photo_file' => 'photo_path',
            'license_file' => 'license_file_path',
            'identity_file' => 'identity_file_path',
        ];

        foreach ($map as $input => $column) {
            if ($request->hasFile($input) && $official->{$column}) {
                Storage::disk('public')->delete($official->{$column});
            }
        }
    }

    private function syncAgeRegistrations(Official $official, $ageRegistrations): void
    {
        $payload = collect($ageRegistrations)->mapWithKeys(function ($registration) use ($official) {
            return [
                $registration['age_group_id'] => [
                    'official_id' => $official->id,
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

            $official->ageRegistrations()->whereIn('age_group_id', $ageGroupIdsToDelete)->delete();
        }

        foreach ($payload as $ageGroupId => $registration) {
            $official->ageRegistrations()->updateOrCreate(
                ['age_group_id' => $ageGroupId],
                $registration
            );
        }
    }

    private function ensureAgeRegistrationCanBeRemoved(Official $official, AgeGroup $ageGroup): void
    {
        if (in_array($official->verification_status, [Official::STATUS_SUBMITTED, Official::STATUS_APPROVED], true)) {
            throw ValidationException::withMessages([
                'age_registration' => 'Kelompok usia official tidak bisa dihapus setelah data dikirim atau disetujui. Kembalikan dulu status verifikasinya ke revisi.',
            ]);
        }

        if ($official->club?->lineupLists()->where('age_group_id', $ageGroup->id)->exists()) {
            throw ValidationException::withMessages([
                'age_registration' => 'Kelompok usia official tidak bisa dihapus karena klub ini sudah memiliki DSP pada kelompok usia tersebut.',
            ]);
        }
    }

    private function availableClubs()
    {
        $user = auth()->user();

        return Club::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    private function ensureClubAccess(int $clubId): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            Club::where('id', $clubId)->where('user_id', $user->id)->exists(),
            403
        );
    }

}

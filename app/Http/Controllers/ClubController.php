<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\Club;
use App\Models\Official;
use App\Models\Player;
use App\Services\ClubLogoService;
use App\Services\ImageAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClubController extends Controller
{
    use HandlesVerificationWorkflow;

    public function __construct(
        private ClubLogoService $clubLogoService,
        private ImageAssetService $imageAssetService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = $request->string('sort')->value() ?: 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'zone', 'officials_count', 'players_count', 'lineup_lists_count', 'verification_status', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->withCount(['officials', 'players', 'lineupLists'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), fn ($query, $status) => $query->where('verification_status', $status))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('competition.clubs.index', [
            'title' => 'Klub',
            'clubs' => $clubs,
        ]);
    }

    public function bulkReview(Request $request)
    {
        $status = $request->input('status');

        if ($status === 'deleted') {
            abort_unless(auth()->user()->isAdmin(), 403);

            $selectedIds = $request->validate([
                'selected_ids' => ['required', 'array', 'min:1'],
                'selected_ids.*' => ['integer'],
            ])['selected_ids'];

            $clubs = Club::query()->whereKey($selectedIds)->get();

            if ($clubs->isEmpty()) {
                throw ValidationException::withMessages([
                    'selected_ids' => 'Tidak ada data yang bisa diproses dari pilihan tersebut.',
                ]);
            }

            $clubs->each(function (Club $club) {
                $this->deleteStoredFiles($club);

                $club->delete();
            });

            return redirect()->back()->with('status', $clubs->count().' data klub berhasil dihapus.');
        }

        return $this->bulkReviewSubmissions(
            $request,
            Club::query(),
            ':count data klub berhasil diperbarui.'
        );
    }

    public function create()
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $user->isClubUser(), 403);

        if ($user->isClubUser()) {
            abort_if($user->clubs()->exists(), 403);
        }

        return view('competition.clubs.create', [
            'title' => 'Tambah Klub',
            'club' => new Club,
        ]);
    }

    public function statementTemplate()
    {
        $path = public_path('documents/surat_pernyataan_liga_piaman_laweh_final.pdf');

        abort_unless(is_file($path), 404);

        return response()->download($path, 'surat_pernyataan_liga_piaman_laweh_final.pdf');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $user->isClubUser(), 403);

        if ($user->isClubUser()) {
            abort_if($user->clubs()->exists(), 403);
        }

        $data = $this->validatedData($request);

        if ($user->isClubUser()) {
            $data['user_id'] = $user->id;
        }

        Club::create($data);

        return redirect()->route('clubs.index')->with('status', 'Data klub berhasil ditambahkan.');
    }

    public function edit(Club $club)
    {
        $this->authorizeClub($club);
        abort_unless(auth()->user()->isAdmin() || $club->canBeEditedByClub(), 422);

        return view('competition.clubs.edit', [
            'title' => 'Edit Klub',
            'club' => $club,
        ]);
    }

    public function show(Club $club)
    {
        $this->authorizeClub($club);

        return view('competition.clubs.show', [
            'title' => 'Detail Klub',
            'club' => $club->loadCount(['officials', 'players', 'lineupLists']),
        ]);
    }

    public function downloadStatement(Club $club)
    {
        $this->authorizeClub($club);

        $absolutePath = $this->imageAssetService->documentAbsolutePath($club->statement_file_path);

        abort_unless($absolutePath, 404);

        return response()->file($absolutePath);
    }

    public function update(Request $request, Club $club)
    {
        $this->authorizeClub($club);
        abort_unless(auth()->user()->isAdmin() || $club->canBeEditedByClub(), 422);

        $oldLogoPath = $club->logo_url;
        $oldStatementPath = $club->statement_file_path;
        $data = $this->validatedData($request, $club);

        $club->update($data);

        if (array_key_exists('logo_url', $data) && $oldLogoPath && ! str_starts_with($oldLogoPath, 'http')) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        if (array_key_exists('statement_file_path', $data) && $oldStatementPath) {
            $this->imageAssetService->deleteDocumentUpload($oldStatementPath);
        }

        return redirect()->route('clubs.index')->with('status', 'Data klub berhasil diperbarui.');
    }

    public function submit(Club $club)
    {
        $this->authorizeClub($club);

        return $this->submitForVerification($club, 'Data klub berhasil dikirim untuk verifikasi.');
    }

    public function review(Request $request, Club $club)
    {
        $validated = $this->validateReviewPayload($request);

        return $this->reviewSubmission(
            $club,
            $validated['status'],
            $validated['verification_notes'] ?? null,
            'Status verifikasi klub berhasil diperbarui.'
        );
    }

    public function destroy(Club $club)
    {
        $this->authorizeClub($club);
        abort_unless(auth()->user()->isAdmin() || $club->canBeSubmittedByClub(), 403);

        $this->deleteStoredFiles($club);

        $club->delete();

        return redirect()->route('clubs.index')->with('status', 'Data klub berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Club $club = null): array
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:50'],
            'manager_name' => ['required', 'string', 'max:255'],
            'manager_title' => ['required', 'string', 'max:255'],
            'zone' => ['required', 'string', 'max:255'],
            'founded_year' => ['required', 'integer', 'min:1900', 'max:'.date('Y')],
            'logo_file' => [blank($club?->logo_url) ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512', 'dimensions:min_width=120,min_height=120'],
            'statement_file' => [blank($club?->statement_file_path) ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:4096'],
            'address' => ['required', 'string'],
            'training_address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo_file')) {
            $data['logo_url'] = $this->clubLogoService->storeNormalized($request->file('logo_file'));
        }

        if ($request->hasFile('statement_file')) {
            $data['statement_file_path'] = $this->imageAssetService->storeDocumentUpload($request->file('statement_file'), 'clubs/statements');
        }

        unset($data['logo_file'], $data['statement_file']);

        return $data;
    }

    private function authorizeClub(Club $club): void
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $club->user_id === $user->id, 403);
    }

    private function deleteStoredFiles(Club $club): void
    {
        if ($club->logo_url && ! str_starts_with($club->logo_url, 'http')) {
            Storage::disk('public')->delete($club->logo_url);
        }

        $this->imageAssetService->deleteDocumentUpload($club->statement_file_path);

        $club->loadMissing([
            'players:id,club_id,photo_path,diploma_file_path,report_file_path,birth_certificate_file_path,family_card_file_path',
            'officials:id,club_id,photo_path,license_file_path,identity_file_path',
        ]);

        $club->players->each(function (Player $player) {
            if ($player->photo_path) {
                Storage::disk('public')->delete($player->photo_path);
            }

            foreach (['diploma_file_path', 'report_file_path', 'birth_certificate_file_path', 'family_card_file_path'] as $field) {
                $this->imageAssetService->deleteDocumentUpload($player->{$field});
            }
        });

        $club->officials->each(function (Official $official) {
            if ($official->photo_path) {
                Storage::disk('public')->delete($official->photo_path);
            }

            foreach (['license_file_path', 'identity_file_path'] as $field) {
                $this->imageAssetService->deleteDocumentUpload($official->{$field});
            }
        });
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesVerificationWorkflow;
use App\Models\Club;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClubController extends Controller
{
    use HandlesVerificationWorkflow;

    public function index(Request $request)
    {
        $user = auth()->user();
        $sort = $request->string('sort')->value() ?: 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'zone', 'city', 'officials_count', 'players_count', 'lineup_lists_count', 'verification_status', 'created_at'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $clubs = Club::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->withCount(['officials', 'players', 'lineupLists'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
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
                if ($club->logo_url && !str_starts_with($club->logo_url, 'http')) {
                    Storage::disk('public')->delete($club->logo_url);
                }

                foreach (['deed_file_path', 'statement_file_path'] as $field) {
                    if ($club->{$field} && !str_starts_with($club->{$field}, 'http')) {
                        Storage::disk('public')->delete($club->{$field});
                    }
                }

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
            'club' => new Club(),
        ]);
    }

    public function statementTemplate(Request $request)
    {
        $user = $request->user();
        $club = null;

        if ($request->filled('club_id')) {
            $club = Club::query()->findOrFail($request->integer('club_id'));

            if ($user->isClubUser()) {
                abort_unless($club->user_id === $user->id, 403);
            }
        } elseif ($user->isClubUser()) {
            $club = $user->clubs()->latest()->first();
        }

        $pdf = Pdf::loadView('competition.clubs.statement-template-pdf', [
            'club' => $club,
            'user' => $user,
            'today' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'template-surat-pernyataan-klub.pdf';

        if ($club?->name) {
            $slug = str($club->name)->slug('-')->value();
            $filename = "template-surat-pernyataan-{$slug}.pdf";
        }

        return $pdf->stream($filename);
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

    public function update(Request $request, Club $club)
    {
        $this->authorizeClub($club);
        abort_unless(auth()->user()->isAdmin() || $club->canBeEditedByClub(), 422);

        if ($request->hasFile('logo_file') && $club->logo_url && !str_starts_with($club->logo_url, 'http')) {
            Storage::disk('public')->delete($club->logo_url);
        }

        foreach ([
            'deed_file' => 'deed_file_path',
            'statement_file' => 'statement_file_path',
        ] as $input => $column) {
            if ($request->hasFile($input) && $club->{$column} && !str_starts_with($club->{$column}, 'http')) {
                Storage::disk('public')->delete($club->{$column});
            }
        }

        $club->update($this->validatedData($request));

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

        if ($club->logo_url && !str_starts_with($club->logo_url, 'http')) {
            Storage::disk('public')->delete($club->logo_url);
        }

        foreach (['deed_file_path', 'statement_file_path'] as $field) {
            if ($club->{$field} && !str_starts_with($club->{$field}, 'http')) {
                Storage::disk('public')->delete($club->{$field});
            }
        }

        $club->delete();

        return redirect()->route('clubs.index')->with('status', 'Data klub berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'manager_name' => ['required', 'string', 'max:255'],
            'manager_title' => ['required', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'deed_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:4096'],
            'statement_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:4096'],
            'address' => ['nullable', 'string'],
            'mailing_address' => ['required', 'string'],
            'training_address' => ['nullable', 'string'],
            'statement_age_group' => ['required', 'string', 'max:50'],
            'statement_contact' => ['required', 'string', 'max:100'],
            'statement_witness_name' => ['required', 'string', 'max:255'],
            'statement_witness_title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo_file')) {
            $data['logo_url'] = $request->file('logo_file')->store('club-logos', 'public');
        }

        if ($request->hasFile('deed_file')) {
            $data['deed_file_path'] = $request->file('deed_file')->store('clubs/deeds', 'public');
        }

        if ($request->hasFile('statement_file')) {
            $data['statement_file_path'] = $request->file('statement_file')->store('clubs/statements', 'public');
        }

        unset($data['logo_file'], $data['deed_file'], $data['statement_file']);

        return $data;
    }

    private function authorizeClub(Club $club): void
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $club->user_id === $user->id, 403);
    }
}

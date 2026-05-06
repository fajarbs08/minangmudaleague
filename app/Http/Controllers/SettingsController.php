<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function createClubAccount(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $clubAccounts = User::query()
            ->where('role', 'club')
            ->withCount('clubs')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'is_active', 'created_at']);

        $nextSequence = $this->nextClubAccountSequence();

        return view('pages.club-accounts.create', [
            'title' => 'Buat Akun Club',
            'clubAccounts' => $clubAccounts,
            'nextSequence' => $nextSequence,
            'currentYear' => now()->year,
        ]);
    }

    public function editClubAccount(Request $request, User $clubAccount): View
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless($clubAccount->role === 'club', 404);

        return view('pages.club-accounts.edit', [
            'title' => 'Edit Akun Club',
            'clubAccount' => $clubAccount->loadCount('clubs'),
            'currentYear' => now()->year,
        ]);
    }

    public function show(Request $request): View
    {
        $user = $request->user();
        $club = $user->isClubUser() ? $user->clubs()->latest()->first() : null;

        return view('pages.settings', [
            'user' => $user,
            'club' => $club,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $club = $user->isClubUser() ? $user->clubs()->latest()->first() : null;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'club_name' => [$club ? 'required' : 'nullable', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($club) {
            $club->update([
                'name' => $validated['club_name'],
                'manager_name' => $validated['manager_name'] ?? null,
                'zone' => $validated['zone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        }

        return redirect()
            ->route('settings.show')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('settings.show')
            ->with('status', 'Password berhasil diperbarui.');
    }

    public function storeClubAccount(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'generated_password' => ['nullable', 'string', 'regex:/^LAPLplw\d{4}[A-Z0-9]{4}$/'],
            'is_active' => ['required', 'boolean'],
        ]);

        $sequence = $this->nextClubAccountSequence();
        $email = $this->generateClubAccountEmail($validated['account_name'], $sequence);
        $password = $validated['generated_password'] ?? $this->generateClubAccountPassword();

        User::create([
            'name' => $validated['account_name'],
            'email' => $email,
            'role' => 'club',
            'is_active' => $request->boolean('is_active'),
            'password' => $password,
        ]);

        return redirect()
            ->route('club-accounts.create')
            ->with('status', "Akun club berhasil dibuat. Email: {$email} | Password: {$password}");
    }

    public function updateClubAccount(Request $request, User $clubAccount): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless($clubAccount->role === 'club', 404);

        $validated = $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'account_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($clubAccount->id)],
            'generated_password' => ['nullable', 'string', 'min:8', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $clubAccount->name = $validated['account_name'];
        $clubAccount->email = $validated['account_email'];
        $clubAccount->is_active = $request->boolean('is_active');

        if (!empty($validated['generated_password'])) {
            $clubAccount->password = $validated['generated_password'];
        }

        $clubAccount->save();

        return redirect()
            ->route('club-accounts.create')
            ->with('status', 'Akun club berhasil diperbarui.');
    }

    public function updateClubAccountStatus(Request $request, User $clubAccount): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless($clubAccount->role === 'club', 404);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $clubAccount->forceFill([
            'is_active' => (bool) $validated['is_active'],
        ])->save();

        $statusLabel = $clubAccount->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('club-accounts.create')
            ->with('status', "Akun club berhasil {$statusLabel}.");
    }

    public function destroyClubAccount(Request $request, User $clubAccount): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless($clubAccount->role === 'club', 404);

        if ($clubAccount->clubs()->exists()) {
            return redirect()
                ->route('club-accounts.create')
                ->withErrors(['club_account' => 'Akun club yang sudah memiliki data club tidak bisa dihapus.']);
        }

        $clubAccount->delete();

        return redirect()
            ->route('club-accounts.create')
            ->with('status', 'Akun club berhasil dihapus.');
    }

    private function nextClubAccountSequence(): string
    {
        return str_pad((string) (User::query()->where('role', 'club')->count() + 1), 3, '0', STR_PAD_LEFT);
    }

    private function generateClubAccountEmail(string $accountName, string $sequence): string
    {
        $slug = Str::of($accountName)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->trim()
            ->value();

        $slug = $slug !== '' ? $slug : 'club'.$sequence;
        $domain = 'ligaanakpiamanlaweh.com';
        $email = "{$slug}@{$domain}";

        if (User::query()->where('email', $email)->exists()) {
            $email = "{$slug}{$sequence}@{$domain}";
        }

        return $email;
    }

    private function generateClubAccountPassword(): string
    {
        return 'LAPLplw'.now()->year.Str::upper(Str::random(4));
    }
}

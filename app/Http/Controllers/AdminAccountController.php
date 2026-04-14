<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAccountController extends Controller
{
    public function index()
    {
        return view('pages.admin-accounts.index', [
            'title' => 'Akun Admin',
            'admins' => User::query()->where('role', 'admin')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'admin',
        ]);

        return redirect()->route('admin-accounts.index')->with('status', 'Akun admin berhasil dibuat.');
    }

    public function edit(User $adminAccount)
    {
        abort_unless($adminAccount->role === 'admin', 404);

        return view('pages.admin-accounts.edit', [
            'title' => 'Edit Akun Admin',
            'adminAccount' => $adminAccount,
        ]);
    }

    public function update(Request $request, User $adminAccount)
    {
        abort_unless($adminAccount->role === 'admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($adminAccount->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $adminAccount->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ?: $adminAccount->password,
            'role' => 'admin',
        ]);

        return redirect()->route('admin-accounts.index')->with('status', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(User $adminAccount)
    {
        abort_unless($adminAccount->role === 'admin', 404);
        abort_if($adminAccount->id === auth()->id(), 403);

        $adminAccount->delete();

        return redirect()->route('admin-accounts.index')->with('status', 'Akun admin berhasil dihapus.');
    }
}

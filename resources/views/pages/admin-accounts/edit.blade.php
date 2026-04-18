@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Edit Akun Admin</h4>
        <p class="text-muted mb-0">{{ $adminAccount->email }}</p>
    </div>
    <a href="{{ route('admin-accounts.index') }}" class="btn btn-light">Kembali</a>
</div>

@include('competition.partials.flash')

<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-1">Detail Akun</h4>
        <p class="text-muted mb-4">Ubah data admin. Kosongkan password jika tidak ingin mengganti.</p>
        <form method="POST" action="{{ route('admin-accounts.update', $adminAccount) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $adminAccount->name) }}" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $adminAccount->email) }}" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control">
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin-accounts.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

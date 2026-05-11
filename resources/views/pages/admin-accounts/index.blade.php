@extends('layouts.vertical', ['title' => $title])

@section('content')
@php
    $editingAdminId = old('_edit_admin_id');
    $editingAdmin = $editingAdminId ? $admins->firstWhere('id', (int) $editingAdminId) : null;
    $hasAdminEditErrors = $editingAdminId && ($errors->has('name') || $errors->has('email') || $errors->has('password'));
    $hasAdminCreateErrors = ! $editingAdminId && ($errors->has('name') || $errors->has('email') || $errors->has('password'));
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Akun Admin</h4>
        <p class="text-muted mb-0">Kelola akun admin untuk akses penuh ke sistem.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
        Tambah Akun Admin
    </button>
</div>

@include('competition.partials.flash')

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title mb-1">Daftar Admin</h4>
                        <p class="text-muted mb-0">Kelola akun admin yang aktif.</p>
                    </div>
                </div>

                <div class="table-responsive competition-table-wrap admin-accounts-table">
                    <table class="table competition-table align-middle mb-0">
                        <thead>
                            <tr>
                                @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Nama', 'defaultSort' => 'name', 'defaultDirection' => 'asc'])
                                @include('competition.partials.sortable-th', ['key' => 'email', 'label' => 'Email', 'defaultSort' => 'name', 'defaultDirection' => 'asc'])
                                @include('competition.partials.sortable-th', ['key' => 'created_at', 'label' => 'Dibuat', 'defaultSort' => 'name', 'defaultDirection' => 'asc'])
                                <th class="text-start text-md-end admin-accounts-action-cell">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($admins as $admin)
                                <tr>
                                    <td class="fw-semibold">{{ $admin->name }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>{{ $admin->created_at?->format('d M Y') ?: '-' }}</td>
                                    <td class="text-start text-md-end admin-accounts-action-cell">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                                <span>Aksi</span>
                                                <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                    <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'square-pen',
                                                    'label' => 'Edit',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#editAdminModal',
                                                        'data-action' => route('admin-accounts.update', $admin),
                                                        'data-id' => $admin->id,
                                                        'data-name' => $admin->name,
                                                        'data-email' => $admin->email,
                                                    ],
                                                ])
                                                @if ($admin->id !== auth()->id())
                                                    <div class="dropdown-divider"></div>
                                                    @include('competition.partials.action-item', [
                                                        'icon' => 'trash-2',
                                                        'label' => 'Hapus',
                                                        'class' => 'text-danger js-delete-admin',
                                                        'attributes' => [
                                                            'data-bs-toggle' => 'modal',
                                                            'data-bs-target' => '#deleteAdminModal',
                                                            'data-action' => route('admin-accounts.destroy', $admin),
                                                            'data-name' => $admin->name,
                                                        ],
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada akun admin.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAdminModalLabel">Tambah Akun Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin-accounts.store') }}" id="create-admin-form">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-4">Buat akun admin baru dengan email dan password.</p>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ $hasAdminCreateErrors ? old('name') : '' }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $hasAdminCreateErrors ? old('email') : '' }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" data-password-input required>
                            <button class="btn btn-light admin-password-toggle" type="button" data-password-toggle aria-label="Tampilkan password" aria-pressed="false">
                                <span data-password-hidden-icon>
                                    <i data-lucide="eye" class="fs-16"></i>
                                </span>
                                <span class="d-none" data-password-visible-icon>
                                    <i data-lucide="eye-off" class="fs-16"></i>
                                </span>
                            </button>
                        </div>
                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAdminModalLabel">Edit Akun Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ $editingAdmin ? route('admin-accounts.update', $editingAdmin) : '#' }}" id="edit-admin-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_edit_admin_id" id="edit-admin-id" value="{{ $editingAdmin?->id }}">
                <div class="modal-body">
                    <p class="text-muted mb-4">Ubah data admin. Kosongkan password jika tidak ingin mengganti.</p>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" id="edit-admin-name" class="form-control" value="{{ old('name', $editingAdmin?->name) }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit-admin-email" class="form-control" value="{{ old('email', $editingAdmin?->email) }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password" id="edit-admin-password" class="form-control" data-password-input>
                            <button class="btn btn-light admin-password-toggle" type="button" data-password-toggle aria-label="Tampilkan password" aria-pressed="false">
                                <span data-password-hidden-icon>
                                    <i data-lucide="eye" class="fs-16"></i>
                                </span>
                                <span class="d-none" data-password-visible-icon>
                                    <i data-lucide="eye-off" class="fs-16"></i>
                                </span>
                            </button>
                        </div>
                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteAdminModal',
    'title' => 'Hapus Akun Admin',
    'formId' => 'delete-admin-form',
    'messagePrefix' => 'Akun',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
    'nameClass' => 'js-delete-admin-name',
])

<style>
    .table-responsive.competition-table-wrap.admin-accounts-table,
    .table-responsive.competition-table-wrap.admin-accounts-table.dropdown-open {
        overflow: visible !important;
        padding-right: 0.75rem;
    }

    .admin-accounts-table .table {
        min-width: 720px;
    }

    .admin-accounts-action-cell {
        min-width: 150px;
        width: 150px;
    }

    .admin-accounts-action-cell .competition-action-menu {
        min-width: 220px;
        max-width: calc(100vw - 1.5rem);
    }

    .admin-password-toggle {
        width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
    (() => {
        const createModal = document.getElementById('createAdminModal');
        const editModal = document.getElementById('editAdminModal');
        const deleteModal = document.getElementById('deleteAdminModal');

        if (editModal) {
            editModal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;
                const action = trigger.getAttribute('data-action');
                const id = trigger.getAttribute('data-id');
                const name = trigger.getAttribute('data-name');
                const email = trigger.getAttribute('data-email');
                const form = editModal.querySelector('#edit-admin-form');
                const idInput = editModal.querySelector('#edit-admin-id');
                const nameInput = editModal.querySelector('#edit-admin-name');
                const emailInput = editModal.querySelector('#edit-admin-email');
                const passwordInput = editModal.querySelector('#edit-admin-password');
                if (form && action) form.setAttribute('action', action);
                if (idInput) idInput.value = id || '';
                if (nameInput) nameInput.value = name || '';
                if (emailInput) emailInput.value = email || '';
                if (passwordInput) passwordInput.value = '';
            });
        }

        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;
                const action = trigger.getAttribute('data-action');
                const name = trigger.getAttribute('data-name');
                const form = deleteModal.querySelector('#delete-admin-form');
                const nameNode = deleteModal.querySelector('.js-delete-admin-name');
                if (form && action) form.setAttribute('action', action);
                if (nameNode) nameNode.textContent = name || '-';
            });
        }

        @if ($hasAdminCreateErrors)
            if (createModal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(createModal).show();
            }
        @endif

        @if ($hasAdminEditErrors)
            if (editModal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(editModal).show();
            }
        @endif
    })();
</script>

<script>
    (() => {
        const forms = [document.getElementById('create-admin-form'), document.getElementById('edit-admin-form')].filter(Boolean);
        if (!forms.length) return;

        const setMessage = (input, message) => {
            input.setCustomValidity(message);
        };

        forms.forEach((form) => {
            form.querySelectorAll('input[required], input[type="email"]').forEach((input) => {
                input.addEventListener('invalid', () => {
                    if (input.validity.valueMissing) {
                        setMessage(input, 'Kolom ini wajib diisi.');
                    } else if (input.validity.typeMismatch) {
                        setMessage(input, 'Masukkan alamat email yang valid.');
                    } else if (input.validity.tooShort) {
                        setMessage(input, `Minimal ${input.minLength} karakter.`);
                    } else {
                        setMessage(input, '');
                    }
                });

                input.addEventListener('input', () => {
                    setMessage(input, '');
                });
            });

            form.querySelectorAll('[data-password-toggle]').forEach((button) => {
                const input = button.closest('.input-group')?.querySelector('[data-password-input]');
                const hiddenIcon = button.querySelector('[data-password-hidden-icon]');
                const visibleIcon = button.querySelector('[data-password-visible-icon]');
                if (!input || !hiddenIcon || !visibleIcon) return;

                button.addEventListener('click', () => {
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                    button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                    hiddenIcon.classList.toggle('d-none', isHidden);
                    visibleIcon.classList.toggle('d-none', !isHidden);
                    input.focus();
                });
            });
        });
    })();
</script>
@endsection

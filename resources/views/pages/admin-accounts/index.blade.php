@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Akun Admin</h4>
        <p class="text-muted mb-0">Kelola akun admin untuk akses penuh ke sistem.</p>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h4 class="card-title mb-1">Tambah Akun Admin</h4>
                <p class="text-muted mb-4">Buat akun admin baru dengan email dan password.</p>
                <form method="POST" action="{{ route('admin-accounts.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
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
                    <button type="submit" class="btn btn-primary w-100">Simpan Akun</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
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
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Dibuat</th>
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
                                                <span>Tindakan</span>
                                                <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                    <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                                @include('competition.partials.action-item', [
                                                    'href' => route('admin-accounts.edit', $admin),
                                                    'icon' => 'square-pen',
                                                    'label' => 'Edit',
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
        const modal = document.getElementById('deleteAdminModal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) return;
            const action = trigger.getAttribute('data-action');
            const name = trigger.getAttribute('data-name');
            const form = modal.querySelector('#delete-admin-form');
            const nameNode = modal.querySelector('.js-delete-admin-name');
            if (form && action) form.setAttribute('action', action);
            if (nameNode) nameNode.textContent = name || '-';
        });
    })();
</script>

<script>
    (() => {
        const form = document.querySelector('form[action="{{ route('admin-accounts.store') }}"]');
        if (!form) return;

        const setMessage = (input, message) => {
            input.setCustomValidity(message);
        };

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
    })();
</script>
@endsection

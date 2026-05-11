@extends('layouts.vertical', ['title' => $title])

@section('content')
@php
    $editingClubAccountId = old('_edit_club_account_id', $editingClubAccount?->id);
    $resolvedEditingClubAccount = $editingClubAccountId
        ? ($clubAccounts->firstWhere('id', (int) $editingClubAccountId) ?? $editingClubAccount)
        : null;
    $hasEditClubAccountErrors = $editingClubAccountId && ($errors->has('account_name') || $errors->has('account_email') || $errors->has('generated_password') || $errors->has('is_active'));
    $hasCreateClubAccountErrors = ! $editingClubAccountId && ($errors->has('account_name') || $errors->has('generated_password') || $errors->has('is_active'));
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Akun Club</h4>
        <p class="text-muted mb-0">Admin membuat akun login club, lalu kredensial itu diberikan ke pendaftar untuk mengisi data club sendiri.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClubAccountModal">
            Buat Akun Club
        </button>
        <a
            href="{{ route('clubs.index') }}"
            class="btn btn-light d-inline-flex align-items-center justify-content-center club-data-shortcut"
            aria-label="Data Klub"
            title="Data Klub"
        >
            <i data-lucide="shield" class="fs-14"></i>
        </a>
    </div>
</div>

@include('competition.partials.flash')

<div class="modal fade" id="createClubAccountModal" tabindex="-1" aria-labelledby="createClubAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="createClubAccountModalLabel">Generator Akun Club</h5>
                    <p class="text-muted mb-0 mt-1">Format default: `namaclub@ligaanakpiamanlaweh.com` dan `LAPLplw{{ $currentYear }}XXXX`.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('club-accounts.store') }}" id="club-account-form">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="account-name" class="form-label">Nama Akun</label>
                            <input
                                class="form-control"
                                id="account-name"
                                name="account_name"
                                type="text"
                                value="{{ old('account_name') }}"
                                placeholder="Contoh: Netral United"
                                required
                            >
                            <div class="competition-table-meta mt-2">Dipakai untuk nama user.</div>
                            @error('account_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="account-email" class="form-label">Email Login</label>
                            <div class="input-group">
                                <input
                                    class="form-control"
                                    id="account-email"
                                    type="text"
                                    value=""
                                    placeholder="otomatis"
                                    readonly
                                >
                                <button class="btn btn-light js-copy-trigger" type="button" data-copy-target="#account-email">Copy</button>
                            </div>
                            <div class="competition-table-meta mt-2">Terisi otomatis ke domain `@ligaanakpiamanlaweh.com`.</div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="account-password" class="form-label">Password</label>
                            <div class="input-group">
                                <input
                                    class="form-control"
                                    id="account-password"
                                    type="text"
                                    value=""
                                    placeholder="LAPLplw{{ $currentYear }}AB12"
                                    readonly
                                >
                                <button class="btn btn-light js-copy-trigger" type="button" data-copy-target="#account-password">Copy</button>
                            </div>
                            <input id="generated-password" name="generated_password" type="hidden" value="{{ old('generated_password') }}">
                            <div class="competition-table-meta mt-2">Suffix 4 karakter dibuat acak, tidak berurutan.</div>
                            @error('generated_password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input name="is_active" type="hidden" value="0">
                                <input class="form-check-input" id="account-is-active" name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1')>
                                <label class="form-check-label fw-semibold" for="account-is-active">Akun aktif</label>
                            </div>
                            <div class="competition-table-meta mt-2">Matikan jika akun dibuat dulu tetapi belum boleh login.</div>
                            @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" id="generate-account-button">Generate Ulang</button>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Akun</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editClubAccountModal" tabindex="-1" aria-labelledby="editClubAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="editClubAccountModalLabel">Edit Akun Club</h5>
                    <p class="text-muted mb-0 mt-1">Ubah nama, email login, status akun, atau generate password baru.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ $resolvedEditingClubAccount ? route('club-accounts.update', $resolvedEditingClubAccount) : '#' }}" id="edit-club-account-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_edit_club_account_id" id="edit-club-account-id" value="{{ $resolvedEditingClubAccount?->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label for="edit-account-name" class="form-label">Nama Akun</label>
                            <input
                                class="form-control"
                                id="edit-account-name"
                                name="account_name"
                                type="text"
                                value="{{ old('account_name', $resolvedEditingClubAccount?->name) }}"
                                placeholder="Contoh: Netral United"
                                required
                            >
                            @error('account_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="edit-account-email" class="form-label">Email Login</label>
                            <input
                                class="form-control"
                                id="edit-account-email"
                                name="account_email"
                                type="email"
                                value="{{ old('account_email', $resolvedEditingClubAccount?->email) }}"
                                required
                            >
                            @error('account_email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="edit-account-password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input
                                    class="form-control"
                                    id="edit-account-password"
                                    name="generated_password"
                                    type="text"
                                    value="{{ old('generated_password') }}"
                                    placeholder="Kosongkan bila tidak diubah"
                                >
                                <button class="btn btn-light js-copy-trigger" type="button" data-copy-target="#edit-account-password">Copy</button>
                            </div>
                            <div class="competition-table-meta mt-2">Isi manual atau klik generate untuk reset password.</div>
                            @error('generated_password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="edit-generate-password-button" class="form-label">Aksi Password</label>
                            <button type="button" class="btn btn-light w-100" id="edit-generate-password-button">Generate Password Baru</button>
                            <div class="competition-table-meta mt-2">Buat password acak baru dengan format standar akun club.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input name="is_active" type="hidden" value="0">
                                <input class="form-check-input" id="edit-account-is-active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $resolvedEditingClubAccount?->is_active ? '1' : '0') === '1')>
                                <label class="form-check-label fw-semibold" for="edit-account-is-active">Akun aktif</label>
                            </div>
                            <div class="competition-table-meta mt-2">Nonaktifkan jika akun club sementara tidak boleh login ke dashboard.</div>
                            @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="card-title mb-1">Daftar Akun Club</h4>
            <p class="text-muted mb-0">Akun yang sudah dibuat admin dan siap diberikan ke pendaftar.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ $clubAccounts->count() }} akun</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle mb-0">
                <thead>
                    <tr>
                        @include('competition.partials.sortable-th', ['key' => 'name', 'label' => 'Nama', 'defaultSort' => 'created_at', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'email', 'label' => 'Email', 'defaultSort' => 'created_at', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'is_active', 'label' => 'Status', 'defaultSort' => 'created_at', 'defaultDirection' => 'desc'])
                        @include('competition.partials.sortable-th', ['key' => 'created_at', 'label' => 'Dibuat', 'defaultSort' => 'created_at', 'defaultDirection' => 'desc'])
                        <th class="text-end competition-table-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clubAccounts as $account)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $account->name }}</div>
                                <div class="competition-table-meta">{{ $account->club_count ? 'Sudah punya data club' : 'Belum punya data club' }}</div>
                            </td>
                            <td>{{ $account->email }}</td>
                            <td>
                                <span class="badge club-account-status-badge {{ $account->is_active ? 'is-active' : 'is-inactive' }}">
                                    {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>{{ $account->created_at?->format('d M Y H:i') ?: '-' }}</td>
                            <td class="text-end competition-table-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                        <span>Aksi</span>
                                        <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                        <div class="competition-action-section">
                                            <div class="competition-action-label px-2 pb-2">Akun</div>
                                            @include('competition.partials.action-item', [
                                                'href' => route('club-accounts.edit', $account),
                                                'icon' => 'square-pen',
                                                'label' => 'Edit',
                                            ])
                                            <form id="club-account-status-form-{{ $account->id }}" method="POST" action="{{ route('club-accounts.status', $account) }}" class="d-none">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $account->is_active ? 0 : 1 }}">
                                            </form>
                                            @include('competition.partials.action-item', [
                                                'icon' => $account->is_active ? 'user-x' : 'badge-check',
                                                'label' => $account->is_active ? 'Nonaktifkan' : 'Aktifkan',
                                                'class' => $account->is_active ? 'text-warning' : 'text-success',
                                                'type' => 'submit',
                                                'attributes' => [
                                                    'form' => 'club-account-status-form-'.$account->id,
                                                ],
                                            ])
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="competition-action-section">
                                            <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                            @if ($account->club_count > 0)
                                                <span
                                                    class="d-block"
                                                    tabindex="0"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="left"
                                                    title="Akun sudah terhubung ke data klub. Nonaktifkan akun jika akses login perlu dihentikan."
                                                >
                                                    @include('competition.partials.action-item', [
                                                        'icon' => 'trash-2',
                                                        'label' => 'Hapus',
                                                        'class' => 'text-danger js-delete-club-account',
                                                        'disabled' => true,
                                                    ])
                                                </span>
                                            @else
                                                @include('competition.partials.action-item', [
                                                    'icon' => 'trash-2',
                                                    'label' => 'Hapus',
                                                    'class' => 'text-danger js-delete-club-account',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#deleteClubAccountModal',
                                                        'data-action' => route('club-accounts.destroy', $account),
                                                        'data-name' => $account->name,
                                                    ],
                                                ])
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="competition-table-empty">Belum ada akun club.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('competition.partials.delete-modal', [
    'modalId' => 'deleteClubAccountModal',
    'title' => 'Hapus Akun Club',
    'formId' => 'delete-club-account-form',
    'nameClass' => 'js-delete-club-account-name',
    'messagePrefix' => 'Akun',
    'messageSuffix' => 'akan dihapus. Tindakan ini tidak bisa dibatalkan.',
])

<style>
    .club-data-shortcut {
        width: 42px;
        padding-inline: 0;
    }
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const createModal = document.getElementById('createClubAccountModal');
    const editModal = document.getElementById('editClubAccountModal');
    const deleteModal = document.getElementById('deleteClubAccountModal');
    const nameInput = document.getElementById('account-name');
    const emailInput = document.getElementById('account-email');
    const passwordInput = document.getElementById('account-password');
    const generatedPasswordInput = document.getElementById('generated-password');
    const generateButton = document.getElementById('generate-account-button');
    const editPasswordInput = document.getElementById('edit-account-password');
    const editGenerateButton = document.getElementById('edit-generate-password-button');
    const copyButtons = document.querySelectorAll('.js-copy-trigger');
    const currentYear = '{{ $currentYear }}';
    const oldGeneratedPassword = @json(old('generated_password'));
    const shouldOpenCreateModal = @json($hasCreateClubAccountErrors);
    const shouldOpenEditModal = @json((bool) $resolvedEditingClubAccount || (bool) $hasEditClubAccountErrors);

    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const action = trigger.getAttribute('data-action');
            const name = trigger.getAttribute('data-name');
            const form = deleteModal.querySelector('#delete-club-account-form');
            const nameNode = deleteModal.querySelector('.js-delete-club-account-name');

            if (form && action) form.setAttribute('action', action);
            if (nameNode) nameNode.textContent = name || '-';
        });
    }

    if (createModal && window.bootstrap?.Modal && shouldOpenCreateModal) {
        window.bootstrap.Modal.getOrCreateInstance(createModal).show();
    }

    if (editModal && window.bootstrap?.Modal && shouldOpenEditModal) {
        window.bootstrap.Modal.getOrCreateInstance(editModal).show();
    }

    if (!nameInput || !emailInput || !passwordInput || !generatedPasswordInput || !generateButton) {
        return;
    }

    const slugify = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '')
        .trim();

    const randomSuffix = () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return Array.from({ length: 4 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    };

    const generateCredentials = () => {
        const slug = slugify(nameInput.value) || 'club{{ $nextSequence }}';
        const password = `LAPLplw${currentYear}${randomSuffix()}`;

        emailInput.value = `${slug}@ligaanakpiamanlaweh.com`;
        passwordInput.value = password;
        generatedPasswordInput.value = password;
    };

    generateButton.addEventListener('click', generateCredentials);
    nameInput.addEventListener('input', generateCredentials);

    if (oldGeneratedPassword) {
        const slug = slugify(nameInput.value) || 'club{{ $nextSequence }}';
        emailInput.value = `${slug}@ligaanakpiamanlaweh.com`;
        passwordInput.value = oldGeneratedPassword;
        generatedPasswordInput.value = oldGeneratedPassword;
    } else {
        generateCredentials();
    }

    if (editPasswordInput && editGenerateButton) {
        editGenerateButton.addEventListener('click', function () {
            editPasswordInput.value = `LAPLplw${currentYear}${randomSuffix()}`;
        });
    }

    const copyText = async (value) => {
        if (!value) {
            return false;
        }

        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);
            return true;
        }

        const temp = document.createElement('textarea');
        temp.value = value;
        temp.setAttribute('readonly', '');
        temp.style.position = 'absolute';
        temp.style.left = '-9999px';
        document.body.appendChild(temp);
        temp.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(temp);

        return copied;
    };

    copyButtons.forEach((button) => {
        button.addEventListener('click', async function () {
            const target = document.querySelector(this.dataset.copyTarget);
            if (!target || !target.value) {
                return;
            }

            const originalText = this.textContent;
            const copied = await copyText(target.value);
            this.textContent = copied ? 'Copied' : 'Gagal';

            window.setTimeout(() => {
                this.textContent = originalText;
            }, 1200);
        });
    });
});
</script>
@endpush

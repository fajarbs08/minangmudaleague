@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Akun Club</h4>
        <p class="text-muted mb-0">Admin membuat akun login club, lalu kredensial itu diberikan ke pendaftar untuk mengisi data club sendiri.</p>
    </div>
    <div class="d-flex gap-2">
        @include('competition.partials.icon-button', [
            'href' => route('clubs.index'),
            'icon' => 'shield',
            'label' => 'Klub',
            'class' => 'btn-light',
        ])
    </div>
</div>

@include('competition.partials.flash')

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="card-title mb-1">Generator Akun Club</h4>
            <p class="text-muted mb-0">Format default: `namaclub@minangmudaleague.com` dan `MMLpdg{{ $currentYear }}XXXX`.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ $clubAccounts->count() }} akun</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap club-account-generator">
            <table class="table competition-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama Akun</th>
                        <th>Email Login</th>
                        <th>Password</th>
                        <th style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <form method="POST" action="{{ route('club-accounts.store') }}" id="club-account-form">
                            @csrf
                            <td>
                                <div class="d-md-none small text-muted mb-1">Nama Akun</div>
                                <input
                                    class="form-control"
                                    id="account-name"
                                    name="account_name"
                                    type="text"
                                    value="{{ old('account_name') }}"
                                    placeholder="Contoh: Netral United"
                                >
                                <div class="competition-table-meta">Dipakai untuk nama user.</div>
                            </td>
                            <td>
                                <div class="d-md-none small text-muted mb-1">Email Login</div>
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
                                <div class="competition-table-meta">Terisi otomatis ke domain `@minangmudaleague.com`.</div>
                            </td>
                            <td>
                                <div class="d-md-none small text-muted mb-1">Password</div>
                                <div class="input-group">
                                    <input
                                        class="form-control"
                                        id="account-password"
                                        type="text"
                                        value=""
                                        placeholder="MMLpdg{{ $currentYear }}AB12"
                                        readonly
                                    >
                                    <button class="btn btn-light js-copy-trigger" type="button" data-copy-target="#account-password">Copy</button>
                                </div>
                                <input id="generated-password" name="generated_password" type="hidden" value="">
                                <div class="competition-table-meta">Suffix 4 karakter dibuat acak, tidak berurutan.</div>
                            </td>
                            <td>
                                <div class="d-md-none small text-muted mb-1">Aksi</div>
                                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2">
                                    <button type="button" class="btn btn-light w-100 w-sm-auto" id="generate-account-button">Generate</button>
                                    <button type="submit" class="btn btn-primary w-100 w-sm-auto">Simpan Akun</button>
                                </div>
                            </td>
                        </form>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-1">Daftar Akun Club</h4>
        <p class="text-muted mb-0">Akun yang sudah dibuat admin dan siap diberikan ke pendaftar.</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clubAccounts as $account)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $account->name }}</div>
                                <div class="competition-table-meta">{{ $account->clubs_count ? 'Sudah punya data club' : 'Belum punya data club' }}</div>
                            </td>
                            <td>{{ $account->email }}</td>
                            <td>{{ $account->created_at?->format('d M Y H:i') ?: '-' }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Tindakan</span>
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
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="competition-action-section">
                                            <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                            @include('competition.partials.action-item', [
                                                'icon' => 'trash-2',
                                                'label' => 'Hapus',
                                                'class' => 'text-danger js-delete-club-account',
                                                'disabled' => $account->clubs_count > 0,
                                                'attributes' => [
                                                    'data-bs-toggle' => 'modal',
                                                    'data-bs-target' => '#deleteClubAccountModal',
                                                    'data-action' => route('club-accounts.destroy', $account),
                                                    'data-name' => $account->name,
                                                ],
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="competition-table-empty">Belum ada akun club.</td>
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
    @media (max-width: 768px) {
        .club-account-generator thead {
            display: none;
        }

        .club-account-generator table,
        .club-account-generator tbody,
        .club-account-generator tr,
        .club-account-generator td {
            display: block;
            width: 100%;
        }

        .club-account-generator tr {
            border-top: 1px solid var(--bs-border-color);
        }

        .club-account-generator td {
            padding: 0.75rem 1rem;
            border: 0;
        }

        .club-account-generator td + td {
            border-top: 1px dashed var(--bs-border-color-translucent);
        }

        .club-account-generator .input-group {
            flex-wrap: wrap;
        }

        .club-account-generator .input-group .btn {
            width: 100%;
            margin-top: 0.5rem;
        }
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('account-name');
    const emailInput = document.getElementById('account-email');
    const passwordInput = document.getElementById('account-password');
    const generatedPasswordInput = document.getElementById('generated-password');
    const generateButton = document.getElementById('generate-account-button');
    const copyButtons = document.querySelectorAll('.js-copy-trigger');
    const currentYear = '{{ $currentYear }}';
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
        const password = `MMLpdg${currentYear}${randomSuffix()}`;

        emailInput.value = `${slug}@minangmudaleague.com`;
        passwordInput.value = password;
        generatedPasswordInput.value = password;
    };

    generateButton.addEventListener('click', generateCredentials);
    nameInput.addEventListener('input', generateCredentials);
    generateCredentials();

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

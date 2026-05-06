@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Edit Akun Club</h4>
        <p class="text-muted mb-0">{{ $clubAccount->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('club-accounts.create') }}" class="btn btn-light">Kembali</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <span class="text-muted text-uppercase fs-12">Status</span>
                <h3 class="mt-2 mb-1">{{ $clubAccount->clubs_count ? 'Sudah Terhubung' : 'Belum Terhubung' }}</h3>
                <p class="text-muted mb-0">{{ $clubAccount->clubs_count ? 'Akun ini sudah punya data club.' : 'Akun ini belum punya data club.' }}</p>
                <div class="mt-3">
                    <span class="badge {{ $clubAccount->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border">
                        {{ $clubAccount->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-1">Detail Akun</h4>
                <p class="text-muted mb-0">Ubah nama, email login, atau generate password baru.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('club-accounts.update', $clubAccount) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="account-name">Nama Akun</label>
                            <input class="form-control" id="account-name" name="account_name" type="text" value="{{ old('account_name', $clubAccount->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account-email">Email Login</label>
                            <input class="form-control" id="account-email" name="account_email" type="email" value="{{ old('account_email', $clubAccount->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block" for="account-is-active">Status Akun</label>
                            <div class="form-check form-switch mt-2">
                                <input name="is_active" type="hidden" value="0">
                                <input class="form-check-input" id="account-is-active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $clubAccount->is_active ? '1' : '0') === '1')>
                                <label class="form-check-label fw-semibold" for="account-is-active">Akun aktif</label>
                            </div>
                            <div class="competition-table-meta">Nonaktifkan jika akun club sementara tidak boleh login ke dashboard.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="generated-password-preview">Password Baru</label>
                            <div class="input-group">
                                <input class="form-control" id="generated-password-preview" name="generated_password" type="text" value="" placeholder="Kosongkan bila tidak diubah">
                                <button class="btn btn-light js-copy-trigger" type="button" data-copy-target="#generated-password-preview">Copy</button>
                            </div>
                            <div class="competition-table-meta">Isi manual atau klik generate untuk reset password.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="generate-password-button">Aksi Password</label>
                            <button type="button" class="btn btn-light w-100" id="generate-password-button">Generate Password Baru</button>
                            <div class="competition-table-meta">Buat password acak baru dengan format standar akun club.</div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                            <a href="{{ route('club-accounts.create') }}" class="btn btn-light">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordPreview = document.getElementById('generated-password-preview');
    const generatedPasswordInput = document.getElementById('generated-password-preview');
    const generateButton = document.getElementById('generate-password-button');
    const copyButtons = document.querySelectorAll('.js-copy-trigger');
    const currentYear = '{{ $currentYear }}';

    if (!passwordPreview || !generatedPasswordInput || !generateButton) {
        return;
    }

    const randomSuffix = () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return Array.from({ length: 4 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    };

    generateButton.addEventListener('click', function () {
        const password = `LAPLplw${currentYear}${randomSuffix()}`;
        passwordPreview.value = password;
        generatedPasswordInput.value = password;
    });

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

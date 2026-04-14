@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1">Dashboard Registrasi Liga</h4>
                <p class="text-muted mb-0">Ringkasan data klub, official, pemain, dan daftar susunan pemain.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('dashboard.admin-manual-pdf') }}" target="_blank" class="btn btn-primary">
                        Preview Manual Admin
                    </a>
                    <a href="{{ route('dashboard.admin-manual-pdf', ['download' => 1]) }}" class="btn btn-light">
                        Download Manual Admin
                    </a>
                @elseif (auth()->user()->isClubUser())
                    <a href="{{ route('dashboard.club-manual-pdf') }}" target="_blank" class="btn btn-primary">
                        Preview Manual Club
                    </a>
                    <a href="{{ route('dashboard.club-manual-pdf', ['download' => 1]) }}" class="btn btn-light">
                        Download Manual Club
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@include('competition.partials.flash')

<div class="row">
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('clubs.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Klub</p>
                    <h3 class="mb-0">{{ $stats['clubs'] }}</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('officials.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Official</p>
                    <h3 class="mb-0">{{ $stats['officials'] }}</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('players.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Pemain</p>
                    <h3 class="mb-0">{{ $stats['players'] }}</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('lineup-lists.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">DSP</p>
                    <h3 class="mb-0">{{ $stats['lineups'] }}</h3>
                </div>
            </div>
        </a>
    </div>
</div>

@if (auth()->user()->isAdmin())
    <div class="row">
        @foreach ($adminReviewStats as $item)
            <div class="col-md-6 col-xl-3">
                <a href="{{ $item['href'] }}" class="text-decoration-none text-reset d-block">
                    <div class="card {{ $item['class'] }}">
                        <div class="card-body">
                            <p class="text-muted mb-2">{{ $item['label'] }}</p>
                            <h3 class="mb-1">{{ $item['value'] }}</h3>
                            <p class="text-muted mb-0">{{ $item['hint'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card h-100" id="queue-admin">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Queue Admin</h4>
                        <p class="text-muted mb-0">Shortcut ke pekerjaan review yang masih terbuka.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($adminQueues as $queue)
                            <div class="col-md-6">
                                <a href="{{ $queue['href'] }}" class="text-decoration-none text-reset">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $queue['label'] }}</div>
                                                <div class="text-muted small">Buka daftar terfilter</div>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary fs-6">{{ $queue['count'] }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-1">Resource Admin</h4>
                    <p class="text-muted mb-0">Ringkasan akun club yang tersedia untuk operasional admin.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>Total akun club</span>
                        <span class="fw-semibold">{{ $adminResources['club_accounts'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span>Akun belum dipakai</span>
                        <span class="fw-semibold">{{ $adminResources['unused_club_accounts'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-1">Pending Paling Lama</h4>
                    <p class="text-muted mb-0">Item submitted yang paling lama belum disentuh admin.</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive competition-table-wrap">
                        <table class="table competition-table align-middle">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Nama</th>
                                    <th>Klub</th>
                                    <th>Submit</th>
                                    <th>Usia Pending</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($oldestPendingReviews as $pending)
                                    <tr>
                                        <td>{{ $pending['type'] }}</td>
                                        <td>{{ $pending['name'] }}</td>
                                        <td>{{ $pending['club'] ?: '-' }}</td>
                                        <td>{{ optional($pending['submitted_at'])->format('d M Y H:i') ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning">
                                                {{ $pending['waiting_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                        <a href="{{ $pending['href'] }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i data-lucide="clipboard-check" class="fs-14"></i>
                                            <span>Review</span>
                                        </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="competition-table-empty">Tidak ada submission pending.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card border-warning border-opacity-25">
                <div class="card-body">
                    <p class="text-muted mb-2">Klub Dalam Proses</p>
                    <h3 class="mb-0">{{ $stats['pending_clubs'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-warning border-opacity-25">
                <div class="card-body">
                    <p class="text-muted mb-2">Official Dalam Proses</p>
                    <h3 class="mb-0">{{ $stats['pending_officials'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-warning border-opacity-25">
                <div class="card-body">
                    <p class="text-muted mb-2">Pemain Dalam Proses</p>
                    <h3 class="mb-0">{{ $stats['pending_players'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-warning border-opacity-25">
                <div class="card-body">
                    <p class="text-muted mb-2">DSP Dalam Proses</p>
                    <h3 class="mb-0">{{ $stats['pending_lineups'] }}</h3>
                </div>
            </div>
        </div>
    </div>

@endif

@if ($clubSummary)
@php
    $clubNextAction = match ($clubSummary->verification_status) {
        'draft' => 'Lengkapi data klub lalu ajukan verifikasi ke admin.',
        'submitted' => 'Data klub sedang diverifikasi admin. Anda tinggal menunggu hasil review.',
        'revision' => 'Admin meminta revisi. Perbaiki data klub sesuai catatan lalu submit ulang.',
        'rejected' => 'Data klub ditolak. Hubungi admin atau panitia untuk membuka kembali pengeditan data.',
        'approved' => 'Data klub sudah diterima dan terkunci untuk menjaga hasil verifikasi.',
        default => '-',
    };
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Status Verifikasi Klub Anda</h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">{{ $clubSummary->name }}</div>
                        <div class="mt-2">
                            @include('competition.partials.status-badge', ['status' => $clubSummary->verification_status])
                        </div>
                        <div class="text-muted mt-2">{{ $clubNextAction }}</div>
                        @if ($clubSummary->verification_notes)
                            <div class="text-muted mt-2">{{ $clubSummary->verification_notes }}</div>
                        @endif
                    </div>
                    <a href="{{ route('clubs.index') }}" class="btn btn-light">Kelola Klub</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if (auth()->user()->isAdmin())
<div class="row">
    <div class="col-12">
        <div class="card" id="submission-terbaru">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">Submission Terbaru</h4>
                    <p class="text-muted mb-0">Gabungan klub, official, pemain, dan DSP yang terakhir masuk workflow.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table align-middle">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Nama</th>
                                <th>Klub</th>
                                <th>Status</th>
                                <th>Submit</th>
                                <th>Reviewer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSubmissions as $submission)
                                <tr>
                                    <td>{{ $submission['type'] }}</td>
                                    <td>{{ $submission['name'] }}</td>
                                    <td>{{ $submission['club'] ?: '-' }}</td>
                                    <td>@include('competition.partials.status-badge', ['status' => $submission['status']])</td>
                                    <td>{{ optional($submission['submitted_at'])->format('d M Y H:i') ?: '-' }}</td>
                                    <td>{{ $submission['reviewed_by'] ?: '-' }}</td>
                                    <td>
                                        <a href="{{ $submission['href'] }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i data-lucide="eye" class="fs-14"></i>
                                            <span>Buka</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="competition-table-empty">Belum ada submission yang masuk ke workflow.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Pemain Terbaru</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table competition-table-compact align-middle">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Klub</th>
                                <th>Usia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPlayers as $player)
                                <tr>
                                    <td>{{ $player->name }}</td>
                                    <td>{{ $player->club?->name }}</td>
                                    <td>{{ $player->primaryAgeGroup?->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="competition-table-empty">Belum ada data pemain.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Daftar Susunan Pemain Terbaru</h4>
                <a href="{{ route('lineup-lists.index') }}" class="btn btn-light btn-sm d-inline-flex align-items-center gap-2">
                    <i data-lucide="list" class="fs-14"></i>
                    <span>Lihat Semua</span>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table align-middle">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Klub</th>
                                <th>Kelompok Usia</th>
                                <th>Tanggal</th>
                                <th>DSP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentLineups as $lineup)
                                <tr>
                                    <td>{{ $lineup->title }}</td>
                                    <td>{{ $lineup->club?->name }}</td>
                                    <td>{{ $lineup->ageGroup?->name }}</td>
                                    <td>{{ optional($lineup->match_date)->format('d M Y') ?: '-' }}</td>
                                    <td>
                                        <a href="{{ route('lineup-lists.show', $lineup) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i data-lucide="file-output" class="fs-14"></i>
                                            <span>Generate</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="competition-table-empty">Belum ada DSP.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@php
    $needsClubProfile = auth()->user()->isClubUser()
        && (
            !$clubSummary
            || blank($clubSummary->name)
            || blank($clubSummary->manager_name)
            || blank($clubSummary->manager_title)
            || blank($clubSummary->statement_age_group)
            || blank($clubSummary->statement_contact)
            || blank($clubSummary->statement_witness_name)
            || blank($clubSummary->statement_witness_title)
            || blank($clubSummary->mailing_address)
            || blank($clubSummary->city)
            || blank($clubSummary->logo_url)
        );
    $missingName = !$clubSummary || blank($clubSummary->name);
    $missingManager = !$clubSummary || blank($clubSummary->manager_name);
    $missingTitle = !$clubSummary || blank($clubSummary->manager_title);
    $missingLogo = !$clubSummary || blank($clubSummary->logo_url);
    $missingAgeGroup = !$clubSummary || blank($clubSummary->statement_age_group);
    $missingContact = !$clubSummary || blank($clubSummary->statement_contact);
    $missingWitnessName = !$clubSummary || blank($clubSummary->statement_witness_name);
    $missingWitnessTitle = !$clubSummary || blank($clubSummary->statement_witness_title);
    $missingMailing = !$clubSummary || blank($clubSummary->mailing_address);
    $missingCity = !$clubSummary || blank($clubSummary->city);
@endphp

@if ($needsClubProfile)
    <div class="modal fade" id="clubProfileModal" tabindex="-1" aria-labelledby="clubProfileModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clubProfileModalLabel">Lengkapi Profil Klub</h5>
                </div>
                <form
                    method="POST"
                    action="{{ $clubSummary ? route('clubs.update', $clubSummary) : route('clubs.store') }}"
                    class="modal-body"
                    data-step-form
                    enctype="multipart/form-data"
                >
                    @csrf
                    @if ($clubSummary)
                        @method('PUT')
                    @endif
                    <p class="text-muted mb-3">Lengkapi data inti agar surat pernyataan otomatis terisi.</p>

                    <div data-step="1">
                        @if (!$missingName)
                            <input type="hidden" name="name" value="{{ $clubSummary?->name }}">
                        @endif
                        @if (!$missingManager)
                            <input type="hidden" name="manager_name" value="{{ $clubSummary?->manager_name }}">
                        @endif
                        @if (!$missingTitle)
                            <input type="hidden" name="manager_title" value="{{ $clubSummary?->manager_title }}">
                        @endif
                        @if (!$missingLogo)
                            <input type="hidden" name="logo_file_exists" value="1">
                        @endif

                        @if ($missingName)
                            <div class="mb-3">
                                <label class="form-label">Nama Klub</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $clubSummary?->name) }}" required>
                            </div>
                        @endif
                        @if ($missingManager)
                            <div class="mb-3">
                                <label class="form-label">Penanggung Jawab</label>
                                <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name', $clubSummary?->manager_name) }}" required>
                            </div>
                        @endif
                        @if ($missingTitle)
                            <div class="mb-3">
                                <label class="form-label">Jabatan Penanggung Jawab</label>
                                <input type="text" name="manager_title" class="form-control" value="{{ old('manager_title', $clubSummary?->manager_title) }}" required>
                            </div>
                        @endif
                        @if ($missingLogo)
                            <div class="mb-3">
                                <label class="form-label">Logo Klub</label>
                                <input type="file" name="logo_file" class="form-control" accept=".jpg,.jpeg,.png" required>
                            </div>
                        @endif
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" data-step-next>Next</button>
                        </div>
                    </div>

                    <div data-step="2" class="d-none">
                        @if (!$missingAgeGroup)
                            <input type="hidden" name="statement_age_group" value="{{ $clubSummary?->statement_age_group }}">
                        @endif
                        @if (!$missingContact)
                            <input type="hidden" name="statement_contact" value="{{ $clubSummary?->statement_contact }}">
                        @endif
                        @if (!$missingWitnessName)
                            <input type="hidden" name="statement_witness_name" value="{{ $clubSummary?->statement_witness_name }}">
                        @endif
                        @if (!$missingWitnessTitle)
                            <input type="hidden" name="statement_witness_title" value="{{ $clubSummary?->statement_witness_title }}">
                        @endif
                        @if (!$missingMailing)
                            <input type="hidden" name="mailing_address" value="{{ $clubSummary?->mailing_address }}">
                        @endif
                        @if (!$missingCity)
                            <input type="hidden" name="city" value="{{ $clubSummary?->city }}">
                        @endif

                        @if ($missingAgeGroup)
                            <div class="mb-3">
                                <label class="form-label">Kelompok Umur (Surat)</label>
                                <input type="text" name="statement_age_group" class="form-control" value="{{ old('statement_age_group', $clubSummary?->statement_age_group) }}" required>
                            </div>
                        @endif
                        @if ($missingContact)
                            <div class="mb-3">
                                <label class="form-label">Kontak (Surat)</label>
                                <input type="text" name="statement_contact" class="form-control" value="{{ old('statement_contact', $clubSummary?->statement_contact) }}" required>
                            </div>
                        @endif
                        @if ($missingWitnessName)
                            <div class="mb-3">
                                <label class="form-label">Nama Penandatangan Mengetahui</label>
                                <input type="text" name="statement_witness_name" class="form-control" value="{{ old('statement_witness_name', $clubSummary?->statement_witness_name) }}" required>
                            </div>
                        @endif
                        @if ($missingWitnessTitle)
                            <div class="mb-3">
                                <label class="form-label">Jabatan Penandatangan Mengetahui</label>
                                <input type="text" name="statement_witness_title" class="form-control" value="{{ old('statement_witness_title', $clubSummary?->statement_witness_title) }}" required>
                            </div>
                        @endif
                        @if ($missingMailing)
                            <div class="mb-3">
                                <label class="form-label">Alamat Bersurat</label>
                                <textarea name="mailing_address" rows="2" class="form-control" required>{{ old('mailing_address', $clubSummary?->mailing_address) }}</textarea>
                            </div>
                        @endif
                        @if ($missingCity)
                            <div class="mb-3">
                                <label class="form-label">Kota</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $clubSummary?->city) }}" required>
                            </div>
                        @endif
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light w-50" data-step-prev>Back</button>
                            <button type="submit" class="btn btn-primary w-50">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modalNode = document.getElementById('clubProfileModal');
                if (!modalNode || typeof bootstrap === 'undefined') {
                    return;
                }
                const stepForm = modalNode.querySelector('[data-step-form]');
                const steps = Array.from(modalNode.querySelectorAll('[data-step]'));
                const showStep = (index) => {
                    steps.forEach((step, idx) => {
                        step.classList.toggle('d-none', idx !== index);
                    });
                };

                const validateStep = (index) => {
                    const inputs = Array.from(steps[index].querySelectorAll('input,textarea'))
                        .filter((input) => !input.disabled && input.offsetParent !== null);
                    return inputs.every((input) => input.reportValidity());
                };

                modalNode.addEventListener('click', (event) => {
                    if (event.target.matches('[data-step-next]')) {
                        if (validateStep(0)) {
                            showStep(1);
                        }
                    }
                    if (event.target.matches('[data-step-prev]')) {
                        showStep(0);
                    }
                });

                modalNode.style.display = '';
                const modal = new bootstrap.Modal(modalNode, {backdrop: 'static', keyboard: false});
                modal.show();
            });
        </script>
    @endpush
@endif

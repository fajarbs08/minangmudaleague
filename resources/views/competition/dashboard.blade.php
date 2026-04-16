@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div>
            <h4 class="mb-1">Dashboard Registrasi Liga</h4>
            <p class="text-muted mb-0">Ringkasan data klub, official, pemain, dan daftar susunan pemain.</p>
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

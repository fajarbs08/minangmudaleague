@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="row lap-admin-page-head">
    <div class="col-12">
        <div class="lap-admin-page-meta">
            <h4 class="lap-admin-page-title">Dashboard Registrasi Liga</h4>
            <p class="lap-admin-page-copy">Pantau progres verifikasi klub, ofisial, pemain, dan DSP dari satu tempat.</p>
        </div>
    </div>
</div>

@include('competition.partials.flash')

<div class="row">
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('clubs.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card lap-admin-stat-card lap-admin-stat-card-primary">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-primary mb-3">Klub</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['clubs'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Total klub terdaftar</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('officials.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card lap-admin-stat-card lap-admin-stat-card-support">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-support mb-3">Ofisial</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['officials'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Total ofisial terdaftar</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('players.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card lap-admin-stat-card lap-admin-stat-card-support">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-support mb-3">Pemain</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['players'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Total pemain terdaftar</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('lineup-lists.index') }}" class="text-decoration-none text-reset d-block">
            <div class="card lap-admin-stat-card lap-admin-stat-card-primary">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-primary mb-3">DSP</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['lineups'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Total DSP terdaftar</p>
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
                    <div class="card lap-admin-stat-card lap-admin-stat-card-{{ $item['tone'] ?? 'support' }} {{ $item['class'] }}">
                        <div class="card-body">
                            <span class="lap-admin-chip lap-admin-chip-{{ $item['tone'] ?? 'support' }} mb-3">{{ $item['label'] }}</span>
                            <h3 class="lap-admin-stat-value mb-2">{{ $item['value'] }}</h3>
                            <p class="lap-admin-stat-copy">{{ $item['hint'] }}</p>
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
                        <h4 class="card-title mb-1">Antrian Verifikasi Admin</h4>
                        <p class="text-muted mb-0">Masuk cepat ke daftar yang masih menunggu keputusan admin.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($adminQueues as $queue)
                            <div class="col-md-6">
                                <a href="{{ $queue['href'] }}" class="text-decoration-none text-reset">
                                    <div class="lap-admin-mini-panel p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $queue['label'] }}</div>
                                                <div class="text-muted small">{{ $queue['hint'] }}</div>
                                            </div>
                                            <span class="lap-admin-chip lap-admin-chip-primary lap-admin-chip-count">{{ $queue['count'] }}</span>
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
                    <h4 class="card-title mb-1">Kesiapan Akun Klub</h4>
                    <p class="text-muted mb-0">Pantau stok akun klub yang masih bisa dipakai untuk registrasi.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>Total akun klub</span>
                        <span class="fw-semibold">{{ $adminResources['club_accounts'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span>Siap dipakai</span>
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
                    <h4 class="card-title mb-1">Paling Lama Menunggu Review</h4>
                    <p class="text-muted mb-0">Prioritas review untuk data yang paling lama belum ditangani.</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive competition-table-wrap">
                        <table class="table competition-table align-middle">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Nama</th>
                                    <th>Klub</th>
                                    <th>Diajukan</th>
                                    <th>Lama Menunggu</th>
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
                                            <span class="lap-admin-chip lap-admin-chip-pending">
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
                                        <td colspan="6" class="competition-table-empty">Tidak ada data yang sedang menunggu review.</td>
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
            <div class="card lap-admin-stat-card lap-admin-stat-card-pending">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-pending mb-3">Klub Menunggu Review</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['pending_clubs'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Pengajuan klub menunggu keputusan admin</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card lap-admin-stat-card lap-admin-stat-card-pending">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-pending mb-3">Ofisial Menunggu Review</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['pending_officials'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Pengajuan ofisial menunggu keputusan admin</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card lap-admin-stat-card lap-admin-stat-card-pending">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-pending mb-3">Pemain Menunggu Review</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['pending_players'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Pengajuan pemain menunggu keputusan admin</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card lap-admin-stat-card lap-admin-stat-card-pending">
                <div class="card-body">
                    <span class="lap-admin-chip lap-admin-chip-pending mb-3">DSP Menunggu Review</span>
                    <h3 class="lap-admin-stat-value">{{ $stats['pending_lineups'] }}</h3>
                    <p class="lap-admin-stat-copy mt-2">Pengajuan DSP menunggu keputusan admin</p>
                </div>
            </div>
        </div>
    </div>

@endif

@if ($clubSummary)
@php
    $clubNextAction = match ($clubSummary->verification_status) {
        'draft' => 'Lengkapi profil klub dan dokumen pendukung, lalu ajukan verifikasi saat semua data sudah siap.',
        'submitted' => 'Data klub sudah diajukan dan sedang menunggu review admin.',
        'revision' => 'Ada catatan revisi dari admin. Perbaiki bagian yang diminta lalu ajukan ulang.',
        'rejected' => 'Data klub belum diterima. Hubungi admin atau panitia bila perlu dibuka ulang.',
        'approved' => 'Data klub sudah diterima dan dikunci agar hasil verifikasi tetap konsisten.',
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
                    <h4 class="card-title mb-1">Pengajuan Terbaru</h4>
                    <p class="text-muted mb-0">Gabungan klub, ofisial, pemain, dan DSP yang terakhir masuk antrean verifikasi.</p>
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
                                    <th>Diajukan</th>
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
                                    <td colspan="7" class="competition-table-empty">Belum ada pengajuan yang masuk ke antrean verifikasi.</td>
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
                                            <span>Buka DSP</span>
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

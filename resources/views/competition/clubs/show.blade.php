@extends('layouts.vertical', ['title' => $title])

@php
    $isHistoryView = app(\App\Services\SeasonContext::class)->isViewingHistory();
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Kompetisi</a></li>
                <li class="breadcrumb-item"><a href="{{ route('clubs.index') }}">Klub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
            </ol>
        </nav>
        <h4 class="mb-1">{{ $club->name }}</h4>
        <p class="text-muted mb-0">Profil klub dan kelengkapan berkas.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        @include('competition.partials.status-badge', ['status' => $club->verification_status])
        <a href="{{ route('clubs.index') }}" class="btn btn-light">Kembali</a>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Profil Klub</h5>
            </div>
            <div class="card-body">
                @if ($club->logo_file_url)
                    <div class="mb-3">
                        <div class="text-muted small">Logo</div>
                        <div class="d-inline-flex align-items-center justify-content-center rounded border bg-white p-3 mt-1" style="width: 140px; height: 140px;">
                            <img src="{{ $club->logo_file_url }}" alt="Logo klub" class="img-fluid" style="max-width: 112px; max-height: 112px; width: auto; height: auto; object-fit: contain;">
                        </div>
                    </div>
                @endif
                <div class="mb-3">
                    <div class="text-muted small">Nama Klub</div>
                    <div class="fw-semibold">{{ $club->name }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Singkatan</div>
                    <div class="fw-semibold">{{ $club->short_name ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Manager</div>
                    <div class="fw-semibold">{{ $club->manager_name ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Jabatan</div>
                    <div class="fw-semibold">{{ $club->manager_title ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Zona (Kota / Kabupaten)</div>
                    <div class="fw-semibold">{{ $club->zone ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-muted small">Tahun Berdiri</div>
                    <div class="fw-semibold">{{ $club->founded_year ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Alamat</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Alamat Klub</div>
                    <div class="fw-semibold">{{ $club->address ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-muted small">Alamat Latihan</div>
                    <div class="fw-semibold">{{ $club->training_address ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Ringkasan</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Ofisial</span>
                    <span class="fw-semibold">{{ $club->officials_count }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Pemain</span>
                    <span class="fw-semibold">{{ $club->players_count }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">DSP</span>
                    <span class="fw-semibold">{{ $club->lineup_lists_count }}</span>
                </div>
                <div>
                    <div class="text-muted small">Catatan</div>
                    <div class="fw-semibold">{{ $club->notes ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Dokumen</h5>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clubs.statement-template') }}" target="_blank" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i data-lucide="download" class="fs-14"></i>
                <span>Download Template Surat</span>
            </a>
            @if ($club->statement_file_url)
                <a href="{{ $club->statement_file_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                    <i data-lucide="file-text" class="fs-14"></i>
                    <span>Pernyataan</span>
                </a>
            @endif
            @if (!$club->statement_file_url)
                <div class="text-muted">Belum ada dokumen yang diunggah.</div>
            @endif
        </div>
        <div class="text-muted small mt-2">Download template, isi data dan tanda tangan, lalu unggah kembali.</div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Status Verifikasi</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Status</div>
                <div class="fw-semibold text-capitalize">{{ $club->verification_status }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Dikirim</div>
                <div class="fw-semibold">{{ optional($club->submitted_at)->format('d M Y H:i') ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Review</div>
                <div class="fw-semibold">{{ optional($club->reviewed_at)->format('d M Y H:i') ?: '-' }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted small">Catatan Verifikasi</div>
                <div class="fw-semibold">{{ $club->verification_notes ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Aksi Verifikasi</h5>
    </div>
    <div class="card-body">
        @if ($isHistoryView)
            <div class="text-muted">Season histori bersifat read-only. Aksi verifikasi hanya tersedia pada season aktif.</div>
        @else
            @include('competition.partials.review-actions', [
                'item' => $club,
                'reviewRoute' => route('clubs.review', $club),
                'submitRoute' => route('clubs.submit', $club),
            ])
        @endif
    </div>
</div>
@endsection

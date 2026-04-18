@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Sponsor</h4>
        <p class="text-muted mb-0">Kelola logo dan tautan sponsor yang tampil di halaman publik.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createSponsorModal">
            <i data-lucide="plus" class="fs-14"></i>
            <span>Tambah Sponsor</span>
        </button>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Total Sponsor</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $sponsors->count() }}</h3>
                    <span class="badge bg-primary-subtle text-primary">Item</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tayang Publik</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $sponsors->where('is_published', true)->count() }}</h3>
                    <span class="badge bg-success-subtle text-success">Aktif</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tier Berbeda</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $sponsors->pluck('tier')->filter()->unique()->count() }}</h3>
                    <span class="badge bg-info-subtle text-info">Kategori</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('sponsors.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="card-title mb-1">Daftar Sponsor</h4>
                <p class="text-muted mb-0">Logo sponsor akan dipakai di halaman publik sponsor.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari sponsor" value="{{ $search ?? '' }}" style="min-width: 220px;">
                <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                <a href="{{ route('sponsors.index') }}" class="btn btn-sm btn-light">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive competition-table-wrap">
            <table class="table competition-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Sponsor</th>
                        <th>Tier</th>
                        <th>Status</th>
                        <th>Urutan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sponsors as $item)
                        @php
                            $deleteSponsorFormId = 'delete-sponsor-'.$item->id;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 64px; height: 64px;">
                                        @if ($item->logo_url)
                                            <img src="{{ $item->logo_url }}" alt="{{ $item->name }}" style="max-width: 48px; max-height: 48px; width: auto; height: auto;">
                                        @else
                                            <span class="fw-bold text-muted">{{ strtoupper(substr($item->short_name ?: $item->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <div class="competition-table-meta">
                                            {{ $item->short_name ?: '-' }}
                                            <span class="mx-1">•</span>
                                            {{ $item->website_url ?: 'Tanpa website' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $item->tier }}</span></td>
                            <td>
                                <span class="badge {{ $item->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $item->is_published ? 'Tampil' : 'Draft' }}
                                </span>
                            </td>
                            <td>{{ $item->sort_order }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light competition-action-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Tindakan</span>
                                        <svg class="competition-action-toggle-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M4 6.5L8 10L12 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-2 competition-action-menu">
                                        @include('competition.partials.action-item', [
                                            'href' => route('sponsors.edit', $item),
                                            'icon' => 'square-pen',
                                            'label' => 'Edit',
                                        ])
                                        <form method="POST" action="{{ route('sponsors.destroy', $item) }}" id="{{ $deleteSponsorFormId }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#sponsorActionConfirmModal"
                                                data-confirm-form="#{{ $deleteSponsorFormId }}"
                                                data-confirm-title="Hapus Sponsor"
                                                data-confirm-message="Sponsor {{ $item->name }} akan dihapus. Tindakan ini tidak bisa dibatalkan."
                                                data-confirm-submit-label="Hapus"
                                                data-confirm-submit-class="btn-danger"
                                            >
                                                <i data-lucide="trash-2" class="fs-14"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada sponsor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createSponsorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('sponsors.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sponsor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $sponsor->name) }}" required>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Singkatan</label>
                            <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $sponsor->short_name) }}">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $sponsor->sort_order) }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="website_url" class="form-control" value="{{ old('website_url', $sponsor->website_url) }}">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Tier <span class="text-danger">*</span></label>
                            <input type="text" name="tier" class="form-control" value="{{ old('tier', $sponsor->tier) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo <span class="text-danger">*</span></label>
                            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg" required>
                            <small class="text-muted d-block mt-2">Logo akan dinormalisasi ke format square transparan agar rapi di slider publik.</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="create-sponsor-published" name="is_published" value="1" @checked(old('is_published', $sponsor->is_published))>
                                <label class="form-check-label" for="create-sponsor-published">Tampilkan di halaman publik</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Sponsor</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.partials.action-confirm-modal', [
    'modalId' => 'sponsorActionConfirmModal',
    'title' => 'Hapus Sponsor',
    'message' => 'Sponsor yang dipilih akan dihapus. Tindakan ini tidak bisa dibatalkan.',
    'submitLabel' => 'Hapus',
    'submitClass' => 'btn-danger',
])
@endsection

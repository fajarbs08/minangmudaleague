@extends('layouts.vertical', ['title' => $title])

@section('content')
@php
    $sortUrl = function (string $column) use ($activeCategory, $search, $sort, $direction) {
        $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

        return route('information-resources.index', array_filter([
            'category' => $activeCategory,
            'search' => $search,
            'sort' => $column,
            'direction' => $nextDirection,
        ]));
    };
    $categories = ['template' => 'Template', 'flow' => 'Flow', 'rules' => 'Rules', 'manual' => 'Manual', 'other' => 'Lainnya'];
    $publishedCount = $resources->where('is_published', true)->count();
    $pinnedCount = $resources->where('is_pinned', true)->count();
    $imageCount = $resources->filter(fn ($item) => $item->isImage)->count();
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Pusat Informasi</h4>
        <p class="text-muted mb-0">Kelola dokumen, gambar panduan, dan file unduhan yang tampil di halaman publik atau khusus akun club.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createInformationResourceModal">
            <i data-lucide="plus" class="fs-14"></i>
            <span>Tambah Dokumen</span>
        </button>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Total Dokumen</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $resources->count() }}</h3>
                    <span class="badge bg-primary-subtle text-primary">{{ count($categories) }} kategori</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Dokumen Tayang</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $publishedCount }}</h3>
                    <span class="badge bg-success-subtle text-success">{{ $pinnedCount }} pinned</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Konten Visual</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">{{ $imageCount }}</h3>
                    <span class="badge bg-info-subtle text-info">Preview aktif</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('information-resources.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="flex-grow-1" style="min-width: 240px;">
                        <h4 class="card-title mb-1">Daftar Dokumen Upload</h4>
                        <p class="text-muted mb-0">Dokumen yang berstatus tampil akan muncul sesuai visibilitasnya: publik atau hanya akun club.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari judul dokumen" value="{{ $search ?? '' }}" style="min-width: 220px;">
                        @if ($activeCategory ?? '')
                            <input type="hidden" name="category" value="{{ $activeCategory }}">
                        @endif
                        @if ($sort ?? '')
                            <input type="hidden" name="sort" value="{{ $sort }}">
                        @endif
                        @if ($direction ?? '')
                            <input type="hidden" name="direction" value="{{ $direction }}">
                        @endif
                        <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                        <a href="{{ route('information-resources.index', array_filter(['category' => $activeCategory ?? null, 'sort' => $sort ?? null, 'direction' => $direction ?? null])) }}" class="btn btn-sm btn-light">Reset</a>
                    </div>
                </form>
            </div>
            <div class="card-header border-top">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach (['' => 'Semua'] + $categories as $value => $label)
                            <a href="{{ route('information-resources.index', array_filter(['category' => $value, 'search' => $search ?? null])) }}" class="btn btn-sm {{ ($activeCategory ?? '') === $value ? 'btn-primary' : 'btn-light' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                    <div class="small text-muted">
                        {{ $resources->count() }} dokumen
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="{{ route('information-resources.bulk-update') }}" id="bulk-information-resource-form">
                    @csrf
                    <div class="card-body border-bottom bg-light-subtle">
                        <div class="row g-3 align-items-end competition-bulk-panel">
                            <div class="col-lg-4">
                                <label class="form-label">Bulk Action</label>
                                <select name="bulk_action" class="form-select" required>
                                    <option value="">Pilih aksi</option>
                                    <option value="publish">Tampilkan</option>
                                    <option value="unpublish">Sembunyikan</option>
                                    <option value="pin">Pin ke Atas</option>
                                    <option value="unpin">Lepas Pin</option>
                                    <option value="delete">Hapus</option>
                                </select>
                            </div>
                            <div class="col-lg-5">
                                <div class="small text-muted">
                                    <span data-bulk-selected-count>0</span> dokumen dipilih.
                                </div>
                                @if ($activeCategory ?? '')
                                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                                @endif
                                @if ($search ?? '')
                                    <input type="hidden" name="search" value="{{ $search }}">
                                @endif
                                @if ($sort ?? '')
                                    <input type="hidden" name="sort" value="{{ $sort }}">
                                @endif
                                @if ($direction ?? '')
                                    <input type="hidden" name="direction" value="{{ $direction }}">
                                @endif
                            </div>
                            <div class="col-lg-3">
                                <button type="submit" class="btn btn-dark w-100" data-bulk-submit disabled>Terapkan</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 48px;">
                                    <input type="checkbox" class="form-check-input js-check-all" data-target=".js-information-resource-row">
                                </th>
                                <th>Dokumen</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Visibilitas</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($resources as $item)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input js-information-resource-row" name="selected_ids[]" value="{{ $item->id }}" form="bulk-information-resource-form">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-start gap-3">
                                            @if ($item->isImage)
                                                <img src="{{ $item->file_url }}" alt="{{ $item->title }}" class="rounded border flex-shrink-0" style="width: 64px; height: 64px; object-fit: cover;">
                                            @else
                                                <div class="avatar-sm bg-light border rounded d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                                    <span class="small fw-semibold text-muted">{{ $item->type_label }}</span>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                                                    <span>{{ $item->title }}</span>
                                                    @if ($item->is_pinned)
                                                        <span class="badge bg-dark-subtle text-dark">Pinned</span>
                                                    @endif
                                                    <span class="badge bg-light text-dark border">{{ $item->type_label }}</span>
                                                </div>
                                                <div class="competition-table-meta">
                                                    {{ $item->file_name }}
                                                    <span class="mx-1">•</span>
                                                    {{ $item->file_size_label }}
                                                    <span class="mx-1">•</span>
                                                    Upload {{ $item->created_at?->format('d M Y H:i') ?: '-' }}
                                                    <span class="mx-1">•</span>
                                                    Urutan {{ $item->sort_order }}
                                                </div>
                                                @if ($item->description)
                                                    <div class="text-muted small mt-1 text-wrap">{{ $item->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->badge_class }}">{{ $item->badge_label }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column align-items-start gap-2">
                                            <span class="badge {{ $item->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ $item->is_published ? 'Tampil' : 'Draft' }}
                                            </span>
                                            @if ($item->is_pinned)
                                                <span class="badge bg-dark-subtle text-dark">Prioritas</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->visibility === 'public' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }}">
                                            {{ $item->visibility_label }}
                                        </span>
                                    </td>
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
                                                    <div class="competition-action-label px-2 pb-2">Dokumen</div>
                                                    @include('competition.partials.action-item', [
                                                        'href' => $item->file_url,
                                                        'icon' => 'eye',
                                                        'label' => 'Buka',
                                                        'attributes' => ['target' => '_blank'],
                                                    ])
                                                    @include('competition.partials.action-item', [
                                                        'href' => route('information-resources.download', $item),
                                                        'icon' => 'download',
                                                        'label' => 'Unduh',
                                                    ])
                                                    @include('competition.partials.action-item', [
                                                        'href' => route('information-resources.edit', $item),
                                                        'icon' => 'square-pen',
                                                        'label' => 'Edit',
                                                    ])
                                                </div>
                                                <div class="dropdown-divider"></div>
                                                <div class="competition-action-section">
                                                    <div class="competition-action-label px-2 pb-2">Status</div>
                                                    <form method="POST" action="{{ route('information-resources.toggle-publish', $item) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if ($activeCategory ?? '')
                                                            <input type="hidden" name="category" value="{{ $activeCategory }}">
                                                        @endif
                                                        @if ($search ?? '')
                                                            <input type="hidden" name="search" value="{{ $search }}">
                                                        @endif
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                                            <i data-lucide="{{ $item->is_published ? 'eye-off' : 'eye' }}" class="fs-14"></i>
                                                            <span>{{ $item->is_published ? 'Sembunyikan' : 'Tampilkan' }}</span>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="dropdown-divider"></div>
                                                <div class="competition-action-section">
                                                    <div class="competition-action-label px-2 pb-2">Zona Bahaya</div>
                                                    <form method="POST" action="{{ route('information-resources.destroy', $item) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                            <i data-lucide="trash-2" class="fs-14"></i>
                                                            <span>Hapus</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="competition-table-empty">Belum ada dokumen upload untuk pusat informasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createInformationResourceModal" tabindex="-1" aria-labelledby="createInformationResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('information-resources.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="createInformationResourceModalLabel">Tambah Dokumen</h5>
                        <div class="small text-muted mt-1">Format yang didukung: PDF, JPG, JPEG, PNG, DOC, DOCX. Maks. 2 MB.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $resource->title) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach ($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', $resource->category) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visibilitas <span class="text-danger">*</span></label>
                            <select name="visibility" class="form-select" required>
                                @foreach ($visibilityOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('visibility', $resource->visibility) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $resource->sort_order) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $resource->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
                            <div class="form-text">Format: PDF, JPG, JPEG, PNG, DOC, DOCX. Maksimal 2 MB per file.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_pinned" name="is_pinned" value="1" @checked(old('is_pinned', $resource->is_pinned))>
                                <label class="form-check-label" for="is_pinned">Pin ke urutan paling atas</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $resource->is_published))>
                                <label class="form-check-label" for="is_published">Tampilkan sesuai visibilitas</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bulkForm = document.getElementById('bulk-information-resource-form');
        const createModalElement = document.getElementById('createInformationResourceModal');

        if (createModalElement && @json($errors->any())) {
            const createModal = bootstrap.Modal.getOrCreateInstance(createModalElement);
            createModal.show();
        }

        if (!bulkForm) return;

        const checkAll = bulkForm.closest('.card').querySelector('.js-check-all');
        const rowChecks = [...bulkForm.closest('.card').querySelectorAll('.js-information-resource-row')];
        const bulkSubmit = bulkForm.querySelector('[data-bulk-submit]');
        const selectedCount = bulkForm.querySelector('[data-bulk-selected-count]');
        const bulkAction = bulkForm.querySelector('[name="bulk_action"]');

        const syncSelectionState = function () {
            const checkedCount = rowChecks.filter((item) => item.checked).length;
            if (selectedCount) {
                selectedCount.textContent = checkedCount;
            }
            if (bulkSubmit) {
                bulkSubmit.disabled = checkedCount === 0;
            }
            if (checkAll) {
                checkAll.checked = checkedCount > 0 && checkedCount === rowChecks.length;
                checkAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
            }
        };

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                rowChecks.forEach((item) => {
                    item.checked = checkAll.checked;
                });
                syncSelectionState();
            });
        }

        rowChecks.forEach((item) => {
            item.addEventListener('change', syncSelectionState);
        });

        bulkForm.addEventListener('submit', function (event) {
            if (!bulkAction || bulkAction.value !== 'delete') {
                return;
            }

            const checkedCount = rowChecks.filter((item) => item.checked).length;
            const confirmed = window.confirm(`Hapus ${checkedCount} dokumen terpilih? File yang sudah dihapus tidak bisa dikembalikan.`);

            if (!confirmed) {
                event.preventDefault();
            }
        });

        syncSelectionState();
    });
</script>
@endsection

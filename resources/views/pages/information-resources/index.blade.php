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
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Pusat Informasi</h4>
        <p class="text-muted mb-0">Admin dapat upload PDF atau gambar agar halaman pusat informasi milik club selalu terbarui.</p>
    </div>
</div>

@include('competition.partials.flash')

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-1">Tambah Dokumen</h4>
                <p class="text-muted mb-0">Format yang didukung: PDF, JPG, JPEG, PNG, DOC, DOCX. Maks. 2 MB.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('information-resources.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $resource->title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            @foreach (['template' => 'Template', 'flow' => 'Flow', 'rules' => 'Rules', 'manual' => 'Manual', 'other' => 'Lainnya'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', $resource->category) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $resource->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $resource->sort_order) }}">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_pinned" name="is_pinned" value="1" @checked(old('is_pinned', $resource->is_pinned))>
                        <label class="form-check-label" for="is_pinned">Pin ke urutan paling atas</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                    </div>
                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $resource->is_published))>
                        <label class="form-check-label" for="is_published">Tampilkan di halaman club</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Dokumen</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('information-resources.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="flex-grow-1" style="min-width: 240px;">
                        <h4 class="card-title mb-1">Daftar Dokumen Upload</h4>
                        <p class="text-muted mb-0">Dokumen aktif akan muncul otomatis di pusat informasi akun club.</p>
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
                        @foreach (['' => 'Semua', 'template' => 'Template', 'flow' => 'Flow', 'rules' => 'Rules', 'manual' => 'Manual', 'other' => 'Lainnya'] as $value => $label)
                            <a href="{{ route('information-resources.index', array_filter(['category' => $value, 'search' => $search ?? null])) }}" class="btn btn-sm {{ ($activeCategory ?? '') === $value ? 'btn-primary' : 'btn-light' }}">
                                {{ $label }}
                            </a>
                        @endforeach
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
                                <th>Judul</th>
                                <th>Tipe</th>
                                <th>
                                    <a href="{{ $sortUrl('file_size') }}" class="text-reset d-inline-flex align-items-center gap-1">
                                        <span>Ukuran</span>
                                        @if (($sort ?? '') === 'file_size')
                                            <span>{{ ($direction ?? 'desc') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('created_at') }}" class="text-reset d-inline-flex align-items-center gap-1">
                                        <span>Upload</span>
                                        @if (($sort ?? '') === 'created_at')
                                            <span>{{ ($direction ?? 'desc') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Urutan</th>
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
                                        <div class="fw-semibold d-flex align-items-center gap-2">
                                            <span>{{ $item->title }}</span>
                                            @if ($item->is_pinned)
                                                <span class="badge bg-dark-subtle text-dark">Pinned</span>
                                            @endif
                                        </div>
                                        <div class="competition-table-meta">{{ $item->file_name }}</div>
                                        @if ($item->isImage)
                                            <img src="{{ $item->file_url }}" alt="{{ $item->title }}" class="rounded border mt-2" style="width: 72px; height: 72px; object-fit: cover;">
                                        @endif
                                    </td>
                                    <td>{{ $item->type_label }}</td>
                                    <td>{{ $item->file_size_label }}</td>
                                    <td>{{ $item->created_at?->format('d M Y H:i') ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $item->badge_class }}">{{ $item->badge_label }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $item->is_published ? 'Tampil' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a href="{{ $item->file_url }}" target="_blank" class="btn btn-sm btn-light">Buka</a>
                                            <a href="{{ route('information-resources.download', $item) }}" class="btn btn-sm btn-light">Download</a>
                                            <form method="POST" action="{{ route('information-resources.toggle-publish', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                @if ($activeCategory ?? '')
                                                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                                                @endif
                                                @if ($search ?? '')
                                                    <input type="hidden" name="search" value="{{ $search }}">
                                                @endif
                                                <button type="submit" class="btn btn-sm {{ $item->is_published ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                                    {{ $item->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                                                </button>
                                            </form>
                                            <a href="{{ route('information-resources.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="{{ route('information-resources.destroy', $item) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="competition-table-empty">Belum ada dokumen upload untuk pusat informasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bulkForm = document.getElementById('bulk-information-resource-form');
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

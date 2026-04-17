@extends('layouts.vertical', ['title' => $title])

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1">Pusat Informasi Club</h4>
                <p class="text-muted mb-0">Dokumen resmi untuk kebutuhan registrasi dan operasional club.</p>
            </div>
        </div>
    </div>
</div>

@include('competition.partials.flash')

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            @foreach (['' => 'Semua', 'template' => 'Template', 'flow' => 'Flow', 'rules' => 'Rules', 'manual' => 'Manual', 'other' => 'Lainnya'] as $value => $label)
                <a href="{{ route('club-resources.index', array_filter(['category' => $value])) }}" class="btn btn-sm {{ ($activeCategory ?? '') === $value ? 'btn-primary' : 'btn-light' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@if ($managedResources->isNotEmpty())
<div class="row g-4 mb-1">
    @foreach ($managedResources as $resource)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge {{ $resource->badge_class }}">{{ $resource->badge_label }}</span>
                                    @if ($resource->is_pinned)
                                        <span class="badge bg-dark-subtle text-dark">Prioritas</span>
                                    @endif
                                    <span class="badge {{ $resource->visibility === 'public' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }}">{{ $resource->visibility_label }}</span>
                                    <span class="badge bg-light text-dark border">{{ $resource->type_label }}</span>
                                </div>
                            <h5 class="mt-3 mb-2">{{ $resource->title }}</h5>
                        </div>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i data-lucide="file-stack" class="fs-20 text-primary"></i>
                        </div>
                    </div>
                    @if ($resource->isImage)
                        <img src="{{ $resource->file_url }}" alt="{{ $resource->title }}" class="img-fluid rounded border mb-3" style="height: 180px; width: 100%; object-fit: cover;">
                    @endif
                    <p class="text-muted mb-4 flex-grow-1">{{ $resource->description ?: 'Dokumen informasi dari admin untuk kebutuhan registrasi club.' }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $resource->file_url }}" target="_blank" class="btn btn-primary">
                            Buka
                        </a>
                        <a href="{{ route('information-resources.download', $resource) }}" class="btn btn-light">
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

@if (($activeCategory ?? '') === '')
    <div class="row g-4">
        @foreach ($downloadResources as $resource)
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <span class="badge {{ $resource['badge_class'] }}">{{ $resource['badge'] }}</span>
                                <h5 class="mt-3 mb-2">{{ $resource['label'] }}</h5>
                            </div>
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i data-lucide="file-down" class="fs-20 text-primary"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-4 flex-grow-1">{{ $resource['description'] }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ $resource['open_url'] }}" target="_blank" class="btn btn-primary">
                                Buka
                            </a>
                            <a href="{{ $resource['download_url'] }}" class="btn btn-light">
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if (($activeCategory ?? '') === '')
    <div class="row mt-2">
        <div class="col-12">
            <div class="card border-dashed">
                <div class="card-header">
                    <h4 class="card-title mb-1">Dokumen Tambahan</h4>
                    <p class="text-muted mb-0">Bagian ini disiapkan untuk rules kompetisi dan dokumen lain yang akan ditambahkan berikutnya.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($upcomingResources as $resource)
                            <div class="col-lg-6">
                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <h6 class="mb-1">{{ $resource['label'] }}</h6>
                                            <p class="text-muted small mb-0">{{ $resource['description'] }}</p>
                                        </div>
                                        <span class="badge bg-warning-subtle text-warning">Segera tersedia</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

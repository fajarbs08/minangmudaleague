<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">Manual Admin</h4>
            <p class="text-muted mb-0">Panduan singkat tugas admin dalam format PDF.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('dashboard.admin-manual-pdf') }}" target="_blank" class="btn btn-primary">
                Buka PDF
            </a>
            <a href="{{ route('dashboard.admin-manual-pdf', ['download' => 1]) }}" class="btn btn-light">
                Unduh PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <iframe
            src="{{ route('dashboard.admin-manual-pdf') }}#toolbar=0"
            title="Manual Admin"
            style="width: 100%; height: 820px; border: 1px solid #dee2e6; border-radius: 8px; background: #f8f9fa;"
        ></iframe>
    </div>
</div>

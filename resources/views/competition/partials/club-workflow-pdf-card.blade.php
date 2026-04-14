<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">Tahapan Registrasi Club</h4>
            <p class="text-muted mb-0">Panduan alur registrasi dalam format PDF yang bisa dibuka langsung dari dashboard.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('dashboard.workflow-pdf') }}" target="_blank" class="btn btn-primary">
                Buka PDF
            </a>
            <a href="{{ route('dashboard.workflow-pdf', ['download' => 1]) }}" class="btn btn-light">
                Download PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <iframe
            src="{{ route('dashboard.workflow-pdf') }}#toolbar=0"
            title="Tahapan Workflow Dashboard Club"
            style="width: 100%; height: 920px; border: 1px solid #dee2e6; border-radius: 8px; background: #f8f9fa;"
        ></iframe>
    </div>
</div>

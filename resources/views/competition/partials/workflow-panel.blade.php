<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h5 class="mb-1">Workflow Verifikasi</h5>
                <div class="text-muted">
                    Tahapan: input data, ajukan verifikasi, review admin, lalu status akhir diterima atau ditolak.
                </div>
            </div>
            @include('competition.partials.status-badge', ['status' => $item->verification_status])
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="text-muted small">Diajukan</div>
                <div>{{ optional($item->submitted_at)->format('d M Y H:i') ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Direview</div>
                <div>{{ optional($item->reviewed_at)->format('d M Y H:i') ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Catatan Admin</div>
                <div>{{ $item->verification_notes ?: '-' }}</div>
            </div>
        </div>

        @include('competition.partials.review-actions', [
            'item' => $item,
            'submitRoute' => $submitRoute,
            'reviewRoute' => $reviewRoute,
        ])
    </div>
</div>

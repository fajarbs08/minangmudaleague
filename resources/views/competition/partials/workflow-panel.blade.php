@php
    $isAdmin = auth()->user()->isAdmin();
    $statusGuidance = match ($item->verification_status) {
        'draft' => $isAdmin
            ? 'Data masih draft. Admin bisa edit manual bila ada permintaan khusus dari klub.'
            : 'Lengkapi DSP lalu ajukan verifikasi ke admin.',
        'submitted' => $isAdmin
            ? 'Data sudah diajukan. Review dari halaman ini atau edit manual bila memang perlu intervensi admin.'
            : 'DSP sedang direview admin.',
        'revision' => $isAdmin
            ? 'DSP menunggu tindak lanjut. Minta revisi jika klub yang harus memperbaiki, atau edit manual bila admin diminta membantu.'
            : 'Perbaiki data sesuai catatan admin lalu ajukan verifikasi ulang.',
        'approved' => $isAdmin
            ? 'DSP sudah diterima. Jika ada kebutuhan khusus dari klub, admin tetap bisa edit manual atau ubah ke revisi.'
            : 'DSP sudah diterima admin.',
        'rejected' => $isAdmin
            ? 'DSP ditolak. Gunakan revisi bila klub perlu memperbaiki, atau edit manual bila admin diminta membantu.'
            : 'DSP ditolak. Periksa catatan admin sebelum lanjut.',
        default => 'Ikuti tahapan verifikasi sesuai status DSP.',
    };
@endphp

<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h5 class="mb-1">Workflow Verifikasi</h5>
                <div class="text-muted">
                    {{ $statusGuidance }}
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

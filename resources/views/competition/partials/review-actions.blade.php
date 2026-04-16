@if (auth()->user()->isAdmin() && $item->canBeReviewedByAdmin())
    <form method="POST" action="{{ $reviewRoute }}" class="w-100 review-actions-form">
        @csrf
        <label class="form-label form-label-sm text-muted mb-2 d-block">Review Admin</label>
        <textarea
            name="verification_notes"
            rows="2"
            class="form-control form-control-sm mb-3 review-actions-notes"
            placeholder="Catatan admin. Wajib untuk revisi atau reject."
        >{{ old('verification_notes') }}</textarea>
        <div class="review-actions-grid">
            @if ($item->verification_status !== 'approved')
                <button type="submit" name="status" value="approved" class="btn btn-sm btn-success review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
                    <i data-lucide="check" class="review-actions-icon" aria-hidden="true"></i>
                    <span>Approve</span>
                </button>
            @endif
            <button type="submit" name="status" value="revision" class="btn btn-sm btn-warning review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
                <i data-lucide="refresh-ccw" class="review-actions-icon" aria-hidden="true"></i>
                <span>Revisi</span>
            </button>
            @if ($item->verification_status !== 'approved')
                <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger review-actions-button review-actions-button-danger d-inline-flex align-items-center justify-content-center gap-2">
                    <i data-lucide="x" class="review-actions-icon" aria-hidden="true"></i>
                    <span>Reject</span>
                </button>
            @endif
        </div>
        @if ($item->verification_status === 'approved')
            <div class="small text-muted mt-2">Data ini sudah diterima. Jika ada masalah yang baru ditemukan, gunakan `Revisi` agar club memperbaiki data.</div>
        @endif
    </form>
@elseif (!auth()->user()->isAdmin() && $item->canBeSubmittedByClub())
    <form method="POST" action="{{ $submitRoute }}" class="w-100 review-actions-form">
        @csrf
        <label class="form-label form-label-sm text-muted mb-2 d-block">Verifikasi</label>
        <button type="submit" class="btn btn-sm btn-primary w-100 review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
            <i data-lucide="send" class="review-actions-icon" aria-hidden="true"></i>
            <span>Submit Verifikasi</span>
        </button>
    </form>
@endif

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
            <button type="submit" name="status" value="approved" class="btn btn-sm btn-success review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
                <i data-lucide="badge-check" class="fs-14"></i>
                <span>Approve</span>
            </button>
            <button type="submit" name="status" value="revision" class="btn btn-sm btn-warning review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
                <i data-lucide="rotate-ccw" class="fs-14"></i>
                <span>Revisi</span>
            </button>
            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger review-actions-button review-actions-button-danger d-inline-flex align-items-center justify-content-center gap-2">
                <i data-lucide="shield-x" class="fs-14"></i>
                <span>Reject</span>
            </button>
        </div>
    </form>
@elseif ($item->canBeSubmittedByClub())
    <form method="POST" action="{{ $submitRoute }}" class="w-100 review-actions-form">
        @csrf
        <label class="form-label form-label-sm text-muted mb-2 d-block">Verifikasi</label>
        <button type="submit" class="btn btn-sm btn-primary w-100 review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
            <i data-lucide="send" class="fs-14"></i>
            <span>Submit Verifikasi</span>
        </button>
    </form>
@endif

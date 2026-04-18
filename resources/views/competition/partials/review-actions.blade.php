@php
    $reviewItemLabel = match (class_basename($item)) {
        'Club' => 'klub',
        'Official' => 'official',
        'Player' => 'pemain',
        'LineupList' => 'DSP',
        default => 'data',
    };
    $reviewFormId = 'review-actions-form-'.strtolower(class_basename($item)).'-'.$item->getKey();
    $reviewConfirmModalId = 'review-actions-confirm-'.strtolower(class_basename($item)).'-'.$item->getKey();
@endphp

@if (auth()->user()->isAdmin() && $item->canBeReviewedByAdmin())
    <form method="POST" action="{{ $reviewRoute }}" class="w-100 review-actions-form" id="{{ $reviewFormId }}">
        @csrf
        <label class="form-label form-label-sm text-muted mb-2 d-block">Verifikasi Admin</label>
        <textarea
            name="verification_notes"
            rows="2"
            class="form-control form-control-sm mb-3 review-actions-notes"
            placeholder="Catatan admin. Wajib untuk revisi atau penolakan."
        >{{ old('verification_notes') }}</textarea>
        <div class="review-actions-grid">
            @if ($item->verification_status !== 'approved')
                <button type="submit" name="status" value="approved" class="btn btn-sm btn-success review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
                    <i data-lucide="check" class="review-actions-icon" aria-hidden="true"></i>
                    <span>Setujui</span>
                </button>
            @endif
            <button type="submit" name="status" value="revision" class="btn btn-sm btn-warning review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
                <i data-lucide="refresh-ccw" class="review-actions-icon" aria-hidden="true"></i>
                <span>Revisi</span>
            </button>
            @if ($item->verification_status !== 'approved')
                <button
                    type="button"
                    class="btn btn-sm btn-danger review-actions-button review-actions-button-danger d-inline-flex align-items-center justify-content-center gap-2"
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $reviewConfirmModalId }}"
                    data-confirm-form="#{{ $reviewFormId }}"
                    data-confirm-submit-name="status"
                    data-confirm-submit-value="rejected"
                    data-confirm-title="Tolak {{ ucfirst($reviewItemLabel) }}"
                    data-confirm-message="Tolak {{ $reviewItemLabel }} ini? Status akan berubah menjadi ditolak dan klub harus menindaklanjuti sesuai catatan admin."
                    data-confirm-submit-label="Tolak"
                    data-confirm-submit-class="btn-danger"
                >
                    <i data-lucide="x" class="review-actions-icon" aria-hidden="true"></i>
                    <span>Tolak</span>
                </button>
            @endif
        </div>
        @if ($item->verification_status === 'approved')
            <div class="small text-muted mt-2">Data ini sudah diterima. Jika ada masalah baru yang ditemukan, gunakan `Revisi` agar klub memperbaiki data.</div>
        @endif
    </form>

    @include('layouts.partials.action-confirm-modal', [
        'modalId' => $reviewConfirmModalId,
        'title' => 'Tolak '.ucfirst($reviewItemLabel),
        'message' => 'Tolak '.$reviewItemLabel.' ini? Status akan berubah menjadi ditolak dan klub harus menindaklanjuti sesuai catatan admin.',
        'submitLabel' => 'Tolak',
        'submitClass' => 'btn-danger',
    ])
@elseif (!auth()->user()->isAdmin() && $item->canBeSubmittedByClub())
    <form method="POST" action="{{ $submitRoute }}" class="w-100 review-actions-form">
        @csrf
        <label class="form-label form-label-sm text-muted mb-2 d-block">Verifikasi</label>
        <button type="submit" class="btn btn-sm btn-primary w-100 review-actions-button d-inline-flex align-items-center justify-content-center gap-2">
            <i data-lucide="send" class="review-actions-icon" aria-hidden="true"></i>
            <span>Ajukan Verifikasi</span>
        </button>
    </form>
@endif

<div
    class="modal fade"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true"
    data-delete-form="#{{ $formId }}"
    data-delete-name=".{{ $nameClass }}"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    {{ $messagePrefix }}
                    <span class="fw-semibold {{ $nameClass }}">-</span>
                    {{ $messageSuffix }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" id="{{ $formId }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-2">
                        <i data-lucide="trash-2" class="fs-14"></i>
                        <span>Hapus</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

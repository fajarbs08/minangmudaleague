@php
    $modalTitle = $title ?? 'Konfirmasi Aksi';
    $modalMessage = $message ?? 'Apakah Anda yakin ingin melanjutkan aksi ini?';
    $modalSubmitLabel = $submitLabel ?? 'Lanjutkan';
    $modalSubmitClass = $submitClass ?? 'btn-danger';
@endphp

<div
    class="modal fade"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true"
    data-default-title="{{ $modalTitle }}"
    data-default-message="{{ $modalMessage }}"
    data-default-submit-label="{{ $modalSubmitLabel }}"
    data-default-submit-class="{{ $modalSubmitClass }}"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label" data-confirm-title>{{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" data-confirm-message>{{ $modalMessage }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" id="{{ $modalId }}-internal-form" class="d-none">
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-confirm-method-input>
                </form>
                <button type="button" class="btn {{ $modalSubmitClass }}" data-confirm-submit>{{ $modalSubmitLabel }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const modalElement = document.getElementById('{{ $modalId }}');

            if (!modalElement) {
                return;
            }

            const titleNode = modalElement.querySelector('[data-confirm-title]');
            const messageNode = modalElement.querySelector('[data-confirm-message]');
            const submitButton = modalElement.querySelector('[data-confirm-submit]');
            const internalForm = modalElement.querySelector('#{{ $modalId }}-internal-form');
            const methodInput = modalElement.querySelector('[data-confirm-method-input]');

            let targetFormSelector = null;
            let submitName = null;
            let submitValue = null;

            modalElement.addEventListener('show.bs.modal', (event) => {
                const source = event.relatedTarget || modalElement;
                const title = source.getAttribute('data-confirm-title') || modalElement.getAttribute('data-default-title') || 'Konfirmasi Aksi';
                const message = source.getAttribute('data-confirm-message') || modalElement.getAttribute('data-default-message') || 'Apakah Anda yakin ingin melanjutkan aksi ini?';
                const label = source.getAttribute('data-confirm-submit-label') || modalElement.getAttribute('data-default-submit-label') || 'Lanjutkan';
                const submitClass = source.getAttribute('data-confirm-submit-class') || modalElement.getAttribute('data-default-submit-class') || 'btn-danger';
                const action = source.getAttribute('data-confirm-action') || '';
                const method = (source.getAttribute('data-confirm-method') || 'POST').toUpperCase();

                targetFormSelector = source.getAttribute('data-confirm-form') || null;
                submitName = source.getAttribute('data-confirm-submit-name') || null;
                submitValue = source.getAttribute('data-confirm-submit-value') || null;

                titleNode.textContent = title;
                messageNode.textContent = message;
                submitButton.textContent = label;
                submitButton.className = `btn ${submitClass}`;

                internalForm.setAttribute('action', action);
                internalForm.setAttribute('method', method === 'GET' ? 'GET' : 'POST');
                methodInput.value = ['GET', 'POST'].includes(method) ? 'POST' : method;
            });

            submitButton.addEventListener('click', () => {
                if (targetFormSelector) {
                    const targetForm = document.querySelector(targetFormSelector);

                    if (!targetForm) {
                        return;
                    }

                    targetForm.querySelectorAll('[data-confirm-temp-input="1"]').forEach((node) => node.remove());

                    if (submitName) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = submitName;
                        hiddenInput.value = submitValue || '';
                        hiddenInput.setAttribute('data-confirm-temp-input', '1');
                        targetForm.appendChild(hiddenInput);
                    }

                    targetForm.dataset.confirmAccepted = '1';
                    targetForm.requestSubmit();
                    bootstrap.Modal.getInstance(modalElement)?.hide();

                    return;
                }

                internalForm.requestSubmit();
            });
        })();
    </script>
@endpush

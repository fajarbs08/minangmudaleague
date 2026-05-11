@if (session('status'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1095; max-width: min(100vw, 420px);">
        <div
            class="toast border-0 shadow-sm"
            role="status"
            aria-live="polite"
            aria-atomic="true"
            data-bs-autohide="true"
            data-bs-delay="3500"
            data-flash-toast
        >
            <div class="toast-header bg-success text-white border-0">
                <strong class="me-auto">Berhasil</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Tutup"></button>
            </div>
            <div class="toast-body bg-white text-dark">
                {{ session('status') }}
            </div>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-flash-toast]').forEach(function (node) {
                    bootstrap.Toast.getOrCreateInstance(node).show();
                });
            });
            </script>
        @endpush
    @endonce
@endif

@if ($errors->any())
    @php
        $alertTitle = $errors->has('match') && count($errors->all()) === 1
            ? 'Tindakan ini tidak dapat diproses:'
            : 'Periksa kembali input berikut:';
    @endphp
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">{{ $alertTitle }}</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

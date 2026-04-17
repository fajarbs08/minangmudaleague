@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
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

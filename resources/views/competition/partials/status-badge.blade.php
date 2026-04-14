@php
    $classes = match ($status) {
        'approved' => 'bg-success-subtle text-success',
        'submitted' => 'bg-warning-subtle text-warning',
        'revision' => 'bg-info-subtle text-info',
        'rejected' => 'bg-danger-subtle text-danger',
        default => 'bg-secondary-subtle text-secondary',
    };

    $label = match ($status) {
        'approved' => 'Diterima',
        'submitted' => 'Dalam Proses',
        'revision' => 'Perlu Revisi',
        'rejected' => 'Ditolak',
        default => 'Draft',
    };
@endphp
<span class="badge {{ $classes }}">{{ $label }}</span>

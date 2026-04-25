@php
    $classes = match ($status) {
        'approved' => 'lap-admin-chip lap-admin-chip-approved',
        'submitted' => 'lap-admin-chip lap-admin-chip-pending',
        'revision' => 'lap-admin-chip lap-admin-chip-revision',
        'rejected' => 'lap-admin-chip lap-admin-chip-danger',
        default => 'lap-admin-chip lap-admin-chip-draft',
    };

    $label = match ($status) {
        'approved' => 'Diterima',
        'submitted' => 'Dalam Proses',
        'revision' => 'Perlu Revisi',
        'rejected' => 'Ditolak',
        default => 'Draft',
    };
@endphp
<span class="{{ $classes }}">{{ $label }}</span>

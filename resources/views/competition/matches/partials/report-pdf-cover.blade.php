@php
    $filterLabel = $selectedAgeGroup?->name ?: 'Semua kelompok usia';
    $documentSource = $documentSource ?? 'Dashboard Kompetisi';
    $reportLogoPath = public_path('images/logo-dark.png');
@endphp

<div class="report-block">
    <table class="report-frame report-head">
        <tr>
            <td rowspan="2" style="width: 22%;">
                <div class="report-logo-box">
                    @if (is_file($reportLogoPath))
                        <img src="{{ $reportLogoPath }}" alt="Logo Liga Anak Piaman Laweh">
                    @endif
                    <div class="report-brand">
                        Kompetisi
                        <strong>Liga Anak Piaman Laweh</strong>
                    </div>
                </div>
            </td>
            <td style="width: 56%;">
                <div class="report-head-title">{{ $documentTitle }}</div>
            </td>
            <td rowspan="2" style="width: 22%;">
                <div class="report-logo-box">
                    @if (is_file($reportLogoPath))
                        <img src="{{ $reportLogoPath }}" alt="Logo Liga Anak Piaman Laweh">
                    @endif
                    <div class="report-side-note">
                        Kelompok Usia
                        <strong>{{ strtoupper($filterLabel) }}</strong>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="report-head-subtitle">{{ $documentSubtitle }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="report-block">
    <table class="report-frame report-meta">
        <tr>
            <td class="label">Dokumen</td>
            <td class="sep">:</td>
            <td class="value-wide">{{ $documentTitle }}</td>
            <td class="label">Kelompok Usia</td>
            <td class="sep">:</td>
            <td class="value">{{ $filterLabel }}</td>
        </tr>
        <tr>
            <td class="label">Digenerate</td>
            <td class="sep">:</td>
            <td class="value-wide">{{ $generatedAt->format('d M Y H:i') }} WIB</td>
            <td class="label">Sumber</td>
            <td class="sep">:</td>
            <td class="value">{{ $documentSource }}</td>
        </tr>
    </table>
</div>

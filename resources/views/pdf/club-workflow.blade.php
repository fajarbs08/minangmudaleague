<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tahapan Workflow Dashboard Club</title>
    <style>
        @page {
            margin: 28px 28px 32px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #f6f4ef;
            color: #18212b;
            font-size: 11px;
            line-height: 1.45;
        }

        .page {
            width: 100%;
            padding: 0 2px;
        }

        .cover {
            width: 100%;
            border: 1px solid #d9d2c6;
            background: #fcfbf8;
            padding: 0;
            margin-bottom: 16px;
        }

        .cover-top {
            padding: 24px 22px 18px;
            border-bottom: 1px solid #e4ddd2;
        }

        .eyebrow {
            margin: 0 0 8px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.1px;
            color: #8a6a43;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #142033;
            line-height: 1.1;
        }

        .hero-copy {
            margin: 8px 0 0;
            font-size: 10px;
            color: #5d6775;
        }

        .hero-rule {
            width: 70px;
            height: 2px;
            margin-top: 14px;
            background: #8a6a43;
        }

        .cover-bottom {
            padding: 16px 18px 18px;
        }

        .tracker {
            width: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            margin-bottom: 14px;
        }

        .section-title {
            margin: 0 0 10px;
            font-size: 12px;
            font-weight: 800;
            color: #142033;
        }

        .tracker-row {
            width: 100%;
            margin-bottom: 0;
        }

        .tracker-step {
            float: left;
            width: calc((100% - 30px) / 6);
            margin-right: 6px;
            padding-right: 0;
        }

        .tracker-step:last-child {
            margin-right: 0;
        }

        .tracker-chip {
            border: 1px solid #d7d0c4;
            background: #f3efe7;
            padding: 8px 7px;
            min-height: 56px;
            text-align: center;
        }

        .tracker-index {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 10px;
            font-weight: 800;
            color: #fff;
            background: #102944;
            margin-bottom: 8px;
        }

        .tracker-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: #374151;
        }

        .cta-panel {
            width: 100%;
            border: 1px solid #d9d2c6;
            background: #f5f1e8;
            color: #142033;
            padding: 12px 14px;
            margin-bottom: 0;
        }

        .cta-title {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 800;
        }

        .cta-copy {
            margin: 0;
            font-size: 9.8px;
            color: #5b6472;
        }

        .step-card {
            width: 100%;
            border: 1px solid #d9d2c6;
            background: #fcfbf8;
            margin-bottom: 14px;
            page-break-inside: auto;
        }

        .step-card + .step-card {
            page-break-before: always;
        }

        .step-head {
            padding: 12px 14px 10px;
            border-bottom: 1px solid #e2dbd0;
            background: #f7f3ec;
        }

        .step-kicker {
            margin: 0 0 6px;
            font-size: 9px;
            color: #8a6a43;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .step-title {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 800;
            color: #142033;
        }

        .step-desc {
            margin: 0;
            font-size: 10px;
            color: #5d6775;
        }

        .step-media {
            padding: 10px 14px 8px;
        }

        .shot-title {
            margin: 0 0 4px;
            font-size: 10px;
            font-weight: 800;
            color: #8a6a43;
        }

        .shot-caption {
            margin: 0 0 6px;
            font-size: 9.4px;
            color: #5b6472;
        }

        .shot-image {
            width: 100%;
            border: 1px solid #ddd4c8;
            display: block;
            margin-top: 2px;
        }

        .step-body {
            padding: 2px 14px 14px;
        }

        .body-col {
            float: left;
            width: calc((100% - 18px) / 2);
        }

        .body-col.right {
            margin-left: 18px;
        }

        .mini-title {
            margin: 0 0 8px;
            font-size: 10px;
            font-weight: 800;
            color: #142033;
        }

        .check-list {
            margin: 0 0 8px 14px;
            padding: 0;
            font-size: 9.7px;
            color: #334155;
        }

        .check-list li {
            margin-bottom: 4px;
        }

        .action-box {
            border: 1px solid #d8d1c6;
            background: #f6f2eb;
            padding: 8px 9px;
            margin-bottom: 8px;
        }

        .action-label {
            margin: 0 0 4px;
            font-size: 9px;
            font-weight: 800;
            color: #8a6a43;
            text-transform: uppercase;
            letter-spacing: .9px;
        }

        .action-text {
            margin: 0;
            font-size: 10px;
            font-weight: 700;
            color: #142033;
        }

        .badge-row {
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 5px 8px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 9px;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .badge-draft {
            background: #f3ebe0;
            color: #8a6a43;
            border-color: #decdb5;
        }

        .badge-submitted {
            background: #e8f0ff;
            color: #2563eb;
            border-color: #c8dafd;
        }

        .badge-revision {
            background: #fff2df;
            color: #b45309;
            border-color: #f0d3ab;
        }

        .badge-rejected {
            background: #fde9e9;
            color: #c81e1e;
            border-color: #efc3c3;
        }

        .badge-approved {
            background: #e9f7ef;
            color: #0f8f56;
            border-color: #c6e6d2;
        }

        .review-grid {
            width: 100%;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .review-card {
            float: left;
            width: calc((100% - 24px) / 3);
            margin-right: 12px;
            border: 1px solid #d9d2c6;
            background: #fcfbf8;
            padding: 10px 10px 8px;
            min-height: 118px;
        }

        .review-card.last {
            margin-right: 0;
        }

        .review-title {
            margin: 0 0 8px;
            font-size: 11px;
            font-weight: 800;
        }

        .review-text {
            margin: 0;
            font-size: 9.6px;
            color: #475467;
        }

        .check-panel {
            width: 100%;
            border: 1px solid #d9d2c6;
            background: #fcfbf8;
            padding: 12px 14px;
            margin-top: 12px;
            page-break-inside: auto;
        }

        .footnote {
            margin-top: 16px;
            width: 100%;
            padding: 10px 12px;
            background: #eae6de;
            border: 1px solid #d7cec2;
            font-size: 9.6px;
            color: #5c6674;
        }

        .clearfix {
            clear: both;
        }

        .page-break {
            page-break-before: always;
        }

        .intro-list {
            margin: 0;
            padding-left: 16px;
            font-size: 9.8px;
            color: #475467;
        }

        .intro-list li {
            margin-bottom: 4px;
        }

        .intro-col {
            float: left;
            width: calc((100% - 18px) / 2);
        }

        .intro-col.right {
            margin-left: 18px;
        }
    </style>
</head>
<body>
@php
    $tracker = [
        ['index' => '1', 'label' => 'Akun'],
        ['index' => '2', 'label' => 'Klub'],
        ['index' => '3', 'label' => 'Official'],
        ['index' => '4', 'label' => 'Pemain'],
        ['index' => '5', 'label' => 'DSP'],
        ['index' => '6', 'label' => 'Submit'],
    ];

    $stepActions = [
        '1' => 'Masuk ke dashboard lalu buka modul yang akan dikerjakan.',
        '2' => 'Simpan Draft sampai seluruh dokumen klub siap, lalu Ajukan Verifikasi.',
        '3' => 'Simpan Draft official satu per satu, lalu cek ulang sebelum diajukan.',
        '4' => 'Lengkapi data pemain, unggah dokumen, lalu simpan sebelum submit.',
        '5' => 'Susun starter dan cadangan, lalu simpan DSP setelah roster valid.',
        '6' => 'Ajukan Verifikasi hanya jika checklist lengkap sudah terpenuhi.',
        '7' => 'Perbaiki data, submit ulang, atau lanjut ke modul berikutnya sesuai status.',
    ];

    $stepCtas = [
        '1' => 'CTA: Buka Dashboard',
        '2' => 'CTA: Simpan Draft / Ajukan Verifikasi',
        '3' => 'CTA: Simpan Draft Official',
        '4' => 'CTA: Simpan Draft Pemain',
        '5' => 'CTA: Simpan DSP',
        '6' => 'CTA: Ajukan Verifikasi',
        '7' => 'CTA: Perbaiki & Submit Ulang',
    ];
@endphp

<div class="page">
    <div class="cover">
        <div class="cover-top">
            <p class="eyebrow">Panduan Akun Club</p>
            <p class="hero-title">Workflow Registrasi Club</p>
            <p class="hero-copy">Panduan ini bersifat statis dan dipakai sebagai acuan urutan kerja akun club dari awal sampai proses verifikasi selesai.</p>
            <div class="hero-rule"></div>
        </div>

        <div class="cover-bottom">
            <div class="tracker">
                <p class="section-title">Urutan Workflow</p>
                <div class="tracker-row">
                    @foreach ($tracker as $item)
                        <div class="tracker-step">
                            <div class="tracker-chip">
                                <span class="tracker-index">{{ $item['index'] }}</span>
                                <span class="tracker-label">{{ $item['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="intro-col">
                <p class="section-title">Cara Membaca Panduan</p>
                <ul class="intro-list">
                    <li>Ikuti tahap sesuai urutan dari atas ke bawah.</li>
                    <li>Gunakan screenshot bertanda untuk mengenali area kerja.</li>
                    <li>Jalankan checklist dan aksi pada tiap tahap.</li>
                </ul>
            </div>

            <div class="intro-col right">
                <p class="section-title">Ringkasan Alur Tetap</p>
                <ul class="intro-list">
                    <li>Lengkapi klub, official, pemain, lalu DSP.</li>
                    <li>Ajukan verifikasi hanya saat data sudah lengkap.</li>
                    <li>Tindak lanjuti hasil review sampai diterima.</li>
                </ul>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="page-break"></div>

    @foreach ($steps as $step)
        <div class="step-card">
            <div class="step-head">
                <p class="step-kicker">Tahap {{ $step['number'] }}</p>
                <p class="step-title">{{ $step['title'] }}</p>
                <p class="step-desc">{{ $step['description'] }}</p>
            </div>

            @if (!empty($step['screenshot']))
                <div class="step-media">
                    <p class="shot-title">{{ $step['screenshot']['title'] }}</p>
                    <p class="shot-caption">{{ $step['screenshot']['caption'] }}</p>
                    <img src="{{ $step['screenshot']['path'] }}" alt="{{ $step['screenshot']['title'] }}" class="shot-image">
                </div>
            @endif

            <div class="step-body">
                <div class="body-col">
                    <p class="mini-title">Checklist tahap ini</p>
                    <ul class="check-list">
                        @foreach (($step['details'] ?? []) as $detail)
                            <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="body-col right">
                    <div class="action-box">
                        <p class="action-label">{{ $stepCtas[$step['number']] ?? 'CTA' }}</p>
                        <p class="action-text">{{ $stepActions[$step['number']] ?? 'Lanjutkan ke langkah berikutnya.' }}</p>
                    </div>

                    @if ($step['number'] === '7')
                        <p class="mini-title">Status yang akan terlihat</p>
                        <div class="badge-row">
                            <span class="badge badge-draft">Draft</span>
                            <span class="badge badge-submitted">Dalam Proses</span>
                            <span class="badge badge-revision">Perlu Revisi</span>
                            <span class="badge badge-rejected">Ditolak</span>
                            <span class="badge badge-approved">Diterima</span>
                        </div>
                    @endif

                    @if (!empty($step['result']))
                        <div class="action-box">
                            <p class="action-label">Output tahap</p>
                            <p class="action-text">{{ $step['result'] }}</p>
                        </div>
                    @endif
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    @endforeach

    <div class="check-panel">
        <p class="section-title">Arti Data Lengkap Sebelum Submit</p>
        <ul class="check-list">
            @foreach ($completionChecks as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>

    <div class="review-grid">
        @foreach ($statusGuides as $guide)
            <div class="review-card{{ $loop->last ? ' last' : '' }}">
                <p class="review-title" style="color: {{ $guide['color'] }};">{{ $guide['label'] }}</p>
                <p class="review-text">{{ $guide['body'] }}</p>
            </div>
        @endforeach
        <div class="clearfix"></div>
    </div>

    <div class="check-panel">
        <p class="section-title">Yang Harus Dilakukan Saat Ditolak</p>
        <ul class="check-list">
            @foreach ($rejectedActions as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>

    <div class="footnote">
        Panduan ini disusun dari implementasi workflow verifikasi pada modul klub, official, pemain, dan DSP.
        Versi PDF dibuat {{ $generatedAt->format('d M Y H:i') }}.
    </div>
</div>
</body>
</html>

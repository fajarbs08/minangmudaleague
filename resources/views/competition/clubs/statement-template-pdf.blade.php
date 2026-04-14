<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Kesanggupan Peserta</title>
    <style>
        @page { margin: 22mm 20mm 18mm; }

        .page {
            width: 100%;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 11.5px;
            line-height: 1.5;
        }

        .page { width: 100%; }

        .letterhead {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
        }

        .letterhead-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            margin-bottom: 6px;
        }

        .title {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .intro { margin: 0 0 8px; text-align: justify; }

        .meta { margin: 6px 0 10px; }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-label {
            width: 140px;
            padding: 2px 0;
            color: #374151;
        }

        .meta-separator { width: 8px; }

        .meta-value {
            padding: 2px 0;
            font-weight: 600;
        }

        .clauses {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .clause-item {
            position: relative;
            padding-left: 22px;
            margin-bottom: 4px;
            text-align: justify;
        }
        .clause-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 18px;
        }
        .clause-text { display: block; }

        .signature-date {
            margin-top: 20px;
            margin-bottom: 12px;
        }

        .signatures { width: 100%; }

        .sign-col {
            width: 47%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }

        .sign-col.right { margin-left: 5%; }
        .sign-role { margin-bottom: 2px; }
        .sign-space { height: 90px; position: relative; }
        .line-placeholder { display: none; }
        .sign-name {
            margin: 6px 0 0;
            font-weight: 700;
            text-transform: uppercase;
        }
        .sign-caption { margin-top: 0; font-size: 10.5px; }
    </style>
</head>
<body>
@php
    $placeholder = '........................................................';
    $clubName = $club?->name ?: $placeholder;
    $managerName = $club?->manager_name ?: ($user->name ?: $placeholder);
    $managerTitle = $club?->manager_title ?: 'Ketua (Penanggung Jawab)';
    $ageGroup = $club?->statement_age_group ?: $placeholder;
    $mailingAddress = $club?->mailing_address ?: $club?->address ?: $placeholder;
    $contact = $club?->statement_contact ?: ($user->email ?: $placeholder);
    $city = $club?->city ?: '........................';
    $clubLogoPath = $club?->logo_url;
    if ($clubLogoPath && !str_starts_with($clubLogoPath, 'http')) {
        $clubLogoPath = str_starts_with($clubLogoPath, 'demo-images/')
            ? public_path($clubLogoPath)
            : public_path('storage/'.$clubLogoPath);
    }
    $clubLogo = $clubLogoPath;
    $witnessName = $club?->statement_witness_name ?: $placeholder;
    $witnessTitle = $club?->statement_witness_title ?: 'Manager Team / Admin Club';
@endphp
    <div class="page">
        <div class="letterhead">
            <img src="{{ public_path('images/logo-full-transparent.png') }}" alt="Minang Muda League" class="letterhead-logo">
            <div class="title">SURAT PERNYATAAN KESANGGUPAN PESERTA</div>
        </div>

        <div class="meta">
            <p class="intro">Saya yang bertanda tangan di bawah ini, bertindak sebagai {{ $managerTitle }}</p>
            <table class="meta-table">
                <tr>
                    <td class="meta-label">Tim</td>
                    <td class="meta-separator">:</td>
                    <td class="meta-value">{{ $clubName }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Kelompok Umur</td>
                    <td class="meta-separator">:</td>
                    <td class="meta-value">{{ $ageGroup }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Alamat Bersurat</td>
                    <td class="meta-separator">:</td>
                    <td class="meta-value">{{ $mailingAddress }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Telp/email</td>
                    <td class="meta-separator">:</td>
                    <td class="meta-value">{{ $contact }}</td>
                </tr>
            </table>
        </div>

        <div class="clauses">
            <div class="clause-item">
                <span class="clause-number">1.</span>
                <span class="clause-text">Menyatakan bersedia mengikuti kompetisi MINANG MUDA LEAGUE musim berjalan dengan mematuhi seluruh peraturan yang ditetapkan oleh panitia maupun yang tertuang dalam regulasi atau manual liga.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">2.</span>
                <span class="clause-text">Menyatakan bertanggung jawab dalam menjaga iklim sportivitas, fair-play, dan respek yang melekat kepada tim yang saya pimpin, baik di dalam maupun di luar lapangan, sebelum dan sesudah pertandingan, mencakup perilaku pemain, official, dan suporter.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">3.</span>
                <span class="clause-text">Menyatakan sanggup melunasi uang pendaftaran sesuai nominal yang ditetapkan panitia dengan cara transfer ke rekening resmi penyelenggara.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">4.</span>
                <span class="clause-text">Transfer pembayaran dilakukan dengan menyertakan kode atau angka unik pada dua digit terakhir yang menunjukkan kelompok umur yang diikuti, sesuai petunjuk panitia.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">5.</span>
                <span class="clause-text">Menyatakan sanggup melunasi biaya registrasi pemain sesuai jumlah pemain yang didaftarkan melalui rekening resmi penyelenggara.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">6.</span>
                <span class="clause-text">Menyatakan sanggup membayar uang pertandingan sesuai ketentuan panitia untuk setiap match yang dijadwalkan selama kompetisi berlangsung.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">7.</span>
                <span class="clause-text">Bilamana tim kami mengundurkan diri sebelum maupun di tengah-tengah kompetisi berlangsung, maka kami bersedia tetap memenuhi keputusan panitia terkait biaya yang telah dibayarkan dan kewajiban lain yang masih berjalan.</span>
            </div>
            <div class="clause-item">
                <span class="clause-number">8.</span>
                <span class="clause-text">Bilamana tim kami mengundurkan diri ketika kompetisi sedang berjalan, maka kami tetap akan memenuhi seluruh kewajiban administrasi sampai kompetisi selesai.</span>
            </div>
        </div>

        <div class="signature-date">
            {{ $city }}, {{ $today->translatedFormat('d F Y') }}
        </div>

        <div class="signatures">
            <div class="sign-col">
                <div class="sign-role">&nbsp;</div>
                <div class="sign-space"></div>
                <div class="sign-caption">({{ $managerTitle }} {{ $clubName }})</div>
                <p class="sign-name">{{ $managerName }}</p>
            </div>
            <div class="sign-col right">
                <div class="sign-role">Mengetahui,</div>
                <div class="sign-space"></div>
                <div class="sign-caption">({{ $witnessTitle }})</div>
                <p class="sign-name">{{ $witnessName }}</p>
            </div>
        </div>
    </div>
</body>
</html>

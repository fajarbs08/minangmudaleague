<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Manual Club - Liga Anak Piaman Laweh</title>
    <style>
        @page { size: A4; margin: 14mm 12mm; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #172033;
            line-height: 1.55;
            background: #ffffff;
        }

        h1, h2, h3, p, ul { margin-top: 0; }

        .manual-sheet { width: 100%; }

        .manual-cover {
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            margin-bottom: 14px;
        }

        .manual-cover-table,
        .manual-meta-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .manual-cover-table td {
            border: 1px solid #dbe2ea;
            padding: 12px 14px;
            vertical-align: middle;
        }

        .manual-logo-wrap { text-align: center; }

        .manual-logo-wrap img {
            width: 62px;
            height: auto;
            display: block;
            margin: 0 auto 8px;
        }

        .manual-kicker {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }

        .manual-cover-title {
            margin: 0 0 6px;
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }

        .manual-cover-copy {
            margin: 0;
            text-align: center;
            color: #475569;
            font-size: 10px;
        }

        .manual-brand {
            text-align: center;
            font-size: 9px;
            color: #475569;
            text-transform: uppercase;
        }

        .manual-brand strong {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            color: #0f172a;
        }

        .manual-meta { margin-bottom: 14px; }

        .manual-meta-table td {
            border: 1px solid #dbe2ea;
            padding: 8px 10px;
            vertical-align: top;
        }

        .manual-meta-label {
            width: 16%;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
        }

        .manual-meta-sep {
            width: 2%;
            text-align: center;
            color: #64748b;
        }

        .manual-section {
            border: 1px solid #dbe2ea;
            background: #ffffff;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .manual-section-head {
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 10px 12px 8px;
        }

        .manual-section-title {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }

        .manual-section-body {
            padding: 10px 12px 12px;
        }

        .manual-subtitle {
            margin: 0 0 6px;
            font-size: 11px;
            font-weight: 800;
            color: #172033;
        }

        .manual-list {
            margin: 0;
            padding-left: 18px;
            color: #334155;
        }

        .manual-list li + li { margin-top: 4px; }

        .manual-callout {
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            padding: 10px 12px;
            color: #334155;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 10px;
        }

        .footer {
            margin-top: 16px;
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="manual-sheet">
        <div class="manual-cover">
            <table class="manual-cover-table">
                <tr>
                    <td style="width: 22%;">
                        <div class="manual-logo-wrap">
                            <img src="{{ public_path('images/logo-full-transparent.png') }}" alt="Liga Anak Piaman Laweh">
                            <div class="manual-brand">
                                Dokumen Panduan
                                <strong>Liga Anak Piaman Laweh</strong>
                            </div>
                        </div>
                    </td>
                    <td style="width: 56%;">
                        <div class="manual-kicker">Panduan Operasional</div>
                        <h1 class="manual-cover-title">Manual Club</h1>
                        <p class="manual-cover-copy">Panduan operasional untuk melengkapi profil klub, mendaftarkan official dan pemain, menyusun DSP, serta mengajukan verifikasi.</p>
                    </td>
                    <td style="width: 22%;">
                        <div class="manual-brand">
                            Diperbarui
                            <strong>{{ $generatedAt->translatedFormat('d F Y') }}</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="manual-meta">
            <table class="manual-meta-table">
                <tr>
                    <td class="manual-meta-label">Dokumen</td>
                    <td class="manual-meta-sep">:</td>
                    <td>Manual Club</td>
                    <td class="manual-meta-label">Target</td>
                    <td class="manual-meta-sep">:</td>
                    <td>Akun Club</td>
                </tr>
                <tr>
                    <td class="manual-meta-label">Kompetisi</td>
                    <td class="manual-meta-sep">:</td>
                    <td>Liga Anak Piaman Laweh</td>
                    <td class="manual-meta-label">Format</td>
                    <td class="manual-meta-sep">:</td>
                    <td>A4 PDF</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Ringkasan Peran Club</h2>
        </div>
        <div class="manual-section-body">
            <div class="manual-callout">
            Club bertugas melengkapi profil klub, mendaftarkan official dan pemain, menyusun DSP, serta mengajukan verifikasi sesuai aturan kompetisi.
            </div>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Persiapan Awal</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Terima email dan password akun club dari panitia atau admin.</li>
            <li>Login ke sistem dan pastikan menu Klub, Official, Pemain, dan DSP terlihat.</li>
            <li>Jika akses menu tidak lengkap, segera laporkan ke admin.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Langkah Wajib</h2>
        </div>
        <div class="manual-section-body">
        <h3 class="manual-subtitle">1) Lengkapi Profil Klub</h3>
        <ul class="manual-list">
            <li>Isi identitas klub: nama, nama singkat, manajer, alamat, zona, kota, dan tahun berdiri.</li>
            <li>Unggah logo klub, akta SSB, dan dokumen pendukung sesuai persyaratan.</li>
            <li>Unduh surat pernyataan, tandatangani, stempel, lalu unggah kembali.</li>
        </ul>

        <h3 class="manual-subtitle">2) Input Official</h3>
        <ul class="manual-list">
            <li>Isi identitas official: nama, peran, kontak, lisensi, dan kewarganegaraan.</li>
            <li>Unggah pas foto 3x4 dan dokumen lisensi yang valid.</li>
            <li>Set kelompok usia dan jabatan per kelompok usia jika diperlukan.</li>
        </ul>

        <h3 class="manual-subtitle">3) Input Pemain</h3>
        <ul class="manual-list">
            <li>Isi data identitas pemain lengkap: nama, tempat/tanggal lahir, sekolah, tinggi, berat, dan dominant foot.</li>
            <li>Unggah dokumen: pas foto, KK, akta kelahiran, ijazah/rapor sesuai aturan kompetisi.</li>
            <li>Set kelompok usia, posisi, dan nomor punggung untuk tiap kelompok usia.</li>
        </ul>

        <h3 class="manual-subtitle">4) Susun DSP</h3>
        <ul class="manual-list">
            <li>Pilih match dan kelompok usia yang tersedia.</li>
            <li>Isi tepat 11 starter dan maksimal 9 cadangan sesuai aturan sistem.</li>
            <li>Pastikan urutan roster benar, termasuk nomor punggung jika custom.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Submit Verifikasi</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Submit hanya jika semua data lengkap dan dokumen valid.</li>
            <li>Status <span class="badge">Perlu Revisi</span> berarti harus perbaiki semua catatan admin.</li>
            <li>Status <strong>Diterima</strong> berarti data sudah bisa dipakai untuk tahap berikutnya.</li>
            <li>Status <strong>Ditolak</strong> berarti data belum diterima dan harus diperbaiki menyeluruh.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Checklist Sebelum Submit</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Semua dokumen dapat dibuka, jelas, dan tidak buram.</li>
            <li>Data identitas konsisten dengan dokumen yang diunggah.</li>
            <li>Nomor punggung dan posisi terisi untuk setiap kelompok usia.</li>
            <li>DSP sesuai aturan jumlah starter dan cadangan.</li>
            <li>Catatan admin pada revisi sebelumnya sudah diperbaiki seluruhnya.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Tips Upload Dokumen</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Gunakan scan atau foto beresolusi cukup, tanpa blur.</li>
            <li>Pastikan nama dan tanggal lahir terlihat jelas.</li>
            <li>Gunakan format file yang didukung (PDF/JPG/PNG sesuai aturan).</li>
            <li>Hindari file yang terlalu besar agar upload tidak gagal.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Tindak Lanjut Hasil Review</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Jika diterima, data siap digunakan untuk tahap berikutnya.</li>
            <li>Jika revisi, baca catatan admin, perbaiki semua bagian, lalu submit ulang.</li>
            <li>Jika ditolak, hubungi panitia untuk memastikan tindakan yang harus diambil.</li>
        </ul>
        </div>
    </div>

    <div class="footer">
        Manual Club • Liga Anak Piaman Laweh
    </div>
    </div>
</body>
</html>

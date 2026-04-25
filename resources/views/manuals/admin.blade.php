<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Manual Admin - Liga Anak Piaman Laweh</title>
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

        .manual-sheet {
            width: 100%;
        }

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

        .manual-logo-wrap {
            text-align: center;
        }

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

        .manual-meta {
            margin-bottom: 14px;
        }

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

        .manual-list li + li {
            margin-top: 4px;
        }

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
            background: #eef2ff;
            color: #3730a3;
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
                        <h1 class="manual-cover-title">Manual Admin</h1>
                        <p class="manual-cover-copy">Panduan ringkas untuk pengelolaan akun, review verifikasi, jadwal pertandingan, dan kontrol kualitas data kompetisi.</p>
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
                    <td>Manual Admin</td>
                    <td class="manual-meta-label">Target</td>
                    <td class="manual-meta-sep">:</td>
                    <td>Admin Kompetisi</td>
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
            <h2 class="manual-section-title">Ringkasan Peran Admin</h2>
        </div>
        <div class="manual-section-body">
            <div class="manual-callout">
            Admin bertanggung jawab atas penyediaan akun club, pengelolaan jadwal pertandingan, proses review semua submission, serta memastikan seluruh data kompetisi konsisten sebelum kick-off.
            </div>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Akses & Navigasi</h2>
        </div>
        <div class="manual-section-body">
            <ul class="manual-list">
            <li>Login menggunakan akun admin yang disiapkan panitia utama.</li>
            <li>Menu utama admin: Dashboard, Klub, Ofisial, Pemain, DSP, Manajemen Akun, dan Jadwal Match.</li>
            <li>Gunakan dashboard untuk melihat antrian review dan ringkasan statistik.</li>
            <li>Gunakan filter status pada setiap modul untuk fokus pada item yang perlu review.</li>
            </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Alur Kerja Utama</h2>
        </div>
        <div class="manual-section-body">
        <h3 class="manual-subtitle">1) Manajemen Akun</h3>
        <ul class="manual-list">
            <li>Buka menu <span class="badge">Manajemen Akun</span> untuk membuat akun admin tambahan.</li>
            <li>Buat akun club untuk tiap pendaftar, lalu kirim kredensial via kanal resmi panitia.</li>
            <li>Pastikan format email akun club konsisten agar mudah dilacak.</li>
        </ul>

        <h3 class="manual-subtitle">2) Jadwal Match</h3>
        <ul class="manual-list">
            <li>Buat match dengan klub A & B, kelompok usia, venue, tanggal, jam, dan matchday.</li>
            <li>Hindari bentrok jadwal untuk klub atau kelompok usia yang sama.</li>
            <li>Pastikan matchday unik dan mudah dipahami panitia lapangan.</li>
        </ul>

        <h3 class="manual-subtitle">3) Review & Verifikasi</h3>
        <ul class="manual-list">
            <li>Review data yang berstatus <strong>Dalam Proses</strong>.</li>
            <li>Beri keputusan: <strong>Diterima</strong>, <strong>Perlu Revisi</strong>, atau <strong>Ditolak</strong>.</li>
            <li>Catatan review wajib jelas dan spesifik agar club bisa memperbaiki dengan cepat.</li>
            <li>Jangan menyetujui item jika ada dokumen kosong, blur, atau data tidak konsisten.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Definisi Status</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li><strong>Draft</strong>: data tersimpan, belum diajukan verifikasi.</li>
            <li><strong>Dalam Proses</strong>: data diajukan ke admin dan masuk antrian review.</li>
            <li><strong>Perlu Revisi</strong>: admin meminta perbaikan, club wajib submit ulang.</li>
            <li><strong>Diterima</strong>: data lolos verifikasi dan valid untuk kompetisi.</li>
            <li><strong>Ditolak</strong>: data tidak bisa diterima, perlu tindakan lanjut dan komunikasi.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Standar Review Per Modul</h2>
        </div>
        <div class="manual-section-body">
        <h3 class="manual-subtitle">Klub</h3>
        <ul class="manual-list">
            <li>Nama, alamat, penanggung jawab, dan dokumen wajib sesuai identitas klub.</li>
            <li>Logo dan dokumen resmi harus terbaca jelas dan tidak terpotong.</li>
            <li>Surat pernyataan wajib diunggah dengan tanda tangan dan cap.</li>
        </ul>

        <h3 class="manual-subtitle">Ofisial</h3>
        <ul class="manual-list">
            <li>Nama, kontak, nomor lisensi, dan jabatan sesuai dokumen.</li>
            <li>Pas foto 3x4 jelas, bukan foto buram atau berlatar kompleks.</li>
            <li>Kelompok usia dan jabatan konsisten dengan kebutuhan tim.</li>
        </ul>

        <h3 class="manual-subtitle">Pemain</h3>
        <ul class="manual-list">
            <li>Data identitas cocok dengan akta, rapor, atau dokumen sekolah.</li>
            <li>Nomor punggung dan posisi terisi untuk setiap kelompok usia.</li>
            <li>Dokumen pendukung dapat dibuka dan sesuai nama pemain.</li>
        </ul>

        <h3 class="manual-subtitle">DSP</h3>
        <ul class="manual-list">
            <li>Starter tepat 11 pemain, cadangan mengikuti batas maksimal.</li>
            <li>Roster dan urutan pemain sesuai aturan yang ditetapkan panitia.</li>
            <li>Match, tanggal, dan kelompok usia sesuai jadwal resmi.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Penanganan Revisi & Penolakan</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Isi catatan review dengan alasan spesifik dan langkah perbaikan yang diminta.</li>
            <li>Jika ditolak, pastikan club memahami apakah data perlu dibuka ulang.</li>
            <li>Setelah revisi, cek ulang seluruh field yang terkait sebelum menerima.</li>
            <li>Catat pola kesalahan yang sering terjadi untuk bahan briefing club.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Checklist Go-Live</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Semua club aktif memiliki profil lengkap dan status Diterima.</li>
            <li>Ofisial dan pemain untuk tiap klub sudah diverifikasi.</li>
            <li>Jadwal match lengkap dan DSP untuk pertandingan aktif sudah disetujui.</li>
            <li>PDF ID Card dan DSP dapat diakses serta diunduh tanpa error.</li>
        </ul>
        </div>
    </div>

    <div class="manual-section">
        <div class="manual-section-head">
            <h2 class="manual-section-title">Troubleshooting Cepat</h2>
        </div>
        <div class="manual-section-body">
        <ul class="manual-list">
            <li>Dokumen tidak terbaca: minta unggah ulang dengan file baru.</li>
            <li>Data tidak konsisten: minta club mengikuti dokumen resmi.</li>
            <li>DSP tidak valid: cek jumlah starter/cadangan dan urutan roster.</li>
            <li>Status tidak berubah: minta club submit ulang setelah revisi.</li>
        </ul>
        </div>
    </div>

    <div class="footer">
        Manual Admin • Liga Anak Piaman Laweh
    </div>
    </div>
</body>
</html>

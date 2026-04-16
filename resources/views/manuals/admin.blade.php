<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Manual Admin - Liga Anak Piaman Laweh</title>
    <style>
        @page { margin: 22mm 20mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; line-height: 1.6; }
        h1 { font-size: 18px; margin: 0 0 8px; text-align: center; }
        h2 { font-size: 14px; margin: 16px 0 6px; }
        h3 { font-size: 12px; margin: 12px 0 4px; }
        .muted { color: #6b7280; }
        .section { margin-bottom: 12px; }
        .list { margin: 6px 0 0 16px; }
        .header { text-align: center; margin-bottom: 16px; }
        .header img { width: 60px; height: auto; margin-bottom: 6px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #eef2ff; color: #3730a3; font-size: 11px; }
        .footer { margin-top: 18px; font-size: 10px; text-align: right; color: #6b7280; }
        .callout { border: 1px solid #e5e7eb; background: #f9fafb; padding: 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo-full-transparent.png') }}" alt="Liga Anak Piaman Laweh">
        <h1>Manual Admin</h1>
        <div class="muted">Liga Anak Piaman Laweh</div>
        <div class="muted">Diperbarui: {{ $generatedAt->translatedFormat('d F Y') }}</div>
    </div>

    <div class="section">
        <h2>Ringkasan Peran Admin</h2>
        <div class="callout">
            Admin bertanggung jawab atas penyediaan akun club, pengelolaan jadwal pertandingan, proses review semua submission, serta memastikan seluruh data kompetisi konsisten sebelum kick-off.
        </div>
    </div>

    <div class="section">
        <h2>Akses & Navigasi</h2>
        <ul class="list">
            <li>Login menggunakan akun admin yang disiapkan panitia utama.</li>
            <li>Menu utama admin: Dashboard, Klub, Official, Pemain, DSP, Manajemen Akun, dan Jadwal Match.</li>
            <li>Gunakan dashboard untuk melihat antrian review dan ringkasan statistik.</li>
            <li>Gunakan filter status pada setiap modul untuk fokus pada item yang perlu review.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Alur Kerja Utama</h2>
        <h3>1) Manajemen Akun</h3>
        <ul class="list">
            <li>Buka menu <span class="badge">Manajemen Akun</span> untuk membuat akun admin tambahan.</li>
            <li>Buat akun club untuk tiap pendaftar, lalu kirim kredensial via kanal resmi panitia.</li>
            <li>Pastikan format email akun club konsisten agar mudah dilacak.</li>
        </ul>

        <h3>2) Jadwal Match</h3>
        <ul class="list">
            <li>Buat match dengan klub A & B, kelompok usia, venue, tanggal, jam, dan matchday.</li>
            <li>Hindari bentrok jadwal untuk klub atau kelompok usia yang sama.</li>
            <li>Pastikan matchday unik dan mudah dipahami panitia lapangan.</li>
        </ul>

        <h3>3) Review & Verifikasi</h3>
        <ul class="list">
            <li>Review data yang berstatus <strong>Dalam Proses</strong>.</li>
            <li>Beri keputusan: <strong>Diterima</strong>, <strong>Perlu Revisi</strong>, atau <strong>Ditolak</strong>.</li>
            <li>Catatan review wajib jelas dan spesifik agar club bisa memperbaiki dengan cepat.</li>
            <li>Jangan menyetujui item jika ada dokumen kosong, blur, atau data tidak konsisten.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Definisi Status</h2>
        <ul class="list">
            <li><strong>Draft</strong>: data tersimpan, belum diajukan verifikasi.</li>
            <li><strong>Dalam Proses</strong>: data diajukan ke admin dan masuk antrian review.</li>
            <li><strong>Perlu Revisi</strong>: admin meminta perbaikan, club wajib submit ulang.</li>
            <li><strong>Diterima</strong>: data lolos verifikasi dan valid untuk kompetisi.</li>
            <li><strong>Ditolak</strong>: data tidak bisa diterima, perlu tindakan lanjut dan komunikasi.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Standar Review Per Modul</h2>
        <h3>Klub</h3>
        <ul class="list">
            <li>Nama, alamat, penanggung jawab, dan dokumen wajib sesuai identitas klub.</li>
            <li>Logo dan dokumen resmi harus terbaca jelas dan tidak terpotong.</li>
            <li>Surat pernyataan wajib diunggah dengan tanda tangan dan cap.</li>
        </ul>

        <h3>Official</h3>
        <ul class="list">
            <li>Nama, kontak, nomor lisensi, dan jabatan sesuai dokumen.</li>
            <li>Pas foto 3x4 jelas, bukan foto buram atau berlatar kompleks.</li>
            <li>Kelompok usia dan jabatan konsisten dengan kebutuhan tim.</li>
        </ul>

        <h3>Pemain</h3>
        <ul class="list">
            <li>Data identitas cocok dengan akta, rapor, atau dokumen sekolah.</li>
            <li>Nomor punggung dan posisi terisi untuk setiap kelompok usia.</li>
            <li>Dokumen pendukung dapat dibuka dan sesuai nama pemain.</li>
        </ul>

        <h3>DSP</h3>
        <ul class="list">
            <li>Starter tepat 11 pemain, cadangan mengikuti batas maksimal.</li>
            <li>Roster dan urutan pemain sesuai aturan yang ditetapkan panitia.</li>
            <li>Match, tanggal, dan kelompok usia sesuai jadwal resmi.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Penanganan Revisi & Penolakan</h2>
        <ul class="list">
            <li>Isi catatan review dengan alasan spesifik dan langkah perbaikan yang diminta.</li>
            <li>Jika ditolak, pastikan club memahami apakah data perlu dibuka ulang.</li>
            <li>Setelah revisi, cek ulang seluruh field yang terkait sebelum menerima.</li>
            <li>Catat pola kesalahan yang sering terjadi untuk bahan briefing club.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Checklist Go-Live</h2>
        <ul class="list">
            <li>Semua club aktif memiliki profil lengkap dan status Diterima.</li>
            <li>Official dan pemain untuk tiap klub sudah diverifikasi.</li>
            <li>Jadwal match lengkap dan DSP untuk pertandingan aktif sudah disetujui.</li>
            <li>PDF ID Card dan DSP dapat diakses serta diunduh tanpa error.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Troubleshooting Cepat</h2>
        <ul class="list">
            <li>Dokumen tidak terbaca: minta unggah ulang dengan file baru.</li>
            <li>Data tidak konsisten: minta club mengikuti dokumen resmi.</li>
            <li>DSP tidak valid: cek jumlah starter/cadangan dan urutan roster.</li>
            <li>Status tidak berubah: minta club submit ulang setelah revisi.</li>
        </ul>
    </div>

    <div class="footer">
        Manual Admin • Liga Anak Piaman Laweh
    </div>
</body>
</html>

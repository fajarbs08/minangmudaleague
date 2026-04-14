<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Manual Club - Minang Muda League</title>
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
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 11px; }
        .footer { margin-top: 18px; font-size: 10px; text-align: right; color: #6b7280; }
        .callout { border: 1px solid #e5e7eb; background: #f9fafb; padding: 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo-full-transparent.png') }}" alt="Minang Muda League">
        <h1>Manual Club</h1>
        <div class="muted">Minang Muda League</div>
        <div class="muted">Diperbarui: {{ $generatedAt->translatedFormat('d F Y') }}</div>
    </div>

    <div class="section">
        <h2>Ringkasan Peran Club</h2>
        <div class="callout">
            Club bertugas melengkapi profil klub, mendaftarkan official dan pemain, menyusun DSP, serta mengajukan verifikasi sesuai aturan kompetisi.
        </div>
    </div>

    <div class="section">
        <h2>Persiapan Awal</h2>
        <ul class="list">
            <li>Terima email dan password akun club dari panitia atau admin.</li>
            <li>Login ke sistem dan pastikan menu Klub, Official, Pemain, dan DSP terlihat.</li>
            <li>Jika akses menu tidak lengkap, segera laporkan ke admin.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Langkah Wajib</h2>
        <h3>1) Lengkapi Profil Klub</h3>
        <ul class="list">
            <li>Isi identitas klub: nama, nama singkat, manajer, alamat, zona, kota, dan tahun berdiri.</li>
            <li>Unggah logo klub, akta SSB, dan dokumen pendukung sesuai persyaratan.</li>
            <li>Unduh surat pernyataan, tandatangani, stempel, lalu unggah kembali.</li>
        </ul>

        <h3>2) Input Official</h3>
        <ul class="list">
            <li>Isi identitas official: nama, peran, kontak, lisensi, dan kewarganegaraan.</li>
            <li>Unggah pas foto 3x4 dan dokumen lisensi yang valid.</li>
            <li>Set kelompok usia dan jabatan per kelompok usia jika diperlukan.</li>
        </ul>

        <h3>3) Input Pemain</h3>
        <ul class="list">
            <li>Isi data identitas pemain lengkap: nama, tempat/tanggal lahir, sekolah, tinggi, berat, dan dominant foot.</li>
            <li>Unggah dokumen: pas foto, akta, NISN/ijazah/rapor sesuai aturan kompetisi.</li>
            <li>Set kelompok usia, posisi, dan nomor punggung untuk tiap kelompok usia.</li>
        </ul>

        <h3>4) Susun DSP</h3>
        <ul class="list">
            <li>Pilih match dan kelompok usia yang tersedia.</li>
            <li>Isi tepat 11 starter dan maksimal 9 cadangan sesuai aturan sistem.</li>
            <li>Pastikan urutan roster benar, termasuk nomor punggung jika custom.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Submit Verifikasi</h2>
        <ul class="list">
            <li>Submit hanya jika semua data lengkap dan dokumen valid.</li>
            <li>Status <span class="badge">Perlu Revisi</span> berarti harus perbaiki semua catatan admin.</li>
            <li>Status <strong>Diterima</strong> berarti data sudah bisa dipakai untuk tahap berikutnya.</li>
            <li>Status <strong>Ditolak</strong> berarti data belum diterima dan harus diperbaiki menyeluruh.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Checklist Sebelum Submit</h2>
        <ul class="list">
            <li>Semua dokumen dapat dibuka, jelas, dan tidak buram.</li>
            <li>Data identitas konsisten dengan dokumen yang diunggah.</li>
            <li>Nomor punggung dan posisi terisi untuk setiap kelompok usia.</li>
            <li>DSP sesuai aturan jumlah starter dan cadangan.</li>
            <li>Catatan admin pada revisi sebelumnya sudah diperbaiki seluruhnya.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Tips Upload Dokumen</h2>
        <ul class="list">
            <li>Gunakan scan atau foto beresolusi cukup, tanpa blur.</li>
            <li>Pastikan nama dan tanggal lahir terlihat jelas.</li>
            <li>Gunakan format file yang didukung (PDF/JPG/PNG sesuai aturan).</li>
            <li>Hindari file yang terlalu besar agar upload tidak gagal.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Tindak Lanjut Hasil Review</h2>
        <ul class="list">
            <li>Jika diterima, data siap digunakan untuk tahap berikutnya.</li>
            <li>Jika revisi, baca catatan admin, perbaiki semua bagian, lalu submit ulang.</li>
            <li>Jika ditolak, hubungi panitia untuk memastikan tindakan yang harus diambil.</li>
        </ul>
    </div>

    <div class="footer">
        Manual Club • Minang Muda League
    </div>
</body>
</html>

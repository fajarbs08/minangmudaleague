# Checklist Admin Season

Dokumen ini ditujukan untuk admin non-teknis saat membuka season baru, memeriksa histori, dan memastikan data operasional tidak tercampur antar musim.

## Tujuan

- season baru dipakai untuk input operasional baru
- season lama tetap bisa dibuka sebagai histori
- histori bersifat read-only

## Sebelum Buka Season Baru

- Pastikan season lama sudah selesai dipakai untuk input pertandingan, DSP, dan verifikasi.
- Pastikan tidak ada data penting yang masih perlu diubah pada season lama.
- Pastikan panitia sudah sepakat nama season baru, misalnya `Musim 2027`.
- Pastikan backup database sudah dilakukan oleh tim teknis.

## Langkah Admin

1. Login ke dashboard admin.
2. Buka menu `Season`.
3. Periksa season aktif saat ini.
4. Klik form `Buat Season Baru`.
5. Isi `Nama`, `Slug`, `Mulai`, dan `Selesai`.
6. Klik `Simpan Season`.
7. Pastikan season baru muncul dengan status `draft`.
8. Klik `Aktifkan` pada season baru.
9. Pastikan season lama berubah menjadi `archived`.
10. Pastikan badge `Sedang Dilihat` pindah ke season baru.
11. Buka `Dashboard`, `Klub`, `Ofisial`, `Pemain`, `DSP`, `Jadwal`, dan `Hasil` untuk memastikan context season aktif sudah benar.

## Cek Setelah Aktivasi Season Baru

- Dashboard menampilkan season baru di banner context.
- Switcher season di topbar menampilkan season baru sebagai aktif.
- Tombol input baru tetap tersedia di season aktif.
- Data histori season lama tidak otomatis tercampur di dashboard season aktif.

## Cek Histori Season Lama

1. Gunakan switcher season di topbar.
2. Pilih season lama.
3. Pastikan banner menampilkan `Histori Read-Only`.
4. Buka modul `Klub`, `Ofisial`, `Pemain`, `DSP`, `Jadwal`, dan `Hasil`.
5. Pastikan tombol tambah, edit, submit, review, hapus, dan input hasil tidak muncul.
6. Pastikan detail histori tetap bisa dibuka.
7. Pastikan dokumen histori klub, pemain, dan ofisial masih bisa diunduh.

## Cek Publik Histori

Gunakan parameter `?season=<slug>` di halaman publik.

Contoh:

- `/klub?season=musim-2026`
- `/jadwal-pertandingan?season=musim-2026`
- `/hasil-pertandingan?season=musim-2026`
- `/klasemen?season=musim-2026`
- `/bagan-knockout?season=musim-2026`

Pastikan:

- halaman memuat label season histori
- link detail tetap membawa query `season`
- identitas klub di histori sesuai snapshot season lama

## Tanda Masalah

- dashboard season baru masih menampilkan data lama yang seharusnya histori
- season lama masih bisa diedit
- dokumen histori gagal diunduh
- halaman publik histori menampilkan identitas klub dari season aktif
- switcher season tidak berubah setelah dipilih

## Jika Ada Masalah

1. Catat halaman dan langkah yang gagal.
2. Ambil screenshot jika tampilan salah.
3. Laporkan season yang dipilih, URL, dan waktu kejadian ke tim teknis.
4. Jangan lanjut input operasional baru sampai masalah dikonfirmasi aman.

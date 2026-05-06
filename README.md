# Liga Anak Piaman Laweh

Sistem administrasi liga berbasis Laravel untuk pengelolaan:

- akun admin dan akun klub
- data klub, official, dan pemain
- verifikasi dokumen dan workflow revisi
- DSP dan jadwal pertandingan
- kartu identitas dan halaman publik hasil scan

## Menjalankan Proyek

1. Salin environment:
   `cp .env.example .env`
2. Install dependency:
   `composer install`
   `npm install`
3. Generate key aplikasi:
   `php artisan key:generate`
4. Jalankan migrasi:
   `php artisan migrate`
5. Jalankan server lokal:
   `php artisan serve`
   `npm run dev`

## Konfigurasi Utama

- `APP_NAME="Liga Anak Piaman Laweh"`
- `APP_URL`
- `TRUSTED_PROXIES` untuk IP/CIDR reverse proxy yang benar-benar dipakai
- kredensial database di `.env`
- konfigurasi mail bila fitur email dipakai

## Catatan Produksi

- Registrasi publik dinonaktifkan. Akun klub dibuat admin dari dashboard.
- Dokumen sensitif disimpan di private storage. Jika ada data lama, jalankan `php artisan documents:secure-sensitive-files` setelah deploy.
- Untuk deploy server biasa di belakang reverse proxy, isi `APP_URL` dan `TRUSTED_PROXIES` sesuai IP/CIDR proxy yang benar-benar dipakai.
- Gunakan cache non-Redis bawaan project: `CACHE_STORE=database`.
- Mulai dari template `./.env.production.example`, lalu salin ke `.env` di server dan isi semua secret yang sebenarnya.
- Setelah `php artisan migrate --force` dan asset selesai dibuild, aktifkan cache production dengan `composer run production:cache`.
- Jika perlu rollback cache saat troubleshooting atau deploy ulang, jalankan `composer run production:clear`.
- Jalankan worker queue terpisah di production, misalnya `php artisan queue:work database --queue=default --tries=1` lewat `systemd` atau `supervisor`.

## Dokumen Operasional

- `docs/season-admin-checklist.md`
- `docs/season-production-runbook.md`
- `scripts/season-post-migration-audit.sql`

## Catatan

Project ini menggunakan Laravel sebagai framework, tetapi branding aplikasi yang ditampilkan ke pengguna adalah `Liga Anak Piaman Laweh`.

## PDF dan Gambar

Project ini memakai `barryvdh/laravel-dompdf` untuk render PDF laporan dan kartu identitas.

Runtime yang tetap perlu tersedia:

- ekstensi PHP `imagick`
- `imagemagick`

`Imagick` dipakai sebagai fallback pemrosesan gambar agar upload dan normalisasi media tetap stabil.

## Credit

- Fajar Budi Setiawan

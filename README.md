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

## Catatan

Project ini menggunakan Laravel sebagai framework, tetapi branding aplikasi yang ditampilkan ke pengguna adalah `Liga Anak Piaman Laweh`.

## Browsershot

Project ini memakai `spatie/browsershot` untuk render PDF kartu identitas dan beberapa dokumen cetak lain.

Runtime yang perlu tersedia:

- `chromium`
- `nodejs`
- `npm`
- `puppeteer`
- `imagemagick`
- ekstensi PHP `imagick`

Contoh environment runtime:

- `ID_CARDS_CHROME_PATH=/usr/bin/chromium`
- `ID_CARDS_NODE_BINARY=/usr/bin/node`
- `ID_CARDS_NODE_MODULES_PATH=/app/storage/app/id-card-node/node_modules`
- `ID_CARDS_NO_SANDBOX=true`

Kalau render `Browsershot` gagal, service akan fallback ke `dompdf`, jadi aplikasi tetap bisa menghasilkan PDF walau kualitas layout browser-rendered tidak terpakai.

## Credit

- Fajar Budi Setiawan

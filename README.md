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
- kredensial database di `.env`
- konfigurasi mail bila fitur email dipakai

## Catatan

Project ini menggunakan Laravel sebagai framework, tetapi branding aplikasi yang ditampilkan ke pengguna adalah `Liga Anak Piaman Laweh`.

## Railway dan Browsershot

Project ini sudah memakai `spatie/browsershot` untuk render PDF kartu identitas. Di Railway, dependency runtime untuk Browsershot dipasang lewat `Dockerfile`, bukan lewat environment PHP default saja.

Runtime yang ikut dipasang di image:

- `chromium`
- `nodejs`
- `npm`
- `puppeteer`
- `imagemagick`
- ekstensi PHP `imagick`

Default environment di container:

- `ID_CARDS_CHROME_PATH=/usr/bin/chromium`
- `ID_CARDS_NODE_BINARY=/usr/bin/node`
- `ID_CARDS_NODE_MODULES_PATH=/app/storage/app/id-card-node/node_modules`
- `ID_CARDS_NO_SANDBOX=true`

Kalau render `Browsershot` gagal, service akan fallback ke `dompdf`, jadi aplikasi tetap bisa menghasilkan PDF walau kualitas layout browser-rendered tidak terpakai.

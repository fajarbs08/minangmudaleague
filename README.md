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

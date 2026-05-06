# Runbook Deploy Production Season Architecture

Dokumen ini dipakai tim teknis saat deploy fitur season-aware competition history ke production.

## Tujuan

- migrasi database berjalan aman
- data lama di-backfill ke season aktif
- season aktif tetap operasional
- histori season lama bisa dibuka tanpa bisa diubah

## Prasyarat

- branch/PR final sudah disetujui
- backup database tersedia
- backup `storage` tersedia
- akses SSH ke server production siap
- file `.env` production sudah benar

## File Penting

- `database/migrations/2026_05_05_000001_create_seasons_and_snapshot_tables.php`
- `database/migrations/2026_05_05_000002_add_season_architecture_fields.php`
- `scripts/season-post-migration-audit.sql`

## Rencana Deploy

1. Masuk ke server production.
2. Aktifkan maintenance mode jika memang diperlukan oleh operasional.
3. Backup database.
4. Backup folder `storage`.
5. Pull code terbaru.
6. Jalankan install/update dependency bila dibutuhkan.
7. Jalankan migration.
8. Bersihkan dan bangun cache production.
9. Jalankan audit pasca-migration.
10. Jalankan smoke test internal dan publik.
11. Nonaktifkan maintenance mode.

## Command Rehearsal

Gunakan urutan berikut sebagai baseline.

```bash
php artisan about
php artisan migrate --pretend
php artisan migrate --force
composer run production:cache
```

## Audit Pasca-Migration

Jalankan query dari file berikut pada database production:

```text
scripts/season-post-migration-audit.sql
```

Yang harus dipastikan:

- hanya ada satu season aktif
- semua tabel kompetisi inti punya `season_id`
- tidak ada orphan snapshot reference
- count snapshot per season masuk akal

## Smoke Test Internal

1. Login admin.
2. Buka `dashboard/season`.
3. Pastikan season aktif tampil benar.
4. Buka `Dashboard`.
5. Buka `Klub`, `Ofisial`, `Pemain`, `DSP`, `Jadwal`, `Hasil`.
6. Ganti season dari topbar ke season lama.
7. Pastikan banner berubah ke `Histori Read-Only`.
8. Pastikan tombol mutasi hilang pada histori.
9. Uji unduh dokumen histori klub, pemain, dan ofisial.

## Smoke Test Publik

Uji URL berikut dengan season histori nyata.

- `/klub?season=<slug>`
- `/klub/<slug>?season=<slug>`
- `/pemain/<slug>?season=<slug>`
- `/ofisial/<slug>?season=<slug>`
- `/jadwal-pertandingan?season=<slug>`
- `/hasil-pertandingan?season=<slug>`
- `/klasemen?season=<slug>`
- `/bagan-knockout?season=<slug>`

Pastikan:

- page load sukses
- tidak ada 500
- identitas klub histori sesuai snapshot
- link detail tetap mempertahankan `?season=`

## Rollback Trigger

Lakukan rollback atau tahan release jika terjadi salah satu kondisi berikut:

- migration gagal parsial
- season aktif salah terbaca
- data season aktif dan histori bercampur
- modul internal historis masih bisa diubah
- dokumen histori gagal diakses
- halaman publik histori memuat data season aktif

## Rollback Minimum

1. Aktifkan maintenance mode.
2. Pulihkan database dari backup sebelum deploy.
3. Pulihkan `storage` jika ada inkonsistensi file.
4. Kembalikan code ke release sebelumnya.
5. Jalankan `composer run production:clear`.
6. Nonaktifkan maintenance mode setelah aplikasi stabil.

## Catatan Operasional

- migration kedua melakukan backfill data season lama ke season aktif saat deploy pertama
- historis internal memang read-only by design
- public page tanpa query `season` tetap default ke season aktif

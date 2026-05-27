# Production Deploy

Dokumen ini merapikan deploy production supaya commit yang sedang live selalu bisa dilacak.

## Tujuan

- deploy source app dan asset publik dengan urutan yang konsisten
- tidak menimpa `public_html/index.php` khusus shared hosting
- menulis `.deploy-manifest.json` di server setelah deploy sukses
- memudahkan pengecekan `live` vs `lokal` vs `origin/main`

## Variabel Environment

Script memakai environment variable berikut:

- `DEPLOY_SSH_HOST`
- `DEPLOY_SSH_PORT`
- `DEPLOY_SSH_USER`
- `DEPLOY_REMOTE_APP_PATH`
- `DEPLOY_REMOTE_PUBLIC_PATH`

Opsional:

- `SSHPASS` untuk login berbasis password lewat `sshpass`
- `DEPLOY_BUILD=0` untuk skip `npm run build`
- `DEPLOY_COMPOSER_INSTALL=0` untuk skip `composer install` di server
- `DEPLOY_MIGRATE=0` untuk skip migration
- `DEPLOY_CACHE=0` untuk skip `composer run production:cache`
- `DEPLOY_DRY_RUN=1` untuk simulasi tanpa mengubah server

## Deploy Standar

Pastikan commit yang mau di-deploy sudah di-push ke `origin/main` karena script akan menolak deploy jika `HEAD` lokal berbeda dari `origin/main`.

```bash
export DEPLOY_SSH_HOST=145.79.14.64
export DEPLOY_SSH_PORT=65002
export DEPLOY_SSH_USER=u905705105
export DEPLOY_REMOTE_APP_PATH=/home/u905705105/domains/ligaanakpiamanlaweh.com/laravel-app
export DEPLOY_REMOTE_PUBLIC_PATH=/home/u905705105/domains/ligaanakpiamanlaweh.com/public_html
export SSHPASS='isi-password-jika-masih-pakai-password-auth'

./scripts/deploy-production.sh
```

## Cek Commit Live

```bash
./scripts/check-live-release.sh
```

Script ini membaca `.deploy-manifest.json` di server lalu menampilkan:

- commit yang sedang live
- commit `HEAD` lokal
- commit `origin/main`
- status apakah ketiganya sudah sinkron

## Catatan

- `public_html/index.php` tidak ikut ditimpa karena file itu khusus mengarah ke `laravel-app` pada shared hosting.
- `.deploy-manifest.json` disimpan di root app server dan di-ignore dari git.
- Untuk migrasi berisiko tinggi, tetap ikuti checklist di `docs/season-production-runbook.md`.

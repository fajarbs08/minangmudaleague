# Changelog

## 2026-05-06

### Harden club account visibility controls

- menambahkan `users.is_active` untuk kontrol aktivasi akun klub
- memblokir login akun klub nonaktif dan memaksa logout sesi yang sudah aktif
- menyembunyikan data aktif klub nonaktif dari konteks publik aktif
- mempertahankan visibilitas history, termasuk pertandingan selesai pada season aktif
- memperbarui bagan knockout agar akun klub melihat full bracket pada kelompok usia yang relevan, dengan highlight klub miliknya
- menambahkan feature test untuk login akun nonaktif, visibilitas history, reaktivasi akun, dan toggle status admin

### Add season rollout docs and action menu polish

- menambahkan dokumen operasional season untuk admin dan production runbook
- menambahkan skrip audit pasca migrasi season
- merapikan action menu di beberapa halaman dashboard
- menambahkan test auth untuk perilaku remember me

### Normalize Blade inline PHP blocks

- merapikan template Blade dengan mengubah inline `@php(...)` menjadi blok `@php ... @endphp` yang lebih konsisten dan mudah dirawat

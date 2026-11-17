# Laporan perbaikan 403 panitia_berkas

## Penyebab

Akun asli: Role Utama `subscriber`, Role Spatie hanya `panitia_berkas`, organisasi PP.

1. Setelah login, Fortify mengarahkan non-admin ke `/dashboard/subscriber`.
2. `RoleMiddleware` memakai `hasSystemRole('subscriber')`, yang hanya melihat role Spatie bila sudah ada. Role Spatie-nya hanya `panitia_berkas`, jadi `subscriber` gagal → 403 **"Anda tidak punya akses."**
3. Middleware panel juga memblokir `/admin` (seharusnya diarahkan ke berkas) dan bisa memblokir `/livewire/update`.

Akun uji sebelumnya tidak punya Role Utama subscriber + organisasi PP, dan sering diuji langsung ke `/admin/berkas-finalis-toc` tanpa melewati alur login Fortify ke dashboard subscriber.

## Perbaikan

- `FortifyServiceProvider`: panitia_berkas langsung ke `/admin/berkas-finalis-toc`.
- `RestrictPanitiaBerkasPanel`: `/admin` → redirect berkas; izinkan Livewire dan `/admin/toc/berkas/*`; halaman admin lain tetap 403.

## Uji

`php artisan test --compact`: 409 lulus.

Termasuk konfigurasi persis akun asli (subscriber + PP + hanya panitia_berkas): berkas OK, `/admin` redirect, dashboard subscriber 403, login mengarah ke berkas.

## Produksi

Akun `onechai@gmail.com` tidak diubah. Cukup keluar-masuk setelah deploy.

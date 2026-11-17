# Laporan role panitia_berkas

Role `panitia_berkas` (guard `web`) hanya boleh memeriksa berkas finalis ToC di panel admin. Admin dan super admin tetap punya akses penuh.

## Menu yang terlihat

Hanya **Berkas Finalis ToC**.

URL lain di `/admin` (dashboard, Users, Roles, Orders, Payments, Pendaftar, Kegiatan, News, Dashboard ToC) ditolak 403. Unduh ZIP, impor Excel, ekspor CSV, dan terima massal tetap hanya untuk admin dan super admin, termasuk lewat URL langsung.

## Cara admin membuat akun panitia

1. Buka **Users** di panel admin.
2. Buat akun baru atau edit akun yang ada.
3. Isi **Role Utama (Legacy)** dengan Alumni (Subscriber). Jangan beri role admin atau super admin.
4. Super admin: pada **Role Sistem (Multi-Role)**, pilih `panitia_berkas`.
5. Admin (bukan super admin): centang **Panitia pemeriksa berkas**.
6. Simpan. Sampaikan kata sandi lewat saluran tertutup. Jangan menyimpan kata sandi di berkas atau log.

Akun ini masuk ke `/admin/berkas-finalis-toc`. Di tabel ada cari, filter **Bagian** (50 peserta per bagian, urut nomor pendaftaran, sama dengan ukuran bagian ZIP), dan filter **Status pemeriksaan**. **Kelola berkas** menampilkan foto, pakta, dan surat di halaman. Tolak wajib berisi alasan. Berkas yang sudah diterima tidak bisa diubah panitia; admin masih bisa mengubahnya.

## Uji

`php artisan test --compact`: 404 lulus, 2870 assertion.

Mencakup menu tunggal, halaman lain 403, ZIP/impor/terima massal/CSV ditolak, tolak tanpa alasan ditolak, email penolakan lewat `Mail::fake`, berkas diterima terkunci untuk panitia tetapi bisa diubah admin, audit (siapa, kapan, nomor peserta, keputusan, tanpa isi berkas), bagian 1–10 berisi 50 dan bagian 11 berisi 14 dari 514 finalis, serta seeder idempotent.

## Data uji

Laporan awal keliru: tidak ada baris ber-nomor `TOC-SMOKE-BROWSER-VERIFY`, tetapi nama smoke itu menimpa finalis asli `TOC-2026-0066`. Pemulihan ada di `laporan-pulihkan-0066.md`. Jumlah finalis tetap 514.

## Produksi

Deploy lewat `scripts/deploy-production.ps1`. Migration membuat role `panitia_berkas`. Aset Filament dan Vite menjawab 200.

Pemeriksaan memakai akun sementara dan satu peserta uji, lalu keduanya dihapus. Kata sandi tidak dicetak dan tidak disimpan. Email penolakan tidak terkirim ke peserta asli (`Mail::fake` pada proses uji).

Hasil: halaman Users 403, menu berkas 200, URL ekspor ZIP 403, tolak tanpa alasan 422, terima berhasil, tolak setelah diterima 403, audit mencatat nomor peserta tanpa isi berkas. Setelah dibersihkan, finalis tetap 514.

## Berkas yang diubah

- `app/Models/User.php`
- `app/Listeners/RedirectAfterLogin.php`
- `app/Http/Middleware/RestrictPanitiaBerkasPanel.php`
- `app/Filament/Pages/Dashboard.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Providers/AppServiceProvider.php`
- `app/Filament/Resources/TocBerkasFinalis/TocBerkasFinalisResource.php`
- `app/Filament/Resources/TocBerkasFinalis/Pages/ManageTocBerkasFinalis.php`
- `resources/views/filament/toc/berkas-detail.blade.php`
- `app/Http/Controllers/Admin/TocBerkasAdminController.php`
- `routes/web/authenticated/toc.php`
- `app/Services/TocBerkasVerificationService.php`
- `app/Services/TocBerkasKeputusanExportService.php`
- `app/Models/TocBerkasAuditLog.php`
- `app/Filament/Resources/Users/Schemas/UserForm.php`
- `app/Filament/Resources/Users/Pages/CreateUser.php`
- `app/Filament/Resources/Users/Pages/EditUser.php`
- `database/seeders/TocRolesSeeder.php`
- `database/migrations/2026_11_17_091647_seed_panitia_berkas_role.php`
- `config/toc.php`
- `tests/Feature/PanitiaBerkasRoleTest.php`

Push memakai akun GitHub yang sedang terautentikasi: `ikaunimed00-lab`. Akun `networkingsuryadigital-tech` tidak aktif di mesin ini.

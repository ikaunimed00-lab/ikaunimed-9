# Akun Admin & Panitia Teacher of Champions (ToC)

Panduan singkat untuk super-admin dalam membuat akun pengelola Teacher of Champions
di panel `/admin`. Tidak ada kata sandi di dokumen, kode, maupun seeder.

## Peran (role)

Role dibuat oleh `TocRolesSeeder` (idempoten, aman dijalankan ulang):

```bash
php artisan db:seed --class=TocRolesSeeder --force
```

| Hak akses | super_admin / admin | admin-toc | panitia-toc |
| --- | --- | --- | --- |
| Masuk `/admin` | ya | ya | ya |
| Lihat pendaftar ToC | ya | ya | ya (email & WhatsApp disamarkan) |
| Ubah status / catatan pendaftar | ya | ya | tidak |
| Ekspor pendaftar (Excel/CSV) | ya | ya | tidak |
| Hapus pendaftar | hanya super_admin | tidak | tidak |
| Dashboard ToC | ya | ya | ya |
| Edit kegiatan | semua | hanya kegiatan `teacher-of-champions` | tidak |
| Berita | semua | buat, ubah & terbitkan berita **milik sendiri**; otomatis bertag "Teacher of Champions" | tidak |
| Audit Log | ya | tidak | tidak |
| Users, Roles, Shop, Pengaturan, Legalisasi | sesuai izin lama | tidak (403) | tidak (403) |

Permission yang dipakai: `toc.pendaftar.view`, `toc.pendaftar.update`, `toc.pendaftar.export`,
`toc.kegiatan.update`, `toc.dashboard.view`, `toc.audit.view`, ditambah `cms.news.*` untuk admin-toc
(dibatasi ke berita milik sendiri oleh `NewsPolicy`).

## Membuat akun

1. Masuk `/admin` sebagai super-admin, buka **User Management → Users → New user**.
2. Isi nama dan email. Kata sandi awal diisi langsung di form (jangan dikirim lewat chat/email
   terbuka); minta pengguna menggantinya setelah login pertama.
3. **Role Utama (Legacy)**: pilih **Alumni (Subscriber)**.
4. **Role Sistem (Multi-Role)**: pilih hanya **admin-toc** atau **panitia-toc**
   (hapus role lain bila ikut terpilih).
5. Kosongkan **Organisasi / Daerah**.
6. Simpan. Pengguna kini login di `/login` dan diarahkan ke `/admin`, dengan menu grup **ToC**.

Untuk mencabut akses: buka user yang sama, hapus role ToC dari **Role Sistem**, lalu simpan.

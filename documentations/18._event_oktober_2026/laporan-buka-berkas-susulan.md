# Laporan: buka unggah berkas susulan ToC sampai 21 Nov 2026 10.00 WIB

## Keputusan

Unggah susulan dibuka sampai **Sabtu, 21 November 2026 pukul 10.00.00 WIB** hanya untuk peserta yang berkasnya ditolak atau belum lengkap. Peserta berkas lengkap tanpa penolakan tetap terkunci setelah 18 Nov 2026 23.59.59 WIB.

## Nilai `.env`

| Kunci | Sebelum | Sesudah |
| --- | --- | --- |
| `TOC_BATAS_UNGGAH` | `2026-11-18 23:59:59` | tidak diubah |
| `TOC_BATAS_UNGGAH_ULANG` | `2026-11-18 23:59:59` | `2026-11-21 10:00:00` |
| `TOC_PENGUMUMAN_DIBUKA` | `true` | tidak diubah |

Zona waktu: `config('toc.timezone')` = `Asia/Jakarta`.

Backup produksi: `.env.bak-susulan-20261120`.

## File yang diubah

- `config/toc.php` — default `batas_unggah_ulang` → `2026-11-21 10:00:00`
- `.env.example` — `TOC_BATAS_UNGGAH_ULANG="2026-11-21 10:00:00"`
- `app/Http/Controllers/TocPengumumanController.php` — label batas memakai jam dari config (bukan teks tetap `23.59`)
- `tests/Feature/TocBerkasVerifikasiMassalTest.php` — test jendela susulan 20 Nov / 21 Nov 09.59 / 21 Nov 10.00.01 + peserta lengkap tetap terkunci
- `documentations/18._event_oktober_2026/toc-verifikasi-massal-berkas.md` — dokumentasi tenggat susulan
- `documentations/18._event_oktober_2026/laporan-buka-berkas-susulan.md` — laporan ini

Tidak ada teks tetap "18 November" di `/toc/berkas` atau email penolakan: keduanya sudah memakai `effectiveBatas()`.

## Hasil test

`php artisan test --compact`: **410 lulus** (2946 assertion).

## Verifikasi produksi

Deploy: `scripts/deploy-production.ps1 -SkipComposer`.

Setelah `php artisan config:clear` + `config:cache` + `queue:restart` (baca saja, tanpa unggah/email):

```
timezone=Asia/Jakarta
batas_unggah=2026-11-18 23:59:59
batas_unggah_ulang=2026-11-21 10:00:00
pengumuman_dibuka=true
now=2026-11-20 19:45:06
TOC-2026-0066: jenis=0/3 ditolak=no incomplete=yes effectiveBatas=2026-11-21 10:00:00 tz=Asia/Jakarta
TOC-2026-0018: jenis=3/3 ditolak=no incomplete=no effectiveBatas=2026-11-18 23:59:59 tz=Asia/Jakarta
```

Tidak ada email/notifikasi ke peserta nyata. Data peserta tidak diubah.

## Commit

- `a9bb485befe5689bacf3da9b7621a4e35e2d738e` — feat(toc): open incomplete berkas reupload until 21 Nov 10.00 WIB
- Push: `ikaunimed00-lab` (akun terautentikasi di mesin ini; `networkingsuryadigital-tech` tidak aktif)

# Laporan: buka unggah berkas susulan ToC sampai 21 Nov 2026 10.00 WIB

## Keputusan

Unggah susulan dibuka sampai **Sabtu, 21 November 2026 pukul 10.00.00 WIB** hanya untuk peserta yang berkasnya ditolak atau belum lengkap. Peserta berkas lengkap tanpa penolakan tetap terkunci setelah 18 Nov 2026 23.59.59 WIB.

## Nilai `.env`

| Kunci | Sebelum | Sesudah |
| --- | --- | --- |
| `TOC_BATAS_UNGGAH` | `2026-11-18 23:59:59` | tidak diubah |
| `TOC_BATAS_UNGGAH_ULANG` | `2026-11-18 23:59:59` | `2026-11-21 10:00:00` |
| `TOC_PENGUMUMAN_DIBUKA` | (apa adanya) | tidak diubah |

Zona waktu: `config('toc.timezone')` = `Asia/Jakarta`.

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

(akan dilengkapi setelah deploy)

## Commit

(akan dilengkapi setelah commit)

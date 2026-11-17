# Laporan pemulihan TOC-2026-0066

## Sebelum

- Nomor `TOC-2026-0066` (finalis asli) tertimpa nama smoke `TOC-SMOKE-BROWSER-VERIFY` dan email `@example.test`.
- Penyebab: skrip uji produksi `prod_ensure_smoke.php` (riwayat chat sebelumnya) yang "reuse" baris lalu menimpa `nama`/`email`, kemudian uji browser mengunggah berkas ke nomor yang sama.
- Ada 3 berkas (foto/pakta/surat), status menunggu, unggah 16 Nov 2026 ~12:02 WIB.
- Bukti berkas uji: PDF berisi `%PDF-1.4` + `test`; foto bukan JPEG; ukuran/waktu sama; tanpa IP/UA.
- Sumber nama asli: ekspor status berkas 16 Nov 10:35 dan daftar resmi pendaftar/penerima (semua `MUHAMMAD IDRIS`). Audit `nama` tidak tercatat (observer hanya status/catatan).

## Sesudah

- Nama dipulihkan ke `MUHAMMAD IDRIS`.
- Email uji diganti kembali ke email asli dari sumber resmi (tidak dicetak di laporan).
- Ketiga berkas uji dan file fisiknya dihapus; status kelengkapan kembali belum lengkap.
- Finalis tetap **514**. Tidak ada email terkirim (`Mail::fake` saat restore).
- Tidak ada finalis lain yang tertimpa smoke/VERIFY/DUMMY/`@example.test`.

## Pencegahan

- Helper `App\Support\TocBerkasUjiPeserta` hanya membuat nomor `TOC-UJI-xxxx` dan menolak menyentuh finalis `TOC-2026-xxxx` yang lulus.
- Skrip aman: `scripts/prod_ensure_uji_peserta.php`.
- Test: `tests/Feature/TocBerkasUjiPesertaGuardTest.php` (gagal bila pola reuse smoke menimpa finalis).

## Akun GitHub

Push memakai `ikaunimed00-lab` (akun terautentikasi di mesin ini).

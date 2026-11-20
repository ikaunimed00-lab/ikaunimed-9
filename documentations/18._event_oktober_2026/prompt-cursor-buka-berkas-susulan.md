# Prompt Cursor: buka unggah berkas susulan ToC 2026 sampai 21 Nov 2026 pukul 10.00 WIB

Kerjakan langsung sampai selesai: commit, push, dan deploy ke produksi tanpa meminta izin lagi. Pakai akun GitHub networkingsuryadigital-tech (bila perlu otentikasi, ikuti cara yang sudah dipakai pada tugas sebelumnya).

## Latar belakang
Batas unggah berkas finalis ToC 2026 (18 Nov 2026 23.59.59 WIB) sudah lewat. Panitia memutuskan membuka unggah SUSULAN sampai **Sabtu, 21 November 2026 pukul 10.00.00 WIB**, hanya untuk peserta yang berkasnya belum lengkap atau ada yang ditolak.

Logika yang sudah ada (jangan diubah):
- `config/toc.php`: `batas_unggah` (env `TOC_BATAS_UNGGAH`) dan `batas_unggah_ulang` (env `TOC_BATAS_UNGGAH_ULANG`).
- `TocBerkasVerificationService::effectiveBatas()`: setelah `batas_unggah`, peserta yang punya berkas ditolak atau belum lengkap memakai `batas_unggah_ulang`; peserta lain tetap memakai `batas_unggah`.
- `TocBerkasController::unggah()` menolak unggahan bila `now()` melewati `effectiveBatas()`.

## Yang harus dilakukan
1. Di `.env` produksi, ubah HANYA:
   `TOC_BATAS_UNGGAH_ULANG="2026-11-21 10:00:00"`
   Biarkan `TOC_BATAS_UNGGAH="2026-11-18 23:59:59"` dan `TOC_PENGUMUMAN_DIBUKA` APA ADANYA (jangan diubah).
2. Pastikan `config('toc.timezone')` adalah Asia/Jakarta (WIB) sehingga 10.00 berarti 10.00 WIB.
3. Refresh cache konfigurasi di produksi (`php artisan config:clear` lalu `php artisan config:cache`, sesuaikan dengan cara deploy yang dipakai) dan restart queue worker bila ada.
4. Periksa semua tempat yang menampilkan batas waktu ke peserta (halaman /toc/berkas, email penolakan `TocBerkasDitolakMail`, teks bantuan, dll.) dan pastikan memakai `effectiveBatas()`, bukan teks tetap "18 November". Perbaiki bila ada teks tetap. Peserta susulan harus melihat batas 21 Nov 2026 pukul 10.00 WIB.
5. Bila `.env.example` / dokumentasi memuat nilai batas, perbarui `TOC_BATAS_UNGGAH_ULANG` dan catat di `documentations`.
6. Tambahkan/perbarui test: (a) peserta berkas belum lengkap bisa mengunggah pada 20 Nov 2026 dan 21 Nov 09.59; (b) peserta yang sama ditolak setelah 21 Nov 10.00.01; (c) peserta berkas lengkap dan tanpa berkas ditolak tetap tertutup setelah 18 Nov 23.59.59. Jalankan seluruh test suite.
7. Verifikasi di produksi TANPA mengunggah berkas apa pun dan TANPA mengirim email ke peserta: periksa nilai konfigurasi efektif dan, dengan akun uji internal atau tinker (hanya baca), hitung `effectiveBatas()` untuk satu peserta belum lengkap (mis. TOC-2026-0066) dan satu peserta lengkap.
8. Jangan mengirim email atau notifikasi apa pun ke peserta nyata. Jangan mengubah data peserta.

## Laporan
Tulis laporan singkat di `documentations/18._event_oktober_2026/laporan-buka-berkas-susulan.md`: nilai .env sebelum/sesudah, file yang diubah, hasil test, hasil verifikasi produksi, dan commit hash.

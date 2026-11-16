# Verifikasi Massal Berkas Finalis Teacher of Champions 2026

Fitur ini ada di Filament: **CMS > Berkas Finalis ToC** (hanya `admin` dan `super_admin`).

## Alur kerja

1. **Unduh ZIP berkas + Excel keputusan**: pilih baris (atau gunakan aksi header untuk hasil filter aktif) lalu jalankan aksi. Maksimal 600 peserta per unduhan. Proses berjalan di queue.
2. Tautan unduhan (ditandatangani, berlaku 1 jam, hanya dapat dibuka admin yang meminta) dikirim sebagai notifikasi dashboard dan juga muncul di tombol header **Unduhan saya**.
3. Isi kolom `*_keputusan` pada `keputusan-berkas.xlsx`: `Terima`, `Tolak`, atau kosong. `Tolak` wajib disertai `*_alasan`.
4. **Impor keputusan (Excel)**: unggah `.xlsx` (maks 5 MB, dibatasi 10 percobaan per menit), lihat pratinjau, lalu konfirmasi **Terapkan keputusan**.
5. **Terima semua berkas yang menunggu**: aksi massal dan aksi header (hasil filter). Modal konfirmasi menampilkan jumlah peserta dan berkas. Hanya berkas `menunggu` yang berubah menjadi `diterima`.

## ZIP

- Folder per peserta: `{nomor_pendaftaran} - {nama bersih}` (karakter terlarang `\ / : * ? " < > |` dibuang, maks 80 karakter). Contoh: `TOC-2026-0001 - Budi Santoso`.
- Isi folder: `foto.<ext>`, `pakta-integritas.<ext>`, `surat-keterangan.<ext>` (ekstensi asli). Semua berkas yang sudah diunggah disertakan apa pun statusnya. Folder tetap dibuat bila berkas belum ada.
- Berkas ditambahkan ke ZIP langsung dari path disk (tidak dimuat ke memori).
- File ZIP disimpan di `storage/app/private/toc/exports/{token}.zip`. File kedaluwarsa dihapus saat tautan dibuka dan oleh command terjadwal `toc:berkas-bersihkan-ekspor` (setiap jam).

## Excel `keputusan-berkas.xlsx`

Sheet **Keputusan** (baris pertama = header, jangan diubah):

`nomor_pendaftaran, nama, bidang_studi, foto_status_saat_ini, foto_keputusan, foto_alasan, pakta_status_saat_ini, pakta_keputusan, pakta_alasan, surat_status_saat_ini, surat_keputusan, surat_alasan, _foto_id, _foto_ver, _pakta_id, _pakta_ver, _surat_id, _surat_ver`

- `_*_id` = id berkas, `_*_ver` = `updated_at` berkas (ISO 8601); kosong bila berkas belum diunggah. Kolom meta tidak boleh diubah.
- Sheet **Petunjuk** menjelaskan aturan pengisian. Validasi nilai dilakukan di server saat impor (tidak ada dropdown di Excel).

## Impor

Pratinjau tidak mengubah data. Aturan per sel keputusan:

| Kondisi | Hasil |
| --- | --- |
| Keputusan kosong | dilewati |
| Bukan Terima/Tolak | kesalahan |
| Berkas belum diunggah (id kosong) | kesalahan |
| Id tidak dikenal / tidak cocok dengan nomor pendaftaran | kesalahan |
| Tolak tanpa alasan | kesalahan |
| Berkas sudah dalam keadaan yang diminta (Terima pada `diterima`, atau Tolak dengan alasan sama) | "sudah sesuai" (no-op) |
| Versi (`updated_at`) sudah berubah sejak ekspor | kesalahan, baris dilewati |
| Selain itu | diterapkan |

Penerapan berjalan dalam satu transaksi database; baris bermasalah dilewati, sisanya diterapkan. Impor ulang file yang sama tidak berefek ganda. Audit tercatat di `toc_berkas_audit_logs` (jenis `export_zip`, `import_keputusan`, `accept_waiting`: siapa, kapan, jumlah) tanpa isi berkas.

## Verifikator

Memakai kolom yang sudah ada: `toc_berkas.verified_by` dan `verified_at` (tampil sebagai **Diverifikasi oleh/pada** di tabel dan pop-up).

## Email penolakan

- Setiap penolakan (pop-up, impor Excel, atau cara lain lewat `TocBerkasVerificationService::reject`) mengantrekan **satu** `SendTocBerkasRejectionMailJob` per peserta yang merangkum semua berkas yang ditolak pada proses tersebut.
- Isi email: salam + nama, daftar berkas + alasan, cara unggah ulang (https://www.ikaunimed.or.id/toc/berkas), tenggat efektif peserta, format dan ukuran dari config, dua nomor WhatsApp panitia.
- Job dibatasi laju (`RateLimited` limiter `toc-rejection-mail`, default 30/menit, `toc.berkas.rejection_mail_per_minute`).
- Tidak dikirim bila email kosong (log berstatus `skipped`). Idempotensi: log `toc_berkas_rejection_mail_logs` unik per `berkas_id + verified_at`; hanya log berstatus `queued`/`failed` yang dikirim. Kolom **Email penolakan** di tabel menampilkan status log terakhir (tanpa isi email).

## Tenggat unggah

Konfigurasi `.env` (tanpa deploy kode):

- `TOC_BATAS_UNGGAH` (default `2026-11-18 23:59:59`), zona waktu `Asia/Jakarta`.
- `TOC_BATAS_UNGGAH_ULANG` (default sama dengan `TOC_BATAS_UNGGAH`).
- `TOC_PANITIA_WHATSAPP` (daftar dipisah koma; default `089679018457,081397718747`).

Tenggat efektif per peserta (`TocBerkasVerificationService::effectiveBatas`):

1. Selama sekarang <= `TOC_BATAS_UNGGAH`: pakai `TOC_BATAS_UNGGAH`.
2. Setelah itu, bila peserta punya berkas ditolak **atau** belum lengkap (kurang dari 3 jenis): pakai `TOC_BATAS_UNGGAH_ULANG`.
3. Selain itu: `TOC_BATAS_UNGGAH` (terkunci).

Formulir terkunci bila sekarang > tenggat efektif; hitung mundur memakai tenggat efektif. Berkas `diterima` tetap tidak bisa diganti.

## Konfigurasi lain (`config/toc.php`)

`berkas.export_max_peserta` (600), `berkas.export_ttl_minutes` (60), `berkas.import_max_kb` (5120), `berkas.export_path` (`toc/exports`), `panitia_whatsapp`.

## Catatan teknis

- Tabel `notifications` proyek ini adalah tabel kustom (bukan tabel notifikasi Laravel/Filament), sehingga tautan unduhan dikirim lewat `App\Models\Notification` (notifikasi dashboard) dan tombol header **Unduhan saya**, bukan `Notification::sendToDatabase` Filament.
- Worker queue harus berjalan (`QUEUE_CONNECTION=database` → `php artisan queue:work`) agar ZIP dan email diproses.

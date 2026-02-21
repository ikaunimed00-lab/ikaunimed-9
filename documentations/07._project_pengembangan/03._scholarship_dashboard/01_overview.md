# Dashboard Seleksi Beasiswa (Backend Admin)

## Overview
Fitur ini memungkinkan admin untuk mengelola pelamar beasiswa secara langsung melalui Admin Panel.

## Komponen
1. **Model & Database**:
   - `ScholarshipApplicant`: Menyimpan data pelamar (user_id, status, essay, cv_path, dll).
   - `scholarship_applicants` table: Relasi antara Scholarship dan User.

2. **Filament Resource**:
   - `ScholarshipResource`: Resource utama untuk manajemen beasiswa.
   - `ApplicantsRelationManager`: Tab "Applicants" di dalam detail Scholarship untuk melihat daftar pelamar.

## Fitur
- **List Pelamar**: Melihat siapa saja yang mendaftar pada beasiswa tertentu.
- **Detail Pelamar**: Melihat Essay, Download CV, dan Catatan Admin.
- **Update Status**: Mengubah status lamaran (Pending -> Review -> Interview -> Approved/Rejected).
- **Badge Status**: Visualisasi status dengan warna (Success, Danger, Warning).

## Status Beasiswa
- Status internal: `active`, `pending`, `rejected`, `closed`.
- Publik hanya melihat dan dapat mendaftar beasiswa `active` (setara dengan “open”).
- `closed` menandakan pendaftaran ditutup; CTA nonaktif di FE.
- `pending`/`rejected` dipakai admin untuk moderasi dan tidak tampil di publik.

## Cara Penggunaan
1. Login ke Admin Panel.
2. Buka menu **Scholarships**.
3. Edit salah satu Beasiswa.
4. Scroll ke bawah atau klik tab **Applicants**.
5. Klik **View** atau **Edit** pada pelamar untuk memproses seleksi.

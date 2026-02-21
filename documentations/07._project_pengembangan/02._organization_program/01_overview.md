# Dokumentasi Modul Organization Program (v5)

## Overview
Modul ini digunakan untuk mengelola program kerja organisasi (seperti Beasiswa, Donasi, Event, dll) secara dinamis. Setiap program memiliki slug unik dan tim pengelola sendiri.

## Struktur File
- **Model**: `app/Core/Models/OrganizationProgram.php`
- **Migration**: `database/migrations/xxxx_create_organization_programs_table.php`
- **Filament Resource**: `app/Filament/Resources/OrganizationProgramResource.php`
- **Relation Manager**: `app/Filament/Resources/OrganizationProgramResource/RelationManagers/MembersRelationManager.php`

## Fitur Utama
1.  **CRUD Program**: Membuat program baru dengan slug otomatis.
2.  **Manajemen Status**: Active, Paused, Archived.
3.  **Manajemen Visibility**: Public, Dashboard, Both.
4.  **Team Management**: Assign user sebagai Admin/Editor/Member per program.

## Masalah & Solusi (Development History)
Berikut adalah rangkuman error yang ditemui selama pengembangan modul ini dan solusinya:

### 1. Filament Version Compatibility (v3 vs v5)
- **Masalah**: Project menggunakan Filament v5, namun kode awal menggunakan namespace v3 (`Filament\Forms\Form`, `Tables\Actions\...`).
- **Error**: `Class "Filament\Tables\Actions\EditAction" not found`.
- **Solusi**: 
    - Menggunakan `Filament\Schemas\Schema` menggantikan `Filament\Forms\Form`.
    - Menggunakan `Filament\Actions\...` untuk Page Actions.
    - Menggunakan `Filament\Tables\Actions\...` untuk Relation Manager Actions.

### 2. Type Hinting Strictness
- **Masalah**: Error pada properti `$navigationIcon` dan `$navigationGroup`.
- **Solusi**: Menambahkan type hint `string|BackedEnum|null` sesuai kontrak parent class di v5.

### 3. Missing Relationship
- **Masalah**: Error `Call to undefined method App\Models\User::organizationPrograms()` saat attach member.
- **Solusi**: Menambahkan method `organizationPrograms()` di model `User.php` sebagai inverse relationship many-to-many.

## Next Steps
- Implementasi frontend untuk menampilkan program di halaman publik.
- Membuat dashboard khusus per program untuk admin program yang ditunjuk.

<?php

namespace App\Filament\Resources\Kegiatans\Schemas;

use App\Models\Kegiatan;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class KegiatanForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Kegiatan')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Textarea::make('summary')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Jadwal / Rundown')
                            ->rows(8)
                            ->helperText('Satu baris satu agenda, format: "06.00-07.00 | Registrasi peserta". Bagian sebelum tanda | adalah waktu.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Waktu (WIB)')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Mulai')
                            ->timezone(Kegiatan::TIMEZONE)
                            ->seconds(false)
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Selesai')
                            ->timezone(Kegiatan::TIMEZONE)
                            ->seconds(false)
                            ->required()
                            ->after('starts_at'),
                    ])->columns(2),

                Section::make('Tombol & Tampilan')
                    ->schema([
                        TextInput::make('cta_label')
                            ->label('Teks tombol')
                            ->placeholder('Daftar sekarang')
                            ->maxLength(255),
                        TextInput::make('cta_url')
                            ->label('Tautan tombol')
                            ->placeholder('/reuni-akbar-2026 atau https://...')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('featured')
                            ->label('Unggulan (tampil di hero)')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Select::make('theme')
                            ->label('Tema warna')
                            ->options(Kegiatan::THEMES)
                            ->default(Kegiatan::THEME_HIJAU)
                            ->required()
                            ->native(false),
                    ])->columns(3),

                Section::make('Gambar')
                    ->schema([
                        static::imageUpload('image', 'Gambar kartu', 2048),
                        static::imageUpload('image_sm', 'Gambar kartu kecil (HP)', 1024),
                        static::imageUpload('hero_image', 'Gambar hero / poster', 4096),
                        static::imageUpload('hero_image_mobile', 'Gambar hero versi HP', 2048),
                        static::imageUpload('route_map_image', 'Gambar pendukung (denah rute / timeline)', 4096),
                    ])->columns(2),
            ]);
    }

    /**
     * Seeded images live in public/images (e.g. "/images/foo.jpg"), outside the public disk,
     * so file-existence checks are skipped to keep those paths when the record is edited.
     */
    protected static function imageUpload(string $name, string $label, int $maxSizeKb): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->disk('public')
            ->directory('kegiatan')
            ->maxSize($maxSizeKb)
            ->fetchFileInformation(false)
            ->getUploadedFileUsing(fn (string $file): array => [
                'name' => basename($file),
                'size' => 0,
                'type' => null,
                'url' => Kegiatan::buildImageUrl($file),
            ]);
    }
}

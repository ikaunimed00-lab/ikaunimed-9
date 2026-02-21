<?php

namespace App\Filament\Resources\Legalizations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalizationForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Pemohon')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Nama Alumni')
                            ->searchable()
                            ->required()
                            ->default(auth()->id()),
                        TextInput::make('jenjang')
                            ->label('Jenjang Pendidikan')
                            ->placeholder('Contoh: S1 Pendidikan Ekonomi')
                            ->required(),
                        TextInput::make('tahun_lulus')
                            ->label('Tahun Lulus')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Section::make('Detail Permohonan')
                    ->schema([
                        TextInput::make('jumlah_lembar')
                            ->label('Jumlah Lembar')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(10),
                        Textarea::make('tujuan')
                            ->label('Tujuan Legalistasi')
                            ->placeholder('Contoh: Melamar Pekerjaan / Melanjutkan Studi')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Dokumen Pendukung')
                    ->schema([
                        FileUpload::make('file_ijazah')
                            ->label('Scan Ijazah Asli')
                            ->directory('legalizations/ijazah')
                            ->required(),
                        FileUpload::make('file_transkrip')
                            ->label('Scan Transkrip Nilai Asli')
                            ->directory('legalizations/transkrip')
                            ->required(),
                    ])->columns(2),

                Section::make('Status & Moderasi')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'submitted' => 'Diajukan',
                                'verified' => 'Diverifikasi',
                                'processing' => 'Diproses',
                                'completed' => 'Selesai',
                                'rejected' => 'Ditolak',
                            ])
                            ->required()
                            ->live(),
                        Textarea::make('keterangan')
                            ->label('Keterangan / Alasan Penolakan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

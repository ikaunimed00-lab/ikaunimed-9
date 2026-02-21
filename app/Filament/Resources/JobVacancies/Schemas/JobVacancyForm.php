<?php

namespace App\Filament\Resources\JobVacancies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class JobVacancyForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Pekerjaan')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Lowongan')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('job_vacancies', 'slug', ignoreRecord: true),
                        TextInput::make('company')
                            ->label('Perusahaan/Instansi')
                            ->required(),
                        FileUpload::make('logo')
                            ->image()
                            ->directory('jobs/logos'),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->placeholder('Contoh: Medan, Jakarta (Remote)')
                            ->required(),
                        Select::make('type')
                            ->label('Tipe Pekerjaan')
                            ->options([
                                'full-time' => 'Full-time',
                                'part-time' => 'Part-time',
                                'internship' => 'Internship',
                                'contract' => 'Contract',
                            ])
                            ->required(),
                        DatePicker::make('deadline')
                            ->label('Batas Pendaftaran'),
                        TextInput::make('link')
                            ->url()
                            ->label('Link Pendaftaran / Email'),
                    ])->columns(2),

                Section::make('Deskripsi & Persyaratan')
                    ->schema([
                        RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Scholarships\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScholarshipForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Beasiswa')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('provider')
                            ->label('Penyelenggara')
                            ->required(),
                        Select::make('degree')
                            ->label('Jenjang')
                            ->options([
                                's1' => 'S1',
                                's2' => 'S2',
                                's3' => 'S3',
                                'all' => 'Semua Jenjang',
                            ])
                            ->required(),
                        Select::make('coverage_type')
                            ->label('Cakupan')
                            ->options([
                                'full' => 'Full Scholarship',
                                'partial' => 'Partial Scholarship',
                            ])
                            ->required(),
                        DatePicker::make('deadline')
                            ->required(),
                        TextInput::make('link')
                            ->url()
                            ->label('Link Pendaftaran'),
                        Select::make('status')
                            ->label('Status Publikasi')
                            ->options([
                                'active' => 'Active',
                                'pending' => 'Pending',
                                'closed' => 'Closed',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending')
                            ->disabled(fn () => ! auth()->user()?->can('cms.scholarship.publish')),
                    ])->columns(2),

                Section::make('Konten & Media')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi/Persyaratan')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->image()
                            ->directory('scholarships'),
                    ]),
            ]);
    }
}

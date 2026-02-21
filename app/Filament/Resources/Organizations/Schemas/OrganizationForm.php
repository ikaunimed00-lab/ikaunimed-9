<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrganizationForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Identitas & Hirarki')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('type')
                            ->options([
                                'pp' => 'Pengurus Pusat (PP)',
                                'dpw' => 'Dewan Pengurus Wilayah (DPW)',
                                'dpc' => 'Dewan Pengurus Cabang (DPC)',
                            ])
                            ->required()
                            ->live(),
                        Select::make('parent_id')
                            ->label('Induk Organisasi')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(false)
                            ->visible(fn (callable $get) => in_array($get('type'), ['dpw', 'dpc'])),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),

                Section::make('Profil & Kontak')
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->directory('organization-logos')
                            ->imageEditor()
                            ->avatar()
                            ->columnSpan(1),
                        TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel(),
                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}

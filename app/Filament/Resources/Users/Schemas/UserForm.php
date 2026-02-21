<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Data login dan identitas dasar user.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Select::make('role')
                            ->label('Role Akses')
                            ->options([
                                'admin' => 'Admin',
                                'editor' => 'Editor',
                                'writer' => 'Writer',
                                'subscriber' => 'Alumni (Subscriber)',
                            ])
                            ->required()
                            ->default('subscriber'),
                        Select::make('roles')
                            ->label('Role Spatie')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->visible(fn () => auth()->user()?->can('master.roles.manage') ?? false),
                        Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->label('Organisasi / Daerah')
                            ->searchable()
                            ->preload(false)
                            ->visible(fn () => auth()->user()?->isCentralAdmin()),
                    ])->columns(2),

                Section::make('Profil Alumni')
                    ->description('Data profil alumni (opsional).')
                    ->schema([
                        TextInput::make('wa')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('nik')
                            ->label('NIK')
                            ->numeric()
                            ->length(16),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tempat_lahir')
                                    ->label('Tempat Lahir'),
                                DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir'),
                            ]),
                        Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Riwayat Pendidikan')
                    ->description('Data pendidikan S1 di Unimed.')
                    ->schema([
                        TextInput::make('s1_fakultas')
                            ->label('Fakultas'),
                        TextInput::make('s1_prodi')
                            ->label('Program Studi'),
                        TextInput::make('s1_stambuk')
                            ->label('Stambuk/Angkatan')
                            ->numeric()
                            ->length(4),
                    ])->columns(3),
            ]);
    }
}

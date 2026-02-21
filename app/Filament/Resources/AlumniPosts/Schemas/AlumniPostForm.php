<?php

namespace App\Filament\Resources\AlumniPosts\Schemas;

use App\Models\AlumniPost;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlumniPostForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Konten Post')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->options(AlumniPost::CATEGORIES)
                            ->required()
                            ->searchable(),
                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->image()
                            ->directory('alumni-posts'),
                    ])->columns(2),

                Section::make('Status & Moderasi')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending Review',
                                'published' => 'Published',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->live(),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->visible(fn (Get $get) => $get('status') === 'published')
                            ->required(fn (Get $get) => $get('status') === 'published'),
                        Textarea::make('rejection_note')
                            ->label('Alasan Penolakan')
                            ->visible(fn (Get $get) => $get('status') === 'rejected')
                            ->required(fn (Get $get) => $get('status') === 'rejected')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}

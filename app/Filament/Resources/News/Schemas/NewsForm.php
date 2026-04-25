<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Utama')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                        Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload(false)
                            ->hidden(fn () => !auth()->user()->isCentralAdmin() && !auth()->user()->isPpAdmin())
                            ->default(fn () => auth()->user()->organization_id),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'Review',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft'),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->visible(fn (Get $get) => $get('status') === 'published'),
                    ])->columns(2),

                Section::make('Konten')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->required(),
                        RichEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Gambar Utama')
                            ->image()
                            ->disk('public')
                            ->directory('news')
                            ->required(),
                        TagsInput::make('video_urls')
                            ->label('Daftar Video (YouTube/TikTok)')
                            ->placeholder('Tempel URL video lalu tekan Enter')
                            ->helperText('Satu tag = satu URL video. Mendukung YouTube (termasuk /shorts) dan TikTok.')
                            ->separator(','),
                    ]),
            ]);
    }
}

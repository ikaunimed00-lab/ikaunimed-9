<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Setting Detail')
                    ->schema([
                        TextInput::make('key')
                            ->disabled()
                            ->required(),
                        TextInput::make('label')
                            ->required(),
                        Select::make('group')
                            ->options([
                                'identity' => 'Identity',
                                'contact' => 'Contact',
                                'social' => 'Social Media',
                                'footer' => 'Footer',
                                'seo' => 'SEO',
                            ])
                            ->disabled()
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Value')
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => in_array($get('type'), ['text'])),
                        
                        Textarea::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => in_array($get('type'), ['textarea'])),
                        
                        FileUpload::make('value')
                            ->label('Image')
                            ->image()
                            ->directory('settings')
                            ->visible(fn ($get) => $get('type') === 'image'),
                    ]),
            ]);
    }
}

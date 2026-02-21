<?php

namespace App\Filament\Resources\JobVacancies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JobVacancyInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('company'),
                TextEntry::make('logo')
                    ->placeholder('-'),
                TextEntry::make('location'),
                TextEntry::make('type'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('salary_range')
                    ->placeholder('-'),
                TextEntry::make('apply_link')
                    ->placeholder('-'),
                TextEntry::make('closing_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('requirements')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('apply_email')
                    ->placeholder('-'),
            ]);
    }
}

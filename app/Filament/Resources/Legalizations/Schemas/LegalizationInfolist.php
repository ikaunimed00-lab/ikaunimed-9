<?php

namespace App\Filament\Resources\Legalizations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LegalizationInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('jenjang'),
                TextEntry::make('tahun_lulus')
                    ->numeric(),
                TextEntry::make('jumlah_lembar')
                    ->numeric(),
                TextEntry::make('tujuan')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('admin_note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('submitted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

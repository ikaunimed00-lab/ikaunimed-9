<?php

namespace App\Filament\Resources\Kegiatans\Tables;

use App\Models\Kegiatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class KegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('starts_at')
                    ->label('Mulai (WIB)')
                    ->dateTime('d M Y, H:i')
                    ->timezone(Kegiatan::TIMEZONE)
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Selesai (WIB)')
                    ->dateTime('d M Y, H:i')
                    ->timezone(Kegiatan::TIMEZONE)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(40)
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->disabled(fn (Kegiatan $record): bool => ! (Auth::user()?->can('update', $record) ?? false)),
                IconColumn::make('featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

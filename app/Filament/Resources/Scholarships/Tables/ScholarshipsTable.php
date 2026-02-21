<?php

namespace App\Filament\Resources\Scholarships\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Filters\SelectFilter;

class ScholarshipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('provider')
                    ->label('Penyelenggara')
                    ->searchable(),
                TextColumn::make('degree')
                    ->label('Jenjang')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('coverage_type')
                    ->label('Cakupan')
                    ->badge()
                    ->color('info'),
                TextColumn::make('deadline')
                    ->label('Batas Akhir')
                    ->date()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pengirim')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'published' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending Review',
                        'published' => 'Published',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('degree')
                    ->label('Jenjang')
                    ->options([
                        's1' => 'S1',
                        's2' => 'S2',
                        's3' => 'S3',
                        'all' => 'Semua Jenjang',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

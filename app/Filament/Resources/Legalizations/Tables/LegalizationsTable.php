<?php

namespace App\Filament\Resources\Legalizations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Filters\SelectFilter;

class LegalizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Alumni')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->searchable(),
                TextColumn::make('tahun_lulus')
                    ->label('Tahun Lulus')
                    ->sortable(),
                TextColumn::make('jumlah_lembar')
                    ->label('Lembar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'verified' => 'info',
                        'processing' => 'primary',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Pending',
                        'verified' => 'Verified',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        default => $state,
                    }),
                TextColumn::make('submitted_at')
                    ->label('Tgl Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                        'processing' => 'Sedang Dicetak',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

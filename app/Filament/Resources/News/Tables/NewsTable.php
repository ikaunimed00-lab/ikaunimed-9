<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('categories.name')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),
                // Badge status sinkron dengan kontrak Model::scopePublished (Item 2).
                // Hanya 2 nilai literal di kolom: draft & published. "Terjadwal" adalah
                // turunan (status=published + published_at>now()) → ditandai khusus
                // agar admin tahu berita belum tayang meskipun status=published.
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (string $state, $record): string {
                        if ($state === 'published' && $record->published_at && $record->published_at->isFuture()) {
                            return 'Terjadwal';
                        }

                        return ucfirst($state);
                    })
                    ->color(function (string $state, $record): string {
                        if ($state === 'published' && $record->published_at && $record->published_at->isFuture()) {
                            return 'warning';
                        }

                        return match ($state) {
                            'draft' => 'gray',
                            'published' => 'success',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter status logical (3 opsi: draft / terjadwal / published).
                // "terjadwal" adalah filter virtual (status=published + published_at>now()).
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Terjadwal',
                        'published' => 'Published',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'draft') {
                            return $query->where('status', 'draft');
                        }
                        if ($value === 'scheduled') {
                            return $query
                                ->where('status', 'published')
                                ->where('published_at', '>', now());
                        }
                        if ($value === 'published') {
                            return $query
                                ->where('status', 'published')
                                ->whereNotNull('published_at')
                                ->where('published_at', '<=', now());
                        }

                        return $query;
                    }),
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('organization')
                    ->relationship('organization', 'name')
                    ->hidden(fn () => ! auth()->user()->isCentralAdmin() && ! auth()->user()->isPpAdmin())
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

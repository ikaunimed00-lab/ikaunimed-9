<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Resources\Shop\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can('shop.order.view') || $user->can('shop.order.manage');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can('shop.order.manage');
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Status Order')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'awaiting_payment' => 'Menunggu Pembayaran',
                                'paid' => 'Sudah Dibayar',
                                'fulfilled' => 'Terpenuhi',
                                'canceled' => 'Dibatalkan',
                            ])
                            ->required(),
                        Select::make('fulfillment_status')
                            ->label('Status Pemenuhan')
                            ->options([
                                'unfulfilled' => 'Belum Diproses',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'completed' => 'Selesai',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organisasi')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'awaiting_payment' => 'warning',
                        'paid' => 'success',
                        'fulfilled' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('fulfillment_status')
                    ->label('Pemenuhan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unfulfilled' => 'gray',
                        'processing' => 'warning',
                        'shipped' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'awaiting_payment' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'fulfilled' => 'Terpenuhi',
                        'canceled' => 'Dibatalkan',
                    ]),
                SelectFilter::make('fulfillment_status')
                    ->label('Status Pemenuhan')
                    ->options([
                        'unfulfilled' => 'Belum Diproses',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                    ]),
                SelectFilter::make('organization_id')
                    ->label('Organisasi')
                    ->relationship('organization', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'organization', 'shipment', 'couponUsage.coupon'])
            ->withCount('items');
    }

    public static function infolist(\Filament\Schemas\Schema $infolist): \Filament\Schemas\Schema
    {
        return $infolist
            ->schema([
                Section::make('Ringkasan Order')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID Order'),
                        TextEntry::make('user.name')
                            ->label('Pengguna'),
                        TextEntry::make('organization.name')
                            ->label('Organisasi')
                            ->placeholder('-'),
                        TextEntry::make('couponUsage.coupon.code')
                            ->label('Kode Kupon')
                            ->placeholder('-'),
                        TextEntry::make('couponUsage.coupon.type')
                            ->label('Tipe Kupon')
                            ->placeholder('-'),
                        TextEntry::make('couponUsage.coupon.value')
                            ->label('Nilai Kupon')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'awaiting_payment' => 'warning',
                                'paid' => 'success',
                                'fulfilled' => 'success',
                                'canceled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('fulfillment_status')
                            ->label('Status Pemenuhan')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'unfulfilled' => 'gray',
                                'processing' => 'warning',
                                'shipped' => 'info',
                                'completed' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('items_count')
                            ->label('Jumlah Item'),
                        TextEntry::make('grand_total')
                            ->label('Total')
                            ->money('idr'),
                    ])
                    ->columns(2),
                Section::make('Alamat Pengiriman')
                    ->schema([
                        TextEntry::make('shipping_address.name')
                            ->label('Nama Penerima')
                            ->placeholder('-'),
                        TextEntry::make('shipping_address.phone')
                            ->label('No. HP/WA')
                            ->placeholder('-'),
                        TextEntry::make('shipping_address.address')
                            ->label('Alamat')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
                Section::make('Catatan')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

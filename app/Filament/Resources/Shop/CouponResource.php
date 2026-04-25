<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\CouponResource\Pages;
use App\Models\Coupon;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

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

        return $user->can('shop.coupon.manage');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can('shop.coupon.manage');
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

        return $user->can('shop.coupon.manage');
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can('shop.coupon.manage');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Kupon')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Kupon')
                            ->required()
                            ->maxLength(50)
                            ->unique('coupons', 'code', ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('code', strtoupper((string) $state));
                            }),
                        Select::make('type')
                            ->label('Tipe Diskon')
                            ->options([
                                'fixed' => 'Fixed Amount',
                                'percentage' => 'Persentase',
                            ])
                            ->required()
                            ->default('fixed'),
                        TextInput::make('value')
                            ->label('Nilai')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(fn ($get) => $get('type') === 'percentage' ? 1 : null)
                            ->helperText('Untuk persentase, gunakan angka 0-1 (misal 0.2 untuk 20%).'),
                        TextInput::make('max_discount')
                            ->label('Maksimum Diskon (opsional)')
                            ->numeric()
                            ->helperText('Hanya berlaku untuk kupon persentase.'),
                        TextInput::make('usage_limit')
                            ->label('Batas Penggunaan (opsional)')
                            ->numeric()
                            ->minValue(1),
                        Select::make('eligible_product_ids')
                            ->label('Produk Khusus (Opsional)')
                            ->multiple()
                            ->searchable()
                            ->options(\App\Models\Product::pluck('name', 'id'))
                            ->helperText('Pilih produk tertentu jika kupon hanya berlaku untuk produk tersebut. Kosongkan untuk semua produk.'),
                        Select::make('eligible_categories')
                            ->label('Kategori Khusus (Opsional)')
                            ->multiple()
                            ->searchable()
                            ->options(\App\Models\ProductCategory::pluck('name', 'id'))
                            ->helperText('Pilih kategori tertentu jika kupon hanya berlaku untuk kategori tersebut. Kosongkan untuk semua kategori.'),
                        DateTimePicker::make('starts_at')
                            ->label('Mulai Berlaku')
                            ->seconds(false),
                        DateTimePicker::make('expires_at')
                            ->label('Berakhir')
                            ->seconds(false),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Non Aktif',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(function ($state, Coupon $record): string {
                        if ($record->type === 'percentage') {
                            $percentage = (float) $state * 100;

                            return number_format($percentage, 0).' %';
                        }

                        return 'Rp '.number_format((float) $state, 0, ',', '.');
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_summary')
                    ->label('Penggunaan')
                    ->getStateUsing(function (Coupon $record): string {
                        $used = $record->used_count ?? 0;

                        if ($record->usage_limit === null) {
                            return $used.'x';
                        }

                        return $used.' / '.$record->usage_limit.'x';
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Berakhir')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Non Aktif',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}


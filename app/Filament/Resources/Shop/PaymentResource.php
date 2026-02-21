<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\PaymentResource\Pages;
use App\Models\Payment;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('shop.payment.view') || $user->can('shop.order.manage');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Metode')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Payment::STATUS_PENDING => 'warning',
                        Payment::STATUS_PAID => 'success',
                        Payment::STATUS_EXPIRED => 'gray',
                        Payment::STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('idr')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->money('idr')
                    ),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar Pada')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Payment::STATUS_PENDING => 'Pending',
                        Payment::STATUS_PAID => 'Paid',
                        Payment::STATUS_EXPIRED => 'Expired',
                        Payment::STATUS_FAILED => 'Failed',
                    ]),
                SelectFilter::make('provider')
                    ->label('Provider')
                    ->options([
                        'tripay' => 'Tripay',
                    ]),
                Filter::make('needs_investigation')
                    ->label('Perlu Investigasi (pending tapi ada log)')
                    ->query(function (Builder $query) {
                        return $query
                            ->where('status', Payment::STATUS_PENDING)
                            ->whereHas('logs');
                    }),
                Filter::make('created_at_range')
                    ->label('Tanggal Dibuat')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
                Filter::make('paid_at_range')
                    ->label('Tanggal Dibayar')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('paid_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('paid_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['order', 'user', 'logs']);
    }

    public static function infolist(\Filament\Schemas\Schema $infolist): \Filament\Schemas\Schema
    {
        return $infolist
            ->schema([
                Section::make('Ringkasan Payment')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('order.id')
                            ->label('Order ID'),
                        TextEntry::make('user.name')
                            ->label('Pengguna'),
                        TextEntry::make('provider')
                            ->label('Provider'),
                        TextEntry::make('provider_reference')
                            ->label('Provider Reference')
                            ->placeholder('-'),
                        TextEntry::make('method')
                            ->label('Metode'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Payment::STATUS_PENDING => 'warning',
                                Payment::STATUS_PAID => 'success',
                                Payment::STATUS_EXPIRED => 'gray',
                                Payment::STATUS_FAILED => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('amount')
                            ->label('Jumlah')
                            ->money('idr'),
                        TextEntry::make('paid_at')
                            ->label('Dibayar Pada')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Section::make('Payment Logs')
                    ->schema([
                        RepeatableEntry::make('logs')
                            ->label('Logs')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime(),
                                TextEntry::make('event')
                                    ->label('Event'),
                                TextEntry::make('status_code')
                                    ->label('Status Code'),
                                TextEntry::make('payload')
                                    ->label('Payload')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->logs()->exists()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}

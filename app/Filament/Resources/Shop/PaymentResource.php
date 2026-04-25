<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\PaymentResource\Pages;
use App\Models\Course;
use App\Models\Enrollment;
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
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        if ($user->hasRole('super_admin')) {
            return true;
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
                Tables\Columns\TextColumn::make('order_summary')
                    ->label('Produk')
                    ->getStateUsing(function (Payment $record): string {
                        $items = $record->order?->items;

                        if (! $items || $items->isEmpty()) {
                            return '-';
                        }

                        $first = $items->first();
                        $count = $items->count();

                        if ($count === 1) {
                            return $first->product_name;
                        }

                        return $first->product_name.' (+'.($count - 1).' lainnya)';
                    })
                    ->wrap()
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
                Tables\Columns\TextColumn::make('order.couponUsage.coupon.code')
                    ->label('Kupon')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('manual_proof_path')
                    ->label('Bukti Manual')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Ada' : 'Belum')
                    ->url(fn (Payment $record): ?string => $record->manual_proof_path
                        ? Storage::disk('public')->url($record->manual_proof_path)
                        : null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('has_coupon_for_stats')
                    ->label('Filter Kupon (Statistik LMS)')
                    ->options([
                        'with' => 'Hanya payment dengan kupon',
                        'without' => 'Hanya payment tanpa kupon',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'with') {
                            return $query->whereHas('order.couponUsage');
                        }

                        if ($value === 'without') {
                            return $query->whereDoesntHave('order.couponUsage');
                        }

                        return $query;
                    }),
                Filter::make('needs_investigation')
                    ->label('Perlu Investigasi (pending tapi ada log)')
                    ->query(function (Builder $query) {
                        return $query
                            ->where('status', Payment::STATUS_PENDING)
                            ->whereHas('logs');
                    }),
                Filter::make('webhook_errors')
                    ->label('Error Webhook (status code ≥ 400)')
                    ->query(function (Builder $query) {
                        return $query->whereHas('logs', function (Builder $sub) {
                            $sub->where('status_code', '>=', 400);
                        });
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
                Filter::make('has_coupon')
                    ->label('Dengan Kupon')
                    ->query(function (Builder $query) {
                        return $query->whereHas('order.couponUsage');
                    }),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('confirmPayment')
                    ->label('Konfirmasi Pembayaran')
                    ->requiresConfirmation()
                    ->visible(function (Payment $record): bool {
                        $user = auth()->user();

                        if (! $user) {
                            return false;
                        }

                        if ($user->hasRole('super_admin')) {
                            return true;
                        }

                        if (! $user->can('shop.order.manage')) {
                            return false;
                        }

                        return $record->status === Payment::STATUS_PENDING
                            && (bool) $record->manual_proof_path;
                    })
                    ->action(function (Payment $record): void {
                        DB::transaction(function () use ($record) {
                            $record->update([
                                'status' => Payment::STATUS_PAID,
                                'paid_at' => now(),
                            ]);

                            $order = $record->order?->loadMissing(['items.product']);

                            if ($order) {
                                $order->update([
                                    'status' => 'paid',
                                    'fulfillment_status' => $order->fulfillment_status ?: 'processing',
                                ]);

                                foreach ($order->items as $item) {
                                    if (($item->product_type ?? null) !== 'digital') {
                                        continue;
                                    }

                                    $product = $item->product;

                                    if (! $product) {
                                        $record->logs()->create([
                                            'event' => 'manual_payment_digital_product_missing',
                                            'payload' => json_encode([
                                                'payment_id' => $record->id,
                                                'order_id' => $order->id,
                                                'order_item_id' => $item->id,
                                                'product_id' => $item->product_id,
                                            ]),
                                            'headers' => [],
                                            'status_code' => 200,
                                        ]);

                                        continue;
                                    }

                                    if (! $product->course_id) {
                                        $record->logs()->create([
                                            'event' => 'manual_payment_digital_product_without_course_id',
                                            'payload' => json_encode([
                                                'payment_id' => $record->id,
                                                'order_id' => $order->id,
                                                'order_item_id' => $item->id,
                                                'product_id' => $item->product_id,
                                            ]),
                                            'headers' => [],
                                            'status_code' => 200,
                                        ]);

                                        continue;
                                    }

                                    $course = Course::query()
                                        ->where('id', $product->course_id)
                                        ->where('status', 'published')
                                        ->first();

                                    if (! $course) {
                                        $record->logs()->create([
                                            'event' => 'manual_payment_digital_course_not_found_by_id',
                                            'payload' => json_encode([
                                                'payment_id' => $record->id,
                                                'order_id' => $order->id,
                                                'order_item_id' => $item->id,
                                                'product_id' => $item->product_id,
                                                'course_id' => $product->course_id,
                                            ]),
                                            'headers' => [],
                                            'status_code' => 200,
                                        ]);

                                        continue;
                                    }

                                    $enrollment = Enrollment::firstOrCreate(
                                        [
                                            'user_id' => $order->user_id,
                                            'course_id' => $course->id,
                                        ],
                                        [
                                            'status' => 'active',
                                            'started_at' => now(),
                                        ]
                                    );

                                    if (! $enrollment->started_at) {
                                        $enrollment->started_at = now();
                                        $enrollment->status = 'active';
                                        $enrollment->save();
                                    }
                                }
                            }

                            $record->logs()->create([
                                'event' => 'manual_payment_confirmed',
                                'payload' => json_encode([
                                    'payment_id' => $record->id,
                                    'order_id' => $order?->id,
                                ]),
                                'headers' => [],
                                'status_code' => 200,
                            ]);
                        });
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['order.items', 'order.couponUsage.coupon', 'user', 'logs']);
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
                        TextEntry::make('order.couponUsage.coupon.code')
                            ->label('Kode Kupon')
                            ->placeholder('-'),
                        TextEntry::make('order.couponUsage.coupon.type')
                            ->label('Tipe Kupon')
                            ->placeholder('-'),
                        TextEntry::make('order.couponUsage.coupon.value')
                            ->label('Nilai Kupon')
                            ->placeholder('-'),
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
                        TextEntry::make('manual_proof_path')
                            ->label('Bukti Manual')
                            ->formatStateUsing(fn (?string $state): string => $state ? 'Sudah diupload' : 'Belum ada')
                            ->url(fn (Payment $record): ?string => $record->manual_proof_path
                                ? Storage::disk('public')->url($record->manual_proof_path)
                                : null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
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

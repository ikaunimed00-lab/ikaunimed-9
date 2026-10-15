<?php

namespace App\Filament\Resources\PendaftarTeacherOfChampions;

use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ManagePendaftarTeacherOfChampions;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\AuditLogger;
use App\Services\PendaftarDuplicateDetector;
use App\Services\PendaftarTeacherOfChampionsExporter;
use App\Services\WilayahService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class PendaftarTeacherOfChampionsResource extends Resource
{
    protected static ?string $model = PendaftarTeacherOfChampions::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static \UnitEnum|string|null $navigationGroup = 'ToC';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pendaftar';

    protected static ?string $modelLabel = 'Pendaftar Teacher of Champions';

    protected static ?string $pluralModelLabel = 'Pendaftar Teacher of Champions';

    protected static ?string $slug = 'pendaftar-teacher-of-champions';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('status')
                ->label('Status')
                ->options(PendaftarTeacherOfChampions::STATUSES)
                ->required()
                ->native(false),
            Textarea::make('catatan')
                ->label('Catatan panitia')
                ->rows(4),
            Textarea::make('catatan_internal')
                ->label('Catatan internal')
                ->helperText('Hanya terlihat oleh pengelola di panel admin.')
                ->rows(4),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        $provinsiOptions = collect(app(WilayahService::class)->provinsi())
            ->mapWithKeys(fn (string $provinsi): array => [$provinsi => $provinsi])
            ->all();
        $canViewContact = self::userCan('viewContact');
        $duplicates = app(PendaftarDuplicateDetector::class)->duplicates();

        return $table
            ->defaultSort('created_at', 'desc')
            ->description(fn (): string => self::summary())
            ->columns([
                TextColumn::make('nomor_pendaftaran')
                    ->label('No. Daftar')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kemungkinan_duplikat')
                    ->label('Duplikat?')
                    ->state(fn (PendaftarTeacherOfChampions $record): ?string => isset($duplicates[$record->id]) ? 'Kemungkinan duplikat' : null)
                    ->tooltip(fn (PendaftarTeacherOfChampions $record): ?string => isset($duplicates[$record->id]) ? implode('; ', $duplicates[$record->id]) : null)
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable($canViewContact)
                    ->formatStateUsing(fn (?string $state): ?string => $canViewContact ? $state : PendaftarTeacherOfChampions::maskContact($state))
                    ->url($canViewContact ? fn (PendaftarTeacherOfChampions $record): string => 'https://wa.me/'.$record->whatsapp : null, shouldOpenInNewTab: true),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable($canViewContact)
                    ->formatStateUsing(fn (?string $state): ?string => $canViewContact ? $state : PendaftarTeacherOfChampions::maskContact($state))
                    ->toggleable(),
                TextColumn::make('provinsi')
                    ->label('Provinsi')
                    ->description(fn (PendaftarTeacherOfChampions $record): string => $record->kabupaten_kota)
                    ->sortable(),
                TextColumn::make('asal_instansi')
                    ->label('Instansi/sekolah')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->formatStateUsing(fn (string $state): string => PendaftarTeacherOfChampions::JENJANG[$state] ?? $state)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('status_kepegawaian')
                    ->label('Kepegawaian')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bidang_studi')
                    ->label('Bidang studi')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lama_mengabdi_tahun')
                    ->label('Mengabdi (thn)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('asal_universitas')
                    ->label('Universitas (S1)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('alumni_unimed')
                    ->label('Alumni UNIMED')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('instagram')
                    ->label('Instagram')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PendaftarTeacherOfChampions::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'terverifikasi' => 'info',
                        'lolos' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('catatan_internal')
                    ->label('Catatan internal')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Daftar (WIB)')
                    ->dateTime('d M Y, H:i')
                    ->timezone(Kegiatan::TIMEZONE)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PendaftarTeacherOfChampions::STATUSES),
                SelectFilter::make('provinsi')
                    ->label('Provinsi')
                    ->options($provinsiOptions)
                    ->searchable(),
                SelectFilter::make('jenjang')
                    ->label('Jenjang')
                    ->options(PendaftarTeacherOfChampions::JENJANG),
                TernaryFilter::make('alumni_unimed')
                    ->label('Alumni UNIMED')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
                Filter::make('tanggal_daftar')
                    ->label('Tanggal daftar (WIB)')
                    ->schema([
                        DatePicker::make('dari')->label('Daftar dari (WIB)'),
                        DatePicker::make('sampai')->label('Daftar sampai (WIB)'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::applyRegistrationDateRange($query, $data['dari'] ?? null, $data['sampai'] ?? null))
                    ->indicateUsing(function (array $data): array {
                        return collect([
                            'dari' => filled($data['dari'] ?? null) ? 'Dari '.CarbonImmutable::parse($data['dari'])->format('d M Y') : null,
                            'sampai' => filled($data['sampai'] ?? null) ? 'Sampai '.CarbonImmutable::parse($data['sampai'])->format('d M Y') : null,
                        ])->filter()->all();
                    }),
                Filter::make('kemungkinan_duplikat')
                    ->label('Hanya kemungkinan duplikat')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereKey(array_keys(app(PendaftarDuplicateDetector::class)->duplicates()))),
            ])
            ->recordActions([
                EditAction::make()->label('Status & catatan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::statusBulkAction('tandaiTerverifikasi', 'terverifikasi', 'Tandai terverifikasi', Heroicon::OutlinedCheckBadge),
                    self::statusBulkAction('tandaiLolos', 'lolos', 'Tandai lolos', Heroicon::OutlinedTrophy),
                    self::statusBulkAction('tandaiDitolak', 'ditolak', 'Tandai ditolak', Heroicon::OutlinedXCircle),
                    BulkAction::make('eksporTerpilih')
                        ->label('Ekspor terpilih (Excel)')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->visible(fn (): bool => self::userCan('export'))
                        ->action(fn (Collection $records) => app(PendaftarTeacherOfChampionsExporter::class)->download($records, 'xlsx', ['sumber' => 'terpilih'])),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => self::userCan('deleteAny')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePendaftarTeacherOfChampions::route('/'),
        ];
    }

    public static function summary(): string
    {
        $total = PendaftarTeacherOfChampions::query()->count();
        $alumni = PendaftarTeacherOfChampions::query()->where('alumni_unimed', true)->count();

        return sprintf('Total %d pendaftar · Alumni UNIMED: %d · Bukan alumni UNIMED: %d', $total, $alumni, $total - $alumni);
    }

    public static function userCan(string $ability): bool
    {
        return Gate::allows($ability, PendaftarTeacherOfChampions::class);
    }

    /**
     * Dates are picked in WIB while created_at is stored in UTC.
     *
     * @param  Builder<PendaftarTeacherOfChampions>  $query
     * @return Builder<PendaftarTeacherOfChampions>
     */
    public static function applyRegistrationDateRange(Builder $query, ?string $from, ?string $until): Builder
    {
        return $query
            ->when(filled($from), fn (Builder $query): Builder => $query->where(
                'created_at',
                '>=',
                CarbonImmutable::parse($from, Kegiatan::TIMEZONE)->startOfDay()->utc(),
            ))
            ->when(filled($until), fn (Builder $query): Builder => $query->where(
                'created_at',
                '<',
                CarbonImmutable::parse($until, Kegiatan::TIMEZONE)->startOfDay()->addDay()->utc(),
            ));
    }

    /**
     * Applies only sensible transitions (see STATUS_TRANSITIONS) and reports how many were skipped.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $records
     * @return array{updated: int, skipped: int}
     */
    public static function bulkUpdateStatus(Collection $records, string $status): array
    {
        $updated = app(AuditLogger::class)->withContext(['massal' => true], function () use ($records, $status): int {
            $updated = 0;

            foreach ($records as $record) {
                if (! $record->canTransitionTo($status)) {
                    continue;
                }

                $record->update(['status' => $status]);
                $updated++;
            }

            return $updated;
        });

        return ['updated' => $updated, 'skipped' => $records->count() - $updated];
    }

    private static function statusBulkAction(string $name, string $status, string $label, Heroicon $icon): BulkAction
    {
        $allowedFrom = collect(PendaftarTeacherOfChampions::STATUS_TRANSITIONS[$status])
            ->map(fn (string $from): string => PendaftarTeacherOfChampions::STATUSES[$from])
            ->implode(' atau ');

        return BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->visible(fn (): bool => self::userCan('updateAny'))
            ->requiresConfirmation()
            ->modalHeading($label.'?')
            ->modalDescription("Hanya pendaftar berstatus {$allowedFrom} yang diubah; sisanya dilewati.")
            ->action(function (Collection $records) use ($status): void {
                abort_unless(self::userCan('updateAny'), 403);

                $result = self::bulkUpdateStatus($records, $status);

                Notification::make()
                    ->title("{$result['updated']} pendaftar diubah menjadi ".PendaftarTeacherOfChampions::STATUSES[$status])
                    ->body($result['skipped'] > 0 ? "{$result['skipped']} dilewati karena statusnya tidak sesuai." : null)
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}

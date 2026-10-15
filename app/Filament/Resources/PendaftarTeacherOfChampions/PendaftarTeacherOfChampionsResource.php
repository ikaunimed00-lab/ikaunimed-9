<?php

namespace App\Filament\Resources\PendaftarTeacherOfChampions;

use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ManagePendaftarTeacherOfChampions;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\PendaftarTeacherOfChampionsExporter;
use App\Services\WilayahService;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
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
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        $provinsiOptions = collect(app(WilayahService::class)->provinsi())
            ->mapWithKeys(fn (string $provinsi): array => [$provinsi => $provinsi])
            ->all();
        $canViewContact = self::userCan('viewContact');

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
            ])
            ->recordActions([
                EditAction::make()->label('Status & catatan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('ubahStatus')
                        ->label('Ubah status')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->visible(fn (): bool => self::userCan('updateAny'))
                        ->schema([
                            Select::make('status')
                                ->label('Status baru')
                                ->options(PendaftarTeacherOfChampions::STATUSES)
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            PendaftarTeacherOfChampions::query()
                                ->whereKey($records->modelKeys())
                                ->update(['status' => $data['status']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('eksporTerpilih')
                        ->label('Ekspor terpilih (Excel)')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->visible(fn (): bool => self::userCan('export'))
                        ->action(fn (Collection $records) => app(PendaftarTeacherOfChampionsExporter::class)->download($records, 'xlsx')),
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
}

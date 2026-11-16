<?php

namespace App\Filament\Resources\TocBerkasFinalis;

use App\Filament\Resources\TocBerkasFinalis\Pages\ManageTocBerkasFinalis;
use App\Jobs\GenerateTocBerkasKeputusanZipJob;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasRejectionMailLog;
use App\Services\TocBerkasAdminService;
use App\Services\TocBerkasKeputusanExportService;
use App\Services\TocBerkasVerificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TocBerkasFinalisResource extends Resource
{
    protected static ?string $model = PendaftarTeacherOfChampions::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Berkas Finalis ToC';

    protected static ?string $modelLabel = 'Berkas Finalis ToC';

    protected static ?string $pluralModelLabel = 'Berkas Finalis ToC';

    protected static ?string $slug = 'berkas-finalis-toc';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canViewAny(): bool
    {
        return self::userCanManage();
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
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $service = app(TocBerkasAdminService::class);

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('lulus_penyisihan', true)
                ->with(['berkas.verifier', 'rejectionMailLogs']))
            ->defaultSort('nomor_pendaftaran')
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
                TextColumn::make('bidang_studi')
                    ->label('Bidang studi')
                    ->toggleable(),
                TextColumn::make('status_foto')
                    ->label('Foto')
                    ->state(fn (PendaftarTeacherOfChampions $record): string => self::statusOf($record, TocBerkas::JENIS_FOTO))
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state)),
                TextColumn::make('status_pakta')
                    ->label('Pakta')
                    ->state(fn (PendaftarTeacherOfChampions $record): string => self::statusOf($record, TocBerkas::JENIS_PAKTA))
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state)),
                TextColumn::make('status_surat')
                    ->label('Surat')
                    ->state(fn (PendaftarTeacherOfChampions $record): string => self::statusOf($record, TocBerkas::JENIS_SURAT))
                    ->badge()
                    ->color(fn (string $state): string => self::statusColor($state)),
                TextColumn::make('status_keseluruhan')
                    ->label('Keseluruhan')
                    ->state(function (PendaftarTeacherOfChampions $record) use ($service): string {
                        $byJenis = $record->berkas->keyBy('jenis');

                        return $service->overallStatus(
                            $byJenis->get(TocBerkas::JENIS_FOTO),
                            $byJenis->get(TocBerkas::JENIS_PAKTA),
                            $byJenis->get(TocBerkas::JENIS_SURAT),
                        );
                    })
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lengkap' => 'Lengkap',
                        'lengkap_diterima' => 'Lengkap & diterima',
                        'menunggu' => 'Menunggu verifikasi',
                        'ditolak' => 'Ada ditolak',
                        default => 'Belum lengkap',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'lengkap', 'lengkap_diterima' => 'success',
                        'menunggu' => 'warning',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('unggah_terakhir')
                    ->label('Unggah terakhir')
                    ->state(fn (PendaftarTeacherOfChampions $record) => $record->berkas->max('uploaded_at'))
                    ->dateTime('d M Y, H:i')
                    ->timezone(Kegiatan::TIMEZONE)
                    ->placeholder('-'),
                TextColumn::make('diverifikasi_oleh')
                    ->label('Diverifikasi oleh')
                    ->state(fn (PendaftarTeacherOfChampions $record): ?string => self::latestVerified($record)?->verifier?->name)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('diverifikasi_pada')
                    ->label('Diverifikasi pada')
                    ->state(fn (PendaftarTeacherOfChampions $record) => self::latestVerified($record)?->verified_at)
                    ->dateTime('d M Y, H:i')
                    ->timezone(Kegiatan::TIMEZONE)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('email_penolakan')
                    ->label('Email penolakan')
                    ->state(fn (PendaftarTeacherOfChampions $record): ?string => $record->rejectionMailLogs
                        ->sortByDesc('id')
                        ->first()?->status)
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => TocBerkasRejectionMailLog::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        TocBerkasRejectionMailLog::STATUS_SENT => 'success',
                        TocBerkasRejectionMailLog::STATUS_FAILED => 'danger',
                        TocBerkasRejectionMailLog::STATUS_SKIPPED => 'gray',
                        default => 'warning',
                    })
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('setuju_publikasi_at')
                    ->label('Setuju publikasi')
                    ->boolean()
                    ->getStateUsing(fn (PendaftarTeacherOfChampions $record): bool => $record->setuju_publikasi_at !== null),
            ])
            ->filters([
                SelectFilter::make('kelengkapan')
                    ->label('Kelengkapan berkas')
                    ->options([
                        'belum_lengkap' => 'Belum lengkap',
                        'menunggu' => 'Menunggu verifikasi',
                        'ditolak' => 'Ditolak',
                        'lengkap' => 'Sudah lengkap',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            'belum_lengkap' => $query->where(function (Builder $q): void {
                                $q->whereDoesntHave('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_FOTO))
                                    ->orWhereDoesntHave('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_PAKTA))
                                    ->orWhereDoesntHave('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_SURAT));
                            }),
                            'menunggu' => $query->whereHas('berkas', fn (Builder $b) => $b->where('status', TocBerkas::STATUS_MENUNGGU)),
                            'ditolak' => $query->whereHas('berkas', fn (Builder $b) => $b->where('status', TocBerkas::STATUS_DITOLAK)),
                            'lengkap' => $query
                                ->whereHas('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_FOTO))
                                ->whereHas('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_PAKTA))
                                ->whereHas('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_SURAT)),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('kelolaBerkas')
                    ->label('Kelola berkas')
                    ->icon(Heroicon::OutlinedFolderOpen)
                    ->modalHeading(fn (PendaftarTeacherOfChampions $record): string => 'Berkas '.$record->nomor_pendaftaran)
                    ->modalContent(fn (PendaftarTeacherOfChampions $record) => view('filament.toc.berkas-detail', [
                        'record' => $record->loadMissing('berkas'),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('unduhZipKeputusan')
                        ->label('Unduh ZIP berkas + Excel keputusan')
                        ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
                        ->visible(fn (): bool => self::userCanManage())
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => self::queueKeputusanExport($records)),
                    BulkAction::make('terimaSemuaMenunggu')
                        ->label('Terima semua berkas yang menunggu')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->visible(fn (): bool => self::userCanManage())
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Terima semua berkas yang menunggu?')
                        ->modalDescription(fn (Collection $records): string => self::waitingDescription($records))
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => self::acceptAllWaiting($records)),
                    BulkAction::make('unduhZipFoto')
                        ->label('Unduh ZIP foto diterima')
                        ->icon(Heroicon::OutlinedArchiveBox)
                        ->action(fn (Collection $records) => app(TocBerkasAdminService::class)
                            ->downloadAcceptedPhotosZip($records->load('berkas'))),
                    BulkAction::make('eksporCsv')
                        ->label('Ekspor CSV status berkas')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(fn (Collection $records) => app(TocBerkasAdminService::class)
                            ->exportCsv($records->load('berkas'))),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTocBerkasFinalis::route('/'),
        ];
    }

    public static function latestVerified(PendaftarTeacherOfChampions $record): ?TocBerkas
    {
        return $record->berkas
            ->whereNotNull('verified_at')
            ->sortByDesc('verified_at')
            ->first();
    }

    /**
     * Mengantrekan job ZIP + Excel keputusan untuk peserta terpilih.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $records
     */
    public static function queueKeputusanExport(Collection $records): void
    {
        abort_unless(self::userCanManage(), 403);

        if ($records->isEmpty()) {
            Notification::make()->title('Tidak ada peserta untuk diekspor')->warning()->send();

            return;
        }

        try {
            app(TocBerkasKeputusanExportService::class)->assertWithinLimit($records->count());
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Ekspor dibatalkan')
                ->body(collect($exception->errors())->flatten()->implode(' '))
                ->danger()
                ->send();

            return;
        }

        GenerateTocBerkasKeputusanZipJob::dispatch((int) Auth::id(), $records->pluck('id')->map(fn ($id): int => (int) $id)->all());

        Notification::make()
            ->title('Ekspor sedang diproses')
            ->body($records->count().' peserta. Tautan unduhan (berlaku 1 jam) akan muncul di notifikasi dashboard Anda dan di menu "Unduhan saya".')
            ->success()
            ->send();
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $records
     */
    public static function waitingDescription(Collection $records): string
    {
        $summary = app(TocBerkasVerificationService::class)->waitingSummary($records);

        return "{$summary['berkas']} berkas berstatus menunggu milik {$summary['peserta']} peserta akan diterima. Berkas ditolak/diterima tidak berubah.";
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $records
     */
    public static function acceptAllWaiting(Collection $records): void
    {
        abort_unless(self::userCanManage(), 403);

        $result = app(TocBerkasVerificationService::class)->acceptWaitingForMany($records, Auth::user());

        Notification::make()
            ->title("{$result['berkas']} berkas diterima")
            ->body("Milik {$result['peserta']} peserta.")
            ->success()
            ->send();
    }

    private static function statusOf(PendaftarTeacherOfChampions $record, string $jenis): string
    {
        return $record->berkas->firstWhere('jenis', $jenis)?->status ?? 'belum';
    }

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'diterima' => 'success',
            'menunggu' => 'warning',
            'ditolak' => 'danger',
            default => 'gray',
        };
    }

    private static function userCanManage(): bool
    {
        return Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false;
    }
}

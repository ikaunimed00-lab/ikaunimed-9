<?php

namespace App\Filament\Resources\TocBerkasFinalis;

use App\Filament\Resources\TocBerkasFinalis\Pages\ManageTocBerkasFinalis;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasRejectionMailLog;
use App\Models\User;
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
use Illuminate\View\View;

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
        return self::userCanReview();
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
                SelectFilter::make('bagian')
                    ->label('Bagian')
                    ->options(fn (): array => app(TocBerkasKeputusanExportService::class)->bagianFilterOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        $ids = app(TocBerkasKeputusanExportService::class)->idsForBagian((int) $value);

                        if ($ids === []) {
                            return $query->whereRaw('0 = 1');
                        }

                        return $query->whereIn($query->getModel()->getQualifiedKeyName(), $ids);
                    }),
                SelectFilter::make('status_pemeriksaan')
                    ->label('Status pemeriksaan')
                    ->options([
                        'belum_diperiksa' => 'Belum diperiksa',
                        'sudah_diterima' => 'Sudah diterima',
                        'ada_ditolak' => 'Ada yang ditolak',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            'belum_diperiksa' => $query->whereDoesntHave(
                                'berkas',
                                fn (Builder $b) => $b->whereIn('status', [TocBerkas::STATUS_DITERIMA, TocBerkas::STATUS_DITOLAK]),
                            ),
                            'sudah_diterima' => $query
                                ->whereHas('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_FOTO)->where('status', TocBerkas::STATUS_DITERIMA))
                                ->whereHas('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_PAKTA)->where('status', TocBerkas::STATUS_DITERIMA))
                                ->whereHas('berkas', fn (Builder $b) => $b->where('jenis', TocBerkas::JENIS_SURAT)->where('status', TocBerkas::STATUS_DITERIMA)),
                            'ada_ditolak' => $query->whereHas(
                                'berkas',
                                fn (Builder $b) => $b->where('status', TocBerkas::STATUS_DITOLAK),
                            ),
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
                        ->modalHeading('Unduh ZIP per bagian')
                        ->modalDescription('Setiap bagian diunduh langsung dari server (maks. 50 peserta).')
                        ->modalContent(fn (Collection $records): View => self::keputusanExportModal($records))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->deselectRecordsAfterCompletion(),
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
                        ->visible(fn (): bool => self::userCanManage())
                        ->action(function (Collection $records) {
                            self::abortUnlessCanManage();

                            return app(TocBerkasAdminService::class)
                                ->downloadAcceptedPhotosZip($records->load('berkas'));
                        }),
                    BulkAction::make('eksporCsv')
                        ->label('Ekspor CSV status berkas')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->visible(fn (): bool => self::userCanManage())
                        ->action(function (Collection $records) {
                            self::abortUnlessCanManage();

                            return app(TocBerkasAdminService::class)
                                ->exportCsv($records->load('berkas'));
                        }),
                ])->visible(fn (): bool => self::userCanManage()),
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
     * Modal unduhan langsung ZIP + Excel keputusan per bagian.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $records
     */
    public static function keputusanExportModal(Collection $records): View
    {
        abort_unless(self::userCanManage(), 403);

        if ($records->isEmpty()) {
            return view('filament.toc.unduh-zip-bagian', [
                'manifest' => ['error' => 'Tidak ada peserta untuk diekspor. Sesuaikan filter atau pilih baris.'],
            ]);
        }

        try {
            $ids = $records
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            $manifest = app(TocBerkasKeputusanExportService::class)
                ->rememberSelection((int) Auth::id(), $ids);
        } catch (ValidationException $exception) {
            return view('filament.toc.unduh-zip-bagian', [
                'manifest' => [
                    'error' => collect($exception->errors())->flatten()->implode(' '),
                ],
            ]);
        }

        return view('filament.toc.unduh-zip-bagian', ['manifest' => $manifest]);
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

    public static function userCanManage(): bool
    {
        return Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false;
    }

    public static function abortUnlessCanManage(): void
    {
        abort_unless(self::userCanManage(), 403);
    }

    private static function userCanReview(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasSystemRole(['super_admin', 'admin']) || $user->can('toc.berkas.view');
    }
}

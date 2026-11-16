<?php

namespace App\Filament\Resources\TocBerkasFinalis\Pages;

use App\Filament\Resources\TocBerkasFinalis\TocBerkasFinalisResource;
use App\Jobs\GenerateTocBerkasKeputusanZipJob;
use App\Models\TocBerkasExportArtifact;
use App\Services\TocBerkasAdminService;
use App\Services\TocBerkasKeputusanImportService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ManageTocBerkasFinalis extends ManageRecords
{
    protected static string $resource = TocBerkasFinalisResource::class;

    public const IMPORT_SESSION_KEY = 'toc_berkas_import_pending_path';

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('eksporSemua')
                    ->label('Ekspor CSV semua')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn () => app(TocBerkasAdminService::class)->exportCsv(
                        $this->getFilteredSortedTableQuery()->with('berkas')->get(),
                    )),
                Action::make('unduhZipSemua')
                    ->label('ZIP foto diterima (filter aktif)')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->action(fn () => app(TocBerkasAdminService::class)->downloadAcceptedPhotosZip(
                        $this->getFilteredSortedTableQuery()->with('berkas')->get(),
                    )),
                Action::make('terimaSemuaMenunggu')
                    ->label('Terima semua berkas yang menunggu (filter aktif)')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Terima semua berkas yang menunggu?')
                    ->modalDescription(fn (): string => TocBerkasFinalisResource::waitingDescription(
                        $this->getFilteredSortedTableQuery()->get(),
                    ))
                    ->action(fn () => TocBerkasFinalisResource::acceptAllWaiting(
                        $this->getFilteredSortedTableQuery()->get(),
                    )),
            ])
                ->label('Aksi')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('gray')
                ->button()
                ->dropdownAutoPlacement(),
            Action::make('unduhZipKeputusan')
                ->label('Unduh ZIP + Excel keputusan')
                ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
                ->action(fn () => TocBerkasFinalisResource::queueKeputusanExport(
                    $this->getFilteredSortedTableQuery()->get(),
                )),
            Action::make('imporKeputusan')
                ->label('Impor keputusan (Excel)')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->rateLimit(10)
                ->modalHeading('Impor keputusan berkas')
                ->modalDescription('Unggah file keputusan-berkas.xlsx hasil unduhan. Pratinjau ditampilkan sebelum perubahan diterapkan.')
                ->modalSubmitActionLabel('Lanjut ke pratinjau')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel keputusan (.xlsx, maks. 5 MB)')
                        ->required()
                        ->disk(config('toc.berkas.disk'))
                        ->directory('toc/imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->rules(['extensions:xlsx'])
                        ->maxSize((int) config('toc.berkas.import_max_kb')),
                ])
                ->action(function (array $data): void {
                    abort_unless(Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false, 403);

                    $this->forgetPendingImport();
                    session()->put(self::IMPORT_SESSION_KEY, $data['file']);

                    $this->replaceMountedAction('konfirmasiImporKeputusan');
                }),
            Action::make('unduhanSaya')
                ->label('Unduhan saya')
                ->icon(Heroicon::OutlinedCloudArrowDown)
                ->color('gray')
                ->modalHeading('ZIP berkas + Excel keputusan')
                ->modalContent(fn () => view('filament.toc.unduhan-ekspor', [
                    'artifacts' => TocBerkasExportArtifact::query()
                        ->where('user_id', Auth::id())
                        ->where('expires_at', '>', now())
                        ->orderByDesc('batch_id')
                        ->orderBy('part_number')
                        ->orderByDesc('id')
                        ->get()
                        ->map(fn (TocBerkasExportArtifact $artifact): array => [
                            'label' => $artifact->label(),
                            'dibuat' => $artifact->created_at->timezone(config('toc.timezone'))->format('d M Y H:i'),
                            'pesertaCount' => $artifact->peserta_count,
                            'berlakuSampai' => $artifact->expires_at->timezone(config('toc.timezone'))->format('H:i'),
                            'url' => GenerateTocBerkasKeputusanZipJob::downloadUrl($artifact),
                        ]),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
        ];
    }

    public function konfirmasiImporKeputusanAction(): Action
    {
        return Action::make('konfirmasiImporKeputusan')
            ->modalHeading('Pratinjau impor keputusan')
            ->modalContent(fn () => view('filament.toc.impor-keputusan-pratinjau', [
                'summary' => $this->pendingImportPreview(),
            ]))
            ->modalSubmitActionLabel('Terapkan keputusan')
            ->modalSubmitAction(fn (Action $action) => $this->pendingImportHasChanges() ? $action : false)
            ->modalCancelActionLabel('Batal')
            ->after(fn () => $this->forgetPendingImport())
            ->action(function (): void {
                abort_unless(Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false, 403);

                $path = $this->pendingImportAbsolutePath();

                if ($path === null) {
                    Notification::make()->title('File impor tidak ditemukan')->danger()->send();

                    return;
                }

                $summary = app(TocBerkasKeputusanImportService::class)->apply($path, Auth::user());

                Notification::make()
                    ->title('Keputusan diterapkan')
                    ->body("{$summary['terima']} diterima, {$summary['tolak']} ditolak, {$summary['sudah_sesuai']} sudah sesuai, ".count($summary['errors']).' baris dilewati karena kesalahan.')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function pendingImportPreview(): ?array
    {
        $path = $this->pendingImportAbsolutePath();

        return $path === null ? null : app(TocBerkasKeputusanImportService::class)->preview($path);
    }

    protected function pendingImportHasChanges(): bool
    {
        $summary = $this->pendingImportPreview();

        return $summary !== null
            && $summary['fatal'] === null
            && ($summary['terima'] + $summary['tolak']) > 0;
    }

    protected function pendingImportAbsolutePath(): ?string
    {
        $relative = session(self::IMPORT_SESSION_KEY);
        $disk = Storage::disk(config('toc.berkas.disk'));

        if (! is_string($relative) || ! str_starts_with($relative, 'toc/imports/') || ! $disk->exists($relative)) {
            return null;
        }

        return $disk->path($relative);
    }

    protected function forgetPendingImport(): void
    {
        $relative = session(self::IMPORT_SESSION_KEY);

        if (is_string($relative) && str_starts_with($relative, 'toc/imports/')) {
            Storage::disk(config('toc.berkas.disk'))->delete($relative);
        }

        session()->forget(self::IMPORT_SESSION_KEY);
    }
}

<?php

namespace App\Filament\Resources\PendaftarTeacherOfChampions\Pages;

use App\Filament\Resources\PendaftarTeacherOfChampions\PendaftarTeacherOfChampionsResource;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\AuditLogger;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewPendaftarTeacherOfChampions extends ViewRecord
{
    protected static string $resource = PendaftarTeacherOfChampionsResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var PendaftarTeacherOfChampions $pendaftar */
        $pendaftar = $this->getRecord();

        app(AuditLogger::class)->record('pendaftar.dilihat', $pendaftar, [
            'pendaftar_id' => $pendaftar->id,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetakPdf')
                ->label('Cetak / PDF')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn (): string => route('toc.pendaftar.print', [
                    'pendaftar' => $this->getRecord(),
                    'autoprint' => 1,
                ]))
                ->openUrlInNewTab(),
            Action::make('salinPesanNomor')
                ->label('Salin pesan nomor pendaftaran')
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->action(function (): void {
                    /** @var PendaftarTeacherOfChampions $record */
                    $record = $this->getRecord();
                    $message = $record->registrationNoticeMessage();

                    $this->js('navigator.clipboard.writeText('.json_encode($message).')');

                    Notification::make()
                        ->title('Pesan siap disalin')
                        ->body('Teks nomor pendaftaran telah disalin ke clipboard.')
                        ->success()
                        ->send();
                }),
            EditAction::make()
                ->label('Status & catatan')
                ->visible(fn (): bool => PendaftarTeacherOfChampionsResource::userCan('updateAny')),
        ];
    }
}

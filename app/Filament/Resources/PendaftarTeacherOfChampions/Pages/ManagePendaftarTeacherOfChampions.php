<?php

namespace App\Filament\Resources\PendaftarTeacherOfChampions\Pages;

use App\Filament\Resources\PendaftarTeacherOfChampions\PendaftarTeacherOfChampionsResource;
use App\Services\PendaftarTeacherOfChampionsExporter;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManagePendaftarTeacherOfChampions extends ManageRecords
{
    protected static string $resource = PendaftarTeacherOfChampionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ekspor')
                ->label('Ekspor')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->visible(fn (): bool => PendaftarTeacherOfChampionsResource::userCan('export'))
                ->modalDescription('Mengekspor semua pendaftar sesuai filter & pencarian yang sedang aktif.')
                ->schema([
                    Select::make('format')
                        ->label('Format')
                        ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (.csv)'])
                        ->default('xlsx')
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    abort_unless(PendaftarTeacherOfChampionsResource::userCan('export'), 403);

                    return app(PendaftarTeacherOfChampionsExporter::class)->download(
                        $this->getFilteredSortedTableQuery()->get(),
                        $data['format'],
                        [
                            'sumber' => 'filter',
                            'filter' => $this->tableFilters,
                            'pencarian' => $this->tableSearch,
                        ],
                    );
                }),
        ];
    }
}

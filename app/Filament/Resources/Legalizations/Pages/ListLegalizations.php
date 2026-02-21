<?php

namespace App\Filament\Resources\Legalizations\Pages;

use App\Filament\Resources\LegalizationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLegalizations extends ListRecords
{
    protected static string $resource = LegalizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

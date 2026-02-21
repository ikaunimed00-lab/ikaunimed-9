<?php

namespace App\Filament\Resources\Legalizations\Pages;

use App\Filament\Resources\LegalizationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLegalization extends ViewRecord
{
    protected static string $resource = LegalizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

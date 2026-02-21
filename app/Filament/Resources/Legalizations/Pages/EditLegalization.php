<?php

namespace App\Filament\Resources\Legalizations\Pages;

use App\Filament\Resources\LegalizationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLegalization extends EditRecord
{
    protected static string $resource = LegalizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

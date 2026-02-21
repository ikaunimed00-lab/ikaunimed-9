<?php

namespace App\Filament\Resources\OrganizationProgramResource\Pages;

use App\Filament\Resources\OrganizationProgramResource;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationProgram extends EditRecord
{
    protected static string $resource = OrganizationProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}

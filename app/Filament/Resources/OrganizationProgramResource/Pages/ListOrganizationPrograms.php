<?php

namespace App\Filament\Resources\OrganizationProgramResource\Pages;

use App\Filament\Resources\OrganizationProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationPrograms extends ListRecords
{
    protected static string $resource = OrganizationProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

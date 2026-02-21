<?php

namespace App\Filament\Resources\HomepageSections\Pages;

use App\Filament\Resources\HomepageSections\HomepageSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHomepageSections extends ManageRecords
{
    protected static string $resource = HomepageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

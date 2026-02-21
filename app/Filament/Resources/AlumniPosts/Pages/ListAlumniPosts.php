<?php

namespace App\Filament\Resources\AlumniPosts\Pages;

use App\Filament\Resources\AlumniPosts\AlumniPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniPosts extends ListRecords
{
    protected static string $resource = AlumniPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

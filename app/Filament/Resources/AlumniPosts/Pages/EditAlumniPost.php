<?php

namespace App\Filament\Resources\AlumniPosts\Pages;

use App\Filament\Resources\AlumniPosts\AlumniPostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniPost extends EditRecord
{
    protected static string $resource = AlumniPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

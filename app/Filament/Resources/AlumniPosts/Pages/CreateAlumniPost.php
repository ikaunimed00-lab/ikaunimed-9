<?php

namespace App\Filament\Resources\AlumniPosts\Pages;

use App\Filament\Resources\AlumniPosts\AlumniPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlumniPost extends CreateRecord
{
    protected static string $resource = AlumniPostResource::class;
}

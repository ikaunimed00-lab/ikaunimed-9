<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        $data['user_id'] = $user->id;
        
        // Jika bukan admin pusat, paksa organization_id sesuai user
        if (!$user->isCentralAdmin()) {
            $data['organization_id'] = $user->organization_id;
        }
        
        return $data;
    }
}

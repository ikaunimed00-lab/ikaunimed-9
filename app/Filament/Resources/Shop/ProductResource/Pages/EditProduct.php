<?php

namespace App\Filament\Resources\Shop\ProductResource\Pages;

use App\Filament\Resources\Shop\ProductResource;
use App\Models\ProductImage;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterSave(): void
    {
        $this->syncImages();
    }

    protected function syncImages(): void
    {
        $record = $this->record;

        if (! $record) {
            return;
        }

        $files = $this->form->getState()['image_files'] ?? [];

        if (! is_array($files)) {
            return;
        }

        $record->images()->delete();

        foreach (array_values($files) as $index => $path) {
            ProductImage::create([
                'product_id' => $record->id,
                'path' => $path,
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }
}

<?php

namespace App\Filament\Resources\Shop\ProductCategoryResource\Pages;

use App\Filament\Resources\Shop\ProductCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;
}


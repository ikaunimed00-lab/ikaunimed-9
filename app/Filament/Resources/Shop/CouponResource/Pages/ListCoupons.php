<?php

namespace App\Filament\Resources\Shop\CouponResource\Pages;

use App\Filament\Resources\Shop\CouponResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}


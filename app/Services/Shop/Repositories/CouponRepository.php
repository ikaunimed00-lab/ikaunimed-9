<?php

namespace App\Services\Shop\Repositories;

use App\Models\Coupon;

class CouponRepository
{
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::query()
            ->where('code', strtoupper($code))
            ->first();
    }
}


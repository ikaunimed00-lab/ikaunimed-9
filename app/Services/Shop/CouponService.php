<?php

namespace App\Services\Shop;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\User;
use App\Services\Shop\Repositories\CouponRepository;
use Carbon\CarbonInterface;

class CouponService
{
    public function __construct(
        private readonly CouponRepository $couponRepository
    ) {
    }

    /**
     * @return array{valid: bool, message: string, discount: int, code?: string, coupon?: Coupon}
     */
    public function evaluateCouponForCart(
        Cart $cart,
        ?User $user,
        string $code,
        float $premiumDiscountRate,
        ?array $premiumDiscountCategorySlugs,
        ?CarbonInterface $now = null
    ): array {
        $normalizedCode = strtoupper(trim($code));

        if ($normalizedCode === '') {
            return $this->invalid('Kode kupon wajib diisi.');
        }

        $coupon = $this->couponRepository->findByCode($normalizedCode);

        if (! $coupon) {
            return $this->invalid('Kode kupon tidak ditemukan.');
        }

        $validationError = $this->validateCouponAvailability($coupon, $now ?? now());

        if ($validationError !== null) {
            return $this->invalid($validationError);
        }

        if ($cart->items->isEmpty()) {
            return $this->invalid('Keranjang belanja kosong.');
        }

        $eligibleBase = $this->calculateEligibleBaseForCoupon(
            $cart,
            $user,
            $coupon,
            $premiumDiscountRate,
            $premiumDiscountCategorySlugs
        );

        if ($eligibleBase <= 0) {
            return $this->invalid('Kupon tidak berlaku untuk produk dalam keranjang Anda.');
        }

        return [
            'valid' => true,
            'message' => 'Kupon berhasil digunakan!',
            'discount' => $this->calculateDiscountFromBase($eligibleBase, $coupon),
            'code' => $coupon->code,
            'coupon' => $coupon,
        ];
    }

    private function validateCouponAvailability(Coupon $coupon, CarbonInterface $now): ?string
    {
        if ($coupon->status !== 'active') {
            return 'Kupon ini sudah tidak aktif.';
        }

        if ($coupon->starts_at && $coupon->starts_at > $now) {
            return 'Kupon ini belum mulai berlaku.';
        }

        if ($coupon->expires_at && $coupon->expires_at < $now) {
            return 'Kupon ini sudah kadaluarsa.';
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return 'Kuota penggunaan kupon ini sudah habis.';
        }

        return null;
    }

    private function calculateEligibleBaseForCoupon(
        Cart $cart,
        ?User $user,
        Coupon $coupon,
        float $premiumDiscountRate,
        ?array $premiumDiscountCategorySlugs
    ): float {
        return (float) $cart->items->reduce(function ($carry, $item) use ($coupon, $user, $premiumDiscountRate, $premiumDiscountCategorySlugs) {
            $product = $item->product;
            if (! $product) {
                return $carry;
            }

            $isCouponEligible = false;

            if (empty($coupon->eligible_product_ids) && empty($coupon->eligible_categories)) {
                $isCouponEligible = true;
            } else {
                $productId = (string) $item->product_id;
                $categoryId = (string) ($product->product_category_id ?? '');
                $productMatch = ! empty($coupon->eligible_product_ids) && in_array($productId, $coupon->eligible_product_ids, true);
                $categoryMatch = ! empty($coupon->eligible_categories) && in_array($categoryId, $coupon->eligible_categories, true);
                $isCouponEligible = $productMatch || $categoryMatch;
            }

            if (! $isCouponEligible) {
                return $carry;
            }

            $lineTotal = $item->price_snapshot * $item->quantity;
            $isPremiumMember = $user?->hasRole('premium_member') ?? false;
            $isPremiumEligible = false;

            if ($isPremiumMember && $product->type === 'physical') {
                if ($premiumDiscountCategorySlugs === null || $premiumDiscountCategorySlugs === []) {
                    $isPremiumEligible = true;
                } else {
                    $categorySlug = (string) ($product->category?->slug ?? '');
                    $isPremiumEligible = $categorySlug !== '' && in_array($categorySlug, $premiumDiscountCategorySlugs, true);
                }
            }

            if ($isPremiumEligible) {
                $lineTotal = $lineTotal * (1 - $premiumDiscountRate);
            }

            return $carry + $lineTotal;
        }, 0);
    }

    private function calculateDiscountFromBase(float $baseForCoupon, Coupon $coupon): int
    {
        if ($coupon->type === 'fixed') {
            return (int) min($coupon->value, $baseForCoupon);
        }

        $raw = $baseForCoupon * (float) $coupon->value;

        if ($coupon->max_discount !== null) {
            $raw = min($raw, (float) $coupon->max_discount);
        }

        $raw = min($raw, $baseForCoupon);

        return (int) floor($raw);
    }

    /**
     * @return array{valid: bool, message: string, discount: int}
     */
    private function invalid(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'discount' => 0,
        ];
    }
}


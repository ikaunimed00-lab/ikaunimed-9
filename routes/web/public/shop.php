<?php

use App\Http\Controllers\ShopController;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Support\Facades\Route;

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/cart', [ShopController::class, 'cart'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class])
    ->name('shop.cart.index');
Route::get('/shop/checkout', [ShopController::class, 'checkout'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class])
    ->name('shop.checkout');
Route::post('/shop/checkout', [ShopController::class, 'processCheckout'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class, 'throttle:20,1'])
    ->name('shop.checkout.process');
Route::post('/shop/checkout/check-coupon', [ShopController::class, 'checkCoupon'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class, 'throttle:20,1'])
    ->name('shop.checkout.check-coupon');
Route::get('/shop/orders', [ShopController::class, 'orders'])
    ->middleware(['auth'])
    ->name('shop.orders.index');
Route::get('/shop/orders/{order}', [ShopController::class, 'showOrder'])
    ->middleware(['auth'])
    ->name('shop.orders.show');
Route::post('/shop/orders/{order}/upload-proof', [ShopController::class, 'uploadPaymentProof'])
    ->middleware(['auth'])
    ->name('shop.orders.upload-proof');
Route::post('/shop/{product:slug}/cart', [ShopController::class, 'addToCart'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class, 'throttle:20,1'])
    ->name('shop.cart.add');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

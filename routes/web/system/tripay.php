<?php

use App\Http\Controllers\TripayWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/tripay/shop', [TripayWebhookController::class, 'shop'])
    ->name('webhook.tripay.shop');

<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/subscriber', [DashboardController::class, 'subscriberDashboard'])
    ->middleware(['role:subscriber', EnsureProfileCompleted::class])
    ->name('dashboard.subscriber');

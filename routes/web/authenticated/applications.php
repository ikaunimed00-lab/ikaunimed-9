<?php

use App\Http\Controllers\Dashboard\Subscriber\ScholarshipApplicationController;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/subscriber/applications')
    ->name('dashboard.subscriber.applications.')
    ->middleware(EnsureProfileCompleted::class)
    ->controller(ScholarshipApplicationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });

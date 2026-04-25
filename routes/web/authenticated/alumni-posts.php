<?php

use App\Http\Controllers\Dashboard\AlumniPostDashboardController;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/subscriber/alumni-posts')
    ->name('dashboard.subscriber.alumni-posts.')
    ->middleware(EnsureProfileCompleted::class)
    ->controller(AlumniPostDashboardController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{alumniPost}/edit', 'edit')->name('edit');
        Route::put('/{alumniPost}', 'update')->name('update');
        Route::delete('/{alumniPost}', 'destroy')->name('destroy');
    });

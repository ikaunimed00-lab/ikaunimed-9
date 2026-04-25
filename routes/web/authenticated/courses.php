<?php

use App\Http\Controllers\Dashboard\CourseDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('courses', [CourseDashboardController::class, 'index'])->name('courses.index');
        Route::get('courses/{course}/participants', [CourseDashboardController::class, 'participants'])
            ->name('courses.participants');
    });

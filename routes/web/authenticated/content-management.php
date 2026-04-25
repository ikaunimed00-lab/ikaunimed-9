<?php

use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
    ->middleware('role:admin,editor')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/database', [DatabaseController::class, 'index'])->name('database.index');
        Route::get('/database/{user}', [DatabaseController::class, 'show'])->name('database.show');
        Route::get('/pages', [StaticPageController::class, 'index'])->name('pages.index');
        Route::get('/pages/{page}/edit', [StaticPageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [StaticPageController::class, 'update'])->name('pages.update');
    });

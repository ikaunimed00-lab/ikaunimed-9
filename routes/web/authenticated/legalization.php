<?php

use App\Http\Controllers\LegalizationController;
use Illuminate\Support\Facades\Route;

Route::get('/legalization', [LegalizationController::class, 'index'])->name('legalization.index');
Route::get('/legalization/create', [LegalizationController::class, 'create'])->name('legalization.create');
Route::post('/legalization', [LegalizationController::class, 'store'])->name('legalization.store');
Route::get('/legalization/{legalization}', [LegalizationController::class, 'show'])->name('legalization.show');
Route::post('/legalization/{legalization}/upload', [LegalizationController::class, 'upload'])
    ->name('legalization.upload');

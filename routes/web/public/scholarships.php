<?php

use App\Http\Controllers\ScholarshipController;
use Illuminate\Support\Facades\Route;

Route::get('/beasiswa', [ScholarshipController::class, 'index'])->name('scholarships.index');
Route::get('/beasiswa/{scholarship:slug}', [ScholarshipController::class, 'show'])->name('scholarships.show');
Route::post('/beasiswa/{scholarship:slug}/apply', [ScholarshipController::class, 'apply'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('scholarships.apply');

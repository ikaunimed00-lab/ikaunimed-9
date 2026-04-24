<?php

use App\Http\Controllers\JobVacancyController;
use Illuminate\Support\Facades\Route;

Route::get('/lowongan-kerja', [JobVacancyController::class, 'index'])->name('jobs.index');
Route::get('/lowongan-kerja/{vacancy:slug}', [JobVacancyController::class, 'show'])->name('jobs.show');
Route::post('/lowongan-kerja/{vacancy:slug}/interest', [JobVacancyController::class, 'toggleInterest'])
    ->middleware('auth')
    ->name('jobs.interest');

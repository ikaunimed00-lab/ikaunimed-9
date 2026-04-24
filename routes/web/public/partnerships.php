<?php

use App\Http\Controllers\PartnershipController;
use Illuminate\Support\Facades\Route;

Route::get('/kemitraan', [PartnershipController::class, 'index'])->name('partnerships.index');
Route::get('/kemitraan/{partnership:slug}', [PartnershipController::class, 'show'])->name('partnerships.show');

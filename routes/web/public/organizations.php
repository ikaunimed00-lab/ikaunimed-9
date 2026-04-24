<?php

use App\Http\Controllers\PublicOrganizationController;
use Illuminate\Support\Facades\Route;

Route::get('/organisasi', [PublicOrganizationController::class, 'index'])->name('organizations.index');
Route::get('/organisasi/{organization:slug}', [PublicOrganizationController::class, 'show'])->name('organizations.show');

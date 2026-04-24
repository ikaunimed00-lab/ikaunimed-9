<?php

use App\Http\Controllers\AlumniDirectoryController;
use App\Http\Controllers\Community\AlumniPostController;
use Illuminate\Support\Facades\Route;

Route::get('/kabar-alumni', [AlumniPostController::class, 'index'])->name('alumni-posts.index');
Route::get('/kabar-alumni/{alumniPost:slug}', [AlumniPostController::class, 'show'])->name('alumni-posts.show');
Route::get('/alumni', [AlumniDirectoryController::class, 'index'])->name('alumni.directory');

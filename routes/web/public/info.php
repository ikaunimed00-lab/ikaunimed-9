<?php

use App\Http\Controllers\Info\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/tentang-kami', [PageController::class, 'about'])->name('info.about');
Route::get('/struktur-organisasi', [PageController::class, 'structure'])->name('info.structure');
Route::get('/faq', [PageController::class, 'faq'])->name('info.faq');
Route::get('/syarat-ketentuan', [PageController::class, 'terms'])->name('info.terms');
Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('info.privacy');
Route::get('/hubungi-kami', [PageController::class, 'contact'])->name('info.contact');

<?php

use App\Http\Controllers\TocPendaftarPrintController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/web/public.php';
require __DIR__.'/web/system.php';
require __DIR__.'/web/authenticated.php';

Route::get('/toc/pendaftar/{pendaftar}/cetak', TocPendaftarPrintController::class)
    ->name('toc.pendaftar.print');

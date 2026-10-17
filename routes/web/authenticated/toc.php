<?php

use App\Http\Controllers\TocPrintReportController;
use Illuminate\Support\Facades\Route;

Route::get('/toc/laporan-cetak', TocPrintReportController::class)
    ->name('toc.print-report');

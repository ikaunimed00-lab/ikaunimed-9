<?php

use App\Http\Controllers\Admin\TocBerkasAdminController;
use App\Http\Controllers\TocPrintReportController;
use Illuminate\Support\Facades\Route;

Route::get('/toc/laporan-cetak', TocPrintReportController::class)
    ->name('toc.print-report');

Route::middleware('role:admin,super_admin')->prefix('admin/toc')->name('admin.toc.')->group(function () {
    Route::get('/berkas/{berkas}/unduh', [TocBerkasAdminController::class, 'download'])
        ->name('berkas.download');
    Route::post('/berkas/{berkas}/terima', [TocBerkasAdminController::class, 'terima'])
        ->name('berkas.terima');
    Route::post('/berkas/{berkas}/tolak', [TocBerkasAdminController::class, 'tolak'])
        ->name('berkas.tolak');
    Route::get('/berkas/exports/langsung/{token}/bagian/{bagian}', [TocBerkasAdminController::class, 'downloadLangsung'])
        ->whereUuid('token')
        ->whereNumber('bagian')
        ->name('berkas.exports.langsung');
    Route::get('/berkas/exports/{artifact:token}', [TocBerkasAdminController::class, 'downloadExport'])
        ->middleware('signed')
        ->name('berkas.exports.download');
});

<?php

use App\Http\Controllers\TocBerkasController;
use App\Http\Controllers\TocPengumumanController;
use Illuminate\Support\Facades\Route;

Route::middleware('toc.noindex')->group(function () {
    Route::get('/toc/pengumuman', [TocPengumumanController::class, 'show'])
        ->name('toc.pengumuman');
    Route::post('/toc/pengumuman', [TocPengumumanController::class, 'check'])
        ->middleware('throttle:toc-login')
        ->name('toc.pengumuman.check');

    Route::get('/toc/berkas', [TocBerkasController::class, 'showLogin'])
        ->name('toc.berkas');
    Route::post('/toc/berkas', [TocBerkasController::class, 'login'])
        ->middleware('throttle:toc-login')
        ->name('toc.berkas.login');
    Route::get('/toc/berkas/formulir', [TocBerkasController::class, 'formulir'])
        ->name('toc.berkas.formulir');
    Route::post('/toc/berkas/unggah/{jenis}', [TocBerkasController::class, 'unggah'])
        ->whereIn('jenis', ['foto', 'pakta_integritas', 'surat_keterangan'])
        ->middleware('throttle:20,1')
        ->name('toc.berkas.unggah');
    Route::get('/toc/berkas/template/{nama}', [TocBerkasController::class, 'template'])
        ->name('toc.berkas.template');
    Route::post('/toc/berkas/keluar', [TocBerkasController::class, 'logout'])
        ->name('toc.berkas.logout');
});

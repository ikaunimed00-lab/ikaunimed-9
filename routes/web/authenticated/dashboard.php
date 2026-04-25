<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/dashboard', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('home');
    }

    if ($user->hasAnyRole(['super_admin', 'admin', 'editor', 'writer'])) {
        if (request()->header('X-Inertia')) {
            return Inertia::location('/admin');
        }

        return redirect('/admin');
    }

    $requiredFields = ['wa', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat_lengkap',
        's1_fakultas', 's1_prodi', 's1_tahun_masuk', 's1_tahun_tamat'];

    foreach ($requiredFields as $field) {
        if (empty($user->{$field})) {
            return redirect()->route('profile.edit');
        }
    }

    return redirect()->route('dashboard.subscriber');
})->name('dashboard');

Route::get('/dashboard/admin', fn () => redirect('/admin'));
Route::get('/dashboard/admin/{any}', fn () => redirect('/admin'))->where('any', '.*');

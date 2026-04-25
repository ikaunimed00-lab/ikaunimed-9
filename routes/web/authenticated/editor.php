<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/editor')
    ->middleware('role:editor,admin')
    ->name('dashboard.editor.')
    ->group(function () {
        Route::get('/alumni-posts/moderation', fn () => redirect()->route('filament.admin.resources.alumni-posts.index'))
            ->name('alumni-posts.moderation');
        Route::get('/alumni-posts/moderation/{alumniPost}', fn () => redirect()->route('filament.admin.resources.alumni-posts.index'))
            ->name('alumni-posts.moderation.show');
        Route::post('/alumni-posts/{alumniPost}/approve', fn () => redirect()->route('filament.admin.resources.alumni-posts.index'))
            ->name('alumni-posts.approve');
        Route::post('/alumni-posts/{alumniPost}/reject', fn () => redirect()->route('filament.admin.resources.alumni-posts.index'))
            ->name('alumni-posts.reject');
        Route::delete('/alumni-posts/{alumniPost}/force', fn () => redirect()->route('filament.admin.resources.alumni-posts.index'))
            ->middleware('role:admin')
            ->name('alumni-posts.force-delete');
    });

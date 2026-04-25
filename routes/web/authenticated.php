<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/authenticated/dashboard.php';

    Route::middleware([HandleInertiaRequests::class])->group(function () {
        require __DIR__ . '/authenticated/session.php';
        require __DIR__ . '/authenticated/subscriber.php';
        require __DIR__ . '/authenticated/notifications.php';
        require __DIR__ . '/authenticated/legalization.php';
        require __DIR__ . '/authenticated/admin.php';
        require __DIR__ . '/authenticated/courses.php';
        require __DIR__ . '/authenticated/elearning.php';
        require __DIR__ . '/authenticated/alumni-posts.php';
        require __DIR__ . '/authenticated/applications.php';
        require __DIR__ . '/authenticated/editor.php';
        require __DIR__ . '/authenticated/content-management.php';
    });
});

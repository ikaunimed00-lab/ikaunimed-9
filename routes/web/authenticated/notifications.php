<?php

use App\Http\Controllers\Dashboard\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread_count');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark_as_read');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark_all_as_read');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});

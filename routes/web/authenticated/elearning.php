<?php

use App\Http\Controllers\Dashboard\CourseDashboardController;
use App\Http\Controllers\Dashboard\LearnerCourseDashboardController;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/elearning')
    ->name('dashboard.elearning.')
    ->group(function () {
        Route::get('learner/courses', [LearnerCourseDashboardController::class, 'index'])
            ->middleware(EnsureProfileCompleted::class)
            ->name('learner.courses.index');
        Route::get('learner/certificates', [LearnerCourseDashboardController::class, 'certificates'])
            ->middleware(EnsureProfileCompleted::class)
            ->name('learner.certificates.index');
        Route::get('learner/certificates/{certificate}/download', [LearnerCourseDashboardController::class, 'downloadCertificate'])
            ->middleware(EnsureProfileCompleted::class)
            ->name('learner.certificates.download');
        Route::post('learner/enrollments/{enrollment}/cancel', [LearnerCourseDashboardController::class, 'cancel'])
            ->middleware(EnsureProfileCompleted::class)
            ->name('enrollments.cancel');
        Route::get('moderator/courses', [CourseDashboardController::class, 'moderator'])
            ->name('moderator.courses.index');
    });

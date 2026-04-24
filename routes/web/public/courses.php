<?php

use App\Http\Controllers\CourseController;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/courses/{course:slug}/enroll', [CourseController::class, 'enroll'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class, 'throttle:10,1'])
    ->name('courses.enroll');
Route::get('/courses/{course:slug}/lessons/{lesson}', [CourseController::class, 'showLesson'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class])
    ->name('courses.lessons.show');
Route::post('/courses/{course:slug}/lessons/{lesson}/quiz-attempts', [CourseController::class, 'attemptQuiz'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class, 'throttle:30,1'])
    ->name('courses.lessons.quiz.attempt');
Route::post('/courses/{course:slug}/lessons/{lesson}/complete', [CourseController::class, 'completeLesson'])
    ->middleware(['auth', 'role:subscriber', EnsureProfileCompleted::class])
    ->name('courses.lessons.complete');

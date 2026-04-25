<?php

use App\Http\Controllers\Dashboard\OrganizationController as DashboardOrganizationController;
use App\Http\Controllers\Dashboard\OrganizationMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/admin')
    ->middleware('role:admin')
    ->name('dashboard.admin.')
    ->group(function () {
        Route::resource('organizations', DashboardOrganizationController::class);
        Route::resource('organizations.members', OrganizationMemberController::class)->shallow();
        Route::post('organizations/{organization}/members/reorder', [OrganizationMemberController::class, 'reorder'])->name('organizations.members.reorder');
    });

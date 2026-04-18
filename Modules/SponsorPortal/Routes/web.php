<?php

use Illuminate\Support\Facades\Route;
use Modules\SponsorPortal\Http\Controllers\Admin\SponsorsAdminController;
use Modules\SponsorPortal\Http\Controllers\Sponsor\AuthController;
use Modules\SponsorPortal\Http\Controllers\Sponsor\DashboardController;
use Modules\SponsorPortal\Http\Middleware\SponsorAuthenticate;

// Admin routes — manage sponsors and projects
Route::group(['middleware' => ['auth', 'XSS', 'revalidate'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::resource('sponsors', SponsorsAdminController::class)->names([
        'index'   => 'sponsors.index',
        'create'  => 'sponsors.create',
        'store'   => 'sponsors.store',
        'show'    => 'sponsors.show',
        'edit'    => 'sponsors.edit',
        'update'  => 'sponsors.update',
        'destroy' => 'sponsors.destroy',
    ]);
    Route::get('sponsors/{id}/assign-project', [SponsorsAdminController::class, 'assignProject'])->name('sponsors.assign-project');
    Route::post('sponsors/{id}/projects', [SponsorsAdminController::class, 'storeProject'])->name('sponsors.store-project');
    Route::get('sponsors/{sponsor}/projects/{project}/farmers', [SponsorsAdminController::class, 'manageFarmers'])->name('sponsors.manage-farmers');
    Route::put('sponsors/{sponsor}/projects/{project}/farmers', [SponsorsAdminController::class, 'syncFarmers'])->name('sponsors.sync-farmers');
});

// Sponsor portal — separate auth guard
Route::prefix('sponsor')->name('sponsor.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(SponsorAuthenticate::class)->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('projects/{id}', [DashboardController::class, 'project'])->name('project');
        Route::get('projects/{id}/report', [DashboardController::class, 'downloadReport'])->name('report');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Logistics\Http\Controllers\LogisticsController;
use Modules\Logistics\Http\Controllers\RidersController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::get('logistics/export', [LogisticsController::class, 'export'])->name('logistics.export');
    Route::post('logistics/{id}/complete', [LogisticsController::class, 'complete'])->name('logistics.complete');
    Route::resource('logistics', LogisticsController::class);
    Route::resource('riders', RidersController::class)->except(['show']);
});

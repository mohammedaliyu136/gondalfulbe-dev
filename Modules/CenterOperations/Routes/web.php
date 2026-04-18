<?php

use Illuminate\Support\Facades\Route;
use Modules\CenterOperations\Http\Controllers\CenterOperationsController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::get('center-costs/export', [CenterOperationsController::class, 'export'])->name('center-costs.export');
    Route::post('center-costs/{id}/submit', [CenterOperationsController::class, 'submit'])->name('center-costs.submit');
    Route::post('center-costs/{id}/approve', [CenterOperationsController::class, 'approve'])->name('center-costs.approve');
    Route::post('center-costs/{id}/reject', [CenterOperationsController::class, 'reject'])->name('center-costs.reject');
    Route::post('center-costs/{id}/paid', [CenterOperationsController::class, 'markPaid'])->name('center-costs.paid');
    Route::resource('center-costs', CenterOperationsController::class);
});

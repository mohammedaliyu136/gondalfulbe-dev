<?php

use Illuminate\Support\Facades\Route;
use Modules\Requisitions\Http\Controllers\RequisitionsController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::get('requisitions/export', [RequisitionsController::class, 'export'])->name('requisitions.export');
    Route::post('requisitions/{id}/approve', [RequisitionsController::class, 'approve'])->name('requisitions.approve');
    Route::post('requisitions/{id}/reject', [RequisitionsController::class, 'reject'])->name('requisitions.reject');
    Route::post('requisitions/{id}/paid', [RequisitionsController::class, 'markPaid'])->name('requisitions.paid');
    Route::get('requisitions/{id}/confirm', [RequisitionsController::class, 'showConfirm'])->name('requisitions.confirm.show');
    Route::post('requisitions/{id}/complete', [RequisitionsController::class, 'confirmReceipt'])->name('requisitions.complete');
    Route::resource('requisitions', RequisitionsController::class);
});

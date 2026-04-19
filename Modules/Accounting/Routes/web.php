<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\AccountingDashboardController;
use Modules\Accounting\Http\Controllers\BudgetController;
use Modules\Accounting\Http\Controllers\ExpenseClaimController;
use Modules\Accounting\Http\Controllers\ReconciliationController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate'], 'prefix' => 'accounting', 'as' => 'accounting.'], function () {

    // ── Finance Dashboard ────────────────────────────────────────────────────
    Route::get('/', [AccountingDashboardController::class, 'index'])->name('dashboard');

    // ── Budget ───────────────────────────────────────────────────────────────
    Route::post('budget/{id}/activate', [BudgetController::class, 'activate'])->name('budget.activate');
    Route::resource('budget', BudgetController::class)->names('budget');

    // ── Bank Reconciliation ──────────────────────────────────────────────────
    Route::post('reconciliation/{recId}/items/{itemId}/match',   [ReconciliationController::class, 'matchItem'])->name('reconciliation.match');
    Route::post('reconciliation/{recId}/items/{itemId}/unmatch', [ReconciliationController::class, 'unmatchItem'])->name('reconciliation.unmatch');
    Route::post('reconciliation/{id}/finalize',                  [ReconciliationController::class, 'finalize'])->name('reconciliation.finalize');
    Route::resource('reconciliation', ReconciliationController::class)->names('reconciliation');

    // ── Expense Claims ───────────────────────────────────────────────────────
    Route::post('expense-claims/{id}/submit',  [ExpenseClaimController::class, 'submit'])->name('expense-claims.submit');
    Route::post('expense-claims/{id}/approve', [ExpenseClaimController::class, 'approve'])->name('expense-claims.approve');
    Route::post('expense-claims/{id}/reject',  [ExpenseClaimController::class, 'reject'])->name('expense-claims.reject');
    Route::post('expense-claims/{id}/pay',     [ExpenseClaimController::class, 'pay'])->name('expense-claims.pay');
    Route::resource('expense-claims', ExpenseClaimController::class)->names('expense-claims');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportsController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::get('reports/dashboard', [ReportsController::class, 'executiveDashboard'])->name('reports.dashboard');
    Route::get('reports/milk-collection', [ReportsController::class, 'milkCollectionReport'])->name('reports.milk');
    Route::get('reports/logistics', [ReportsController::class, 'logisticsReport'])->name('reports.logistics');
    Route::get('reports/center-operations', [ReportsController::class, 'centerOperationsReport'])->name('reports.centers');
    Route::get('reports/requisitions', [ReportsController::class, 'requisitionsReport'])->name('reports.requisitions');
    Route::get('reports/extension', [ReportsController::class, 'extensionReport'])->name('reports.extension');
    Route::get('reports/inventory', [ReportsController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('reports/agent-distribution', [ReportsController::class, 'agentDistributionReport'])->name('reports.agent');
    Route::get('reports/weekly/{id}/download', [ReportsController::class, 'downloadWeeklyReport'])->name('reports.weekly.download');
});

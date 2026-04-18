<?php

use Illuminate\Support\Facades\Route;
use Modules\OSS\Http\Controllers\OssProductsController;
use Modules\OSS\Http\Controllers\OssInventoryController;
use Modules\OSS\Http\Controllers\OssSalesController;
use Modules\OSS\Http\Controllers\OssAgentDistributionController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::resource('oss-products', OssProductsController::class);

    Route::get('oss-inventory', [OssInventoryController::class, 'index'])->name('oss-inventory.index');
    Route::get('oss-inventory/stock-in', [OssInventoryController::class, 'stockInForm'])->name('oss-inventory.stock-in');
    Route::post('oss-inventory/stock-in', [OssInventoryController::class, 'stockIn'])->name('oss-inventory.store-in');
    Route::get('oss-inventory/stock-out', [OssInventoryController::class, 'stockOutForm'])->name('oss-inventory.stock-out');
    Route::post('oss-inventory/stock-out', [OssInventoryController::class, 'stockOut'])->name('oss-inventory.store-out');

    Route::resource('oss-sales', OssSalesController::class)->except(['edit', 'update', 'destroy']);

    Route::get('oss-agent/distribution', [OssAgentDistributionController::class, 'index'])->name('oss.agent.index');
    Route::post('oss-agent/allocate', [OssAgentDistributionController::class, 'allocate'])->name('oss.agent.allocate');
    Route::get('oss-agent/sale', [OssAgentDistributionController::class, 'saleForm'])->name('oss.agent.sale');
    Route::post('oss-agent/sale', [OssAgentDistributionController::class, 'recordSale'])->name('oss.agent.record-sale');
    Route::post('oss-agent/return', [OssAgentDistributionController::class, 'recordReturn'])->name('oss.agent.return');
    Route::get('oss-agent/{agentId}/balance', [OssAgentDistributionController::class, 'agentBalance'])->name('oss.agent.balance');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\MilkCollection\Http\Controllers\MilkCollectionController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::get('milk-collections/daily-summary', [MilkCollectionController::class, 'dailySummary'])->name('milk-collections.daily-summary');
    Route::get('milk-collections/export', [MilkCollectionController::class, 'export'])->name('milk-collections.export');
    Route::resource('milk-collections', MilkCollectionController::class);
});

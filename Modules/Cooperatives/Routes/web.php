<?php

use Illuminate\Support\Facades\Route;
use Modules\Cooperatives\Http\Controllers\CooperativesController;

/*
|--------------------------------------------------------------------------
| Cooperatives Web Routes
| Middleware: auth (authenticated), XSS (sanitise inputs), revalidate (no cache)
| Authorization is enforced inside each controller method.
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {

    // Export must be registered before the resource so it is not captured
    // by the {cooperative} wildcard.
    Route::get('cooperatives/export',        [CooperativesController::class, 'export'])
         ->name('cooperatives.export');

    Route::get('cooperatives/import',        [CooperativesController::class, 'importForm'])
         ->name('cooperatives.import.form');

    Route::post('cooperatives/import',       [CooperativesController::class, 'importProcess'])
         ->name('cooperatives.import');

    Route::get('cooperatives/{id}/export-farmers', [CooperativesController::class, 'exportFarmers'])
         ->name('cooperatives.farmers.export');

    Route::resource('cooperatives', CooperativesController::class);
});

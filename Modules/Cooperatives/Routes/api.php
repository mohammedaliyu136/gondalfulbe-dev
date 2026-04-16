<?php

use Illuminate\Support\Facades\Route;
use Modules\Cooperatives\Http\Controllers\Api\CooperativesController;

/*
|--------------------------------------------------------------------------
| Cooperatives API Routes
| Prefix  : /api/cooperatives  (applied by RouteServiceProvider)
| Guard   : Sanctum token authentication
| Returns : JSON only – handled by Api\CooperativesController
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cooperatives', CooperativesController::class)
         ->names([
             'index'   => 'api.cooperatives.index',
             'store'   => 'api.cooperatives.store',
             'show'    => 'api.cooperatives.show',
             'update'  => 'api.cooperatives.update',
             'destroy' => 'api.cooperatives.destroy',
         ]);
});

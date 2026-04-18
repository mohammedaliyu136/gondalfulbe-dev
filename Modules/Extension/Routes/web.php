<?php

use Illuminate\Support\Facades\Route;
use Modules\Extension\Http\Controllers\ExtensionAgentsController;
use Modules\Extension\Http\Controllers\FieldVisitsController;
use Modules\Extension\Http\Controllers\TrainingEventsController;

Route::group(['middleware' => ['auth', 'XSS', 'revalidate']], function () {
    Route::resource('extension-agents', ExtensionAgentsController::class);
    Route::resource('field-visits', FieldVisitsController::class);
    Route::resource('training-events', TrainingEventsController::class);
});

<?php

use App\Http\Controllers\Taller\TireMeasureController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('tiremeasure', [TireMeasureController::class, 'list']);
    Route::get('tiremeasure/{id}', [TireMeasureController::class, 'show']);
    Route::post('tiremeasure', [TireMeasureController::class, 'store']);
    Route::put('tiremeasure/{id}', [TireMeasureController::class, 'update']);
    Route::delete('tiremeasure/{id}', [TireMeasureController::class, 'destroy']);
});
<?php

use App\Http\Controllers\TarifarioController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('tarifario', [TarifarioController::class, 'index']);
    Route::post('tarifario', [TarifarioController::class, 'store']);
    Route::get('tarifario/{id}', [TarifarioController::class, 'show']);
    Route::put('tarifario/{id}', [TarifarioController::class, 'update']);
    Route::delete('tarifario/{id}', [TarifarioController::class, 'destroy']);
});

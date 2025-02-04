<?php

use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('unity', [UnityController::class, 'index']);
    Route::post('unity', [UnityController::class, 'store']);
    Route::get('unity/{id}', [UnityController::class, 'show']);
    Route::put('unity/{id}', [UnityController::class, 'update']);
    Route::delete('unity/{id}', [UnityController::class, 'destroy']);
});

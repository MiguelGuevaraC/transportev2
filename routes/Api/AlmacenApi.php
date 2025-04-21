<?php


use App\Http\Controllers\Api\AlmacenController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('almacen', [AlmacenController::class, 'index']);
    Route::get('almacen/{id}', [AlmacenController::class, 'show']);
    Route::post('almacen', [AlmacenController::class, 'store']);
    Route::delete('almacen/{id}', [AlmacenController::class, 'destroy']);
    Route::put('almacen/{id}', [AlmacenController::class, 'update']);
});

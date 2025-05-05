<?php


use App\Http\Controllers\Taller\RepuestoController;
use App\Http\Controllers\Taller\ServiceController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('service', [ServiceController::class, 'index']);
    Route::get('service/{id}', [ServiceController::class, 'show']);
    Route::post('service', [ServiceController::class, 'store']);
    Route::delete('service/{id}', [ServiceController::class, 'destroy']);
    Route::put('service/{id}', [ServiceController::class, 'update']);
});

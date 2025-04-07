<?php


use App\Http\Controllers\Taller\RepuestoController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('repuesto', [RepuestoController::class, 'list']);
    Route::get('repuesto/{id}', [RepuestoController::class, 'show']);
    Route::post('repuesto', [RepuestoController::class, 'store']);
    Route::delete('repuesto/{id}', [RepuestoController::class, 'destroy']);
    Route::put('repuesto/{id}', [RepuestoController::class, 'update']);
});

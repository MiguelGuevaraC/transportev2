<?php


use App\Http\Controllers\Taller\TireController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('tire', [TireController::class, 'list']);
    Route::get('tire/{id}', [TireController::class, 'show']);
    Route::post('tire', [TireController::class, 'store']);
    Route::delete('tire/{id}', [TireController::class, 'destroy']);
    Route::put('tire/{id}', [TireController::class, 'update']);
});

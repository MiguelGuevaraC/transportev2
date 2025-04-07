<?php

use App\Http\Controllers\Taller\TallerController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('taller', [TallerController::class, 'list']);
    Route::get('taller/{id}', [TallerController::class, 'show']);
    Route::post('taller', [TallerController::class, 'store']);
    Route::delete('taller/{id}', [TallerController::class, 'destroy']);
    Route::put('taller/{id}', [TallerController::class, 'update']);
});

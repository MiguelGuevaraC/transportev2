<?php

use App\Http\Controllers\Api\SeccionController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('seccion', [SeccionController::class, 'index']);
    Route::get('seccion/{id}', [SeccionController::class, 'show']);
    Route::post('seccion', [SeccionController::class, 'store']);
    Route::delete('seccion/{id}', [SeccionController::class, 'destroy']);
    Route::put('seccion/{id}', [SeccionController::class, 'update']);

    Route::get('report-seccion/{id}', [SeccionController::class, 'report']);
});

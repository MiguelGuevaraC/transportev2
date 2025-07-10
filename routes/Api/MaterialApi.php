<?php

use App\Http\Controllers\Taller\MaterialController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('material', [MaterialController::class, 'list']);
    Route::get('material/{id}', [MaterialController::class, 'show']);
    Route::post('material', [MaterialController::class, 'store']);
    Route::put('material/{id}', [MaterialController::class, 'update']);
    Route::delete('material/{id}', [MaterialController::class, 'destroy']);
});
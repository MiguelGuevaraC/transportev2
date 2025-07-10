<?php

use App\Http\Controllers\taller\BrandController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('brand', [BrandController::class, 'list']);
    Route::get('brand/{id}', [BrandController::class, 'show']);
    Route::post('brand', [BrandController::class, 'store']);
    Route::put('brand/{id}', [BrandController::class, 'update']);
    Route::delete('brand/{id}', [BrandController::class, 'destroy']);
});
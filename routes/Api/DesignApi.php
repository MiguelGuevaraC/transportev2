<?php

use App\Http\Controllers\taller\DesignController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('design', [DesignController::class, 'list']);
    Route::get('design/{id}', [DesignController::class, 'show']);
    Route::post('design', [DesignController::class, 'store']);
    Route::put('design/{id}', [DesignController::class, 'update']);
    Route::delete('design/{id}', [DesignController::class, 'destroy']);
});
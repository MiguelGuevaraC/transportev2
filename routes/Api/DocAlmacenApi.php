<?php

use App\Http\Controllers\Taller\DocAlmacenController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('docalmacen', [DocAlmacenController::class, 'list']);
    Route::get('docalmacen/{id}', [DocAlmacenController::class, 'show']);
    Route::post('docalmacen', [DocAlmacenController::class, 'store']);
    Route::put('docalmacen/{id}', [DocAlmacenController::class, 'update']);
    Route::delete('docalmacen/{id}', [DocAlmacenController::class, 'destroy']);
});
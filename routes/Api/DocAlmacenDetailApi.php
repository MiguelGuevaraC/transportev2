<?php

use App\Http\Controllers\Taller\DocAlmacenDetailController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('docalmacendetail', [DocAlmacenDetailController::class, 'list']);
    Route::get('docalmacendetail/{id}', [DocAlmacenDetailController::class, 'show']);
    Route::post('docalmacendetail', [DocAlmacenDetailController::class, 'store']);
    Route::put('docalmacendetail/{id}', [DocAlmacenDetailController::class, 'update']);
    Route::delete('docalmacendetail/{id}', [DocAlmacenDetailController::class, 'destroy']);
});
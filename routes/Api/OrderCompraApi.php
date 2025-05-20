<?php

use App\Http\Controllers\Compra\CompraOrderController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('ordercompra', [CompraOrderController::class, 'index']);
    Route::get('ordercompra/{id}', [CompraOrderController::class, 'show']);
    Route::post('ordercompra', [CompraOrderController::class, 'store']);
    Route::put('ordercompra/{id}', [CompraOrderController::class, 'update']);
    Route::delete('ordercompra/{id}', [CompraOrderController::class, 'destroy']);


});

<?php

use App\Http\Controllers\Compra\CompraOrderController;
use App\Http\Controllers\Compra\CompraOrderDetailController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('ordercompradetail', [CompraOrderDetailController::class, 'index']);
    Route::get('ordercompradetail/{id}', [CompraOrderDetailController::class, 'show']);
    Route::post('ordercompradetail', [CompraOrderDetailController::class, 'store']);
    Route::put('ordercompradetail/{id}', [CompraOrderDetailController::class, 'update']);
    Route::delete('ordercompradetail/{id}', [CompraOrderDetailController::class, 'destroy']);
});

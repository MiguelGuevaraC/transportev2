<?php

use App\Http\Controllers\Compra\CompraOrderController;
use App\Http\Controllers\Compra\CompraOrderDetailController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('movimentcompradetail', [CompraOrderDetailController::class, 'index']);
    Route::get('movimentcompradetail/{id}', [CompraOrderDetailController::class, 'show']);
    Route::post('movimentcompradetail', [CompraOrderDetailController::class, 'store']);
    Route::put('movimentcompradetail/{id}', [CompraOrderDetailController::class, 'update']);
    Route::delete('movimentcompradetail/{id}', [CompraOrderDetailController::class, 'destroy']);
});

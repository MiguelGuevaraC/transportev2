<?php

use App\Http\Controllers\Compra\CompraMovimentController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('movimentcompra', [CompraMovimentController::class, 'index']);
    Route::get('movimentcompra/{id}', [CompraMovimentController::class, 'show']);
    Route::post('movimentcompra', [CompraMovimentController::class, 'store']);
    Route::put('movimentcompra/{id}', [CompraMovimentController::class, 'update']);
    Route::delete('movimentcompra/{id}', [CompraMovimentController::class, 'destroy']);
});

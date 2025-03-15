<?php


use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\DriverExpenseController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post('transferir_saldo', [DriverExpenseController::class, 'transferir_saldo']);
});

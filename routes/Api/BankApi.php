<?php


use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('bank', [BankController::class, 'index']);
    Route::get('bank-list', [BankController::class, 'list']);
    Route::get('bank/{id}', [BankController::class, 'show']);
    Route::post('bank', [BankController::class, 'store']);
    Route::delete('bank/{id}', [BankController::class, 'destroy']);
    Route::put('bank/{id}', [BankController::class, 'update']);
});

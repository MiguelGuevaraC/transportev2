<?php

use App\Http\Controllers\LargeCashBox\BankAccountController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('bank-account', [BankAccountController::class, 'index']);
    Route::get('bank-account/{id}', [BankAccountController::class, 'show']);
    Route::post('bank-account', [BankAccountController::class, 'store']);
    Route::delete('bank-account/{id}', [BankAccountController::class, 'destroy']);
    Route::put('bank-account/{id}', [BankAccountController::class, 'update']);
});

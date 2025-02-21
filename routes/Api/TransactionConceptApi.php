<?php


use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\LargeCashBox\TransactionConceptsController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK

    Route::get('transaction-concept', [TransactionConceptsController::class, 'index']);
    Route::get('transaction-concept/{id}', [TransactionConceptsController::class, 'show']);
    Route::post('transaction-concept', [TransactionConceptsController::class, 'store']);
    Route::delete('transaction-concept/{id}', [TransactionConceptsController::class, 'destroy']);
    Route::put('transaction-concept/{id}', [TransactionConceptsController::class, 'update']);
});

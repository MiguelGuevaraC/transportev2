<?php


use App\Http\Controllers\LargeCashBox\BankMovementController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('bank-movement', [BankMovementController::class, 'index']);
    Route::get('bank-movement-export-excel', [BankMovementController::class, 'index_export_excel']);
    Route::get('bank-movement/{id}', [BankMovementController::class, 'show']);
    Route::post('bank-movement', [BankMovementController::class, 'store']);
    Route::delete('bank-movement/{id}', [BankMovementController::class, 'destroy']);
    Route::put('bank-movement/{id}', [BankMovementController::class, 'update']);
});

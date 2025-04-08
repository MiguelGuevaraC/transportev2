<?php


use App\Http\Controllers\Taller\CheckListItemController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('checklistitem', [CheckListItemController::class, 'list']);
    Route::get('checklistitem/{id}', [CheckListItemController::class, 'show']);
    Route::post('checklistitem', [CheckListItemController::class, 'store']);
    Route::delete('checklistitem/{id}', [CheckListItemController::class, 'destroy']);
    Route::put('checklistitem/{id}', [CheckListItemController::class, 'update']);
});

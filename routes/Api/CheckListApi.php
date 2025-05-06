<?php

use App\Http\Controllers\Taller\CheckListController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('checklist', [CheckListController::class, 'list']);
    Route::get('checklist/{id}', [CheckListController::class, 'show']);
    Route::post('checklist', [CheckListController::class, 'store']);
    Route::delete('checklist/{id}', [CheckListController::class, 'destroy']);
    Route::put('checklist/{id}', [CheckListController::class, 'update']);
    Route::get('report-checklist/{id}', [CheckListController::class, 'report'])->name('report');
});

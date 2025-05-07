<?php


use App\Http\Controllers\Api\AlmacenController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('report_history_programming_by_worker/{id}', [WorkerController::class, 'report_history_programming_by_worker']);


});

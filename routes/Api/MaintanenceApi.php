<?php


use App\Http\Controllers\Taller\MaintananceController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('maintanence', [MaintananceController::class, 'index']);
    Route::post('maintanence', [MaintananceController::class, 'store']);
    Route::get('maintanence/{id}', [MaintananceController::class, 'show']);
    Route::put('maintanence/{id}', [MaintananceController::class, 'update']);
    Route::delete('maintanence/{id}', [MaintananceController::class, 'destroy']);
});

<?php

use App\Http\Controllers\Taller\MaintananceDetailController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('maintanencedetail', [MaintananceDetailController::class, 'index']);
    Route::post('maintanencedetail', [MaintananceDetailController::class, 'store']);
    Route::get('maintanencedetail/{id}', [MaintananceDetailController::class, 'show']);
    Route::put('maintanencedetail/{id}', [MaintananceDetailController::class, 'update']);
    Route::delete('maintanencedetail/{id}', [MaintananceDetailController::class, 'destroy']);
});

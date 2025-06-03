<?php

use App\Http\Controllers\Api\CarrierGuideController;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post('validatetoken', [EmailController::class, 'validatemail']);
    Route::post('desvinculatesale', [EmailController::class, 'desvincularGuideSale']);

    Route::patch('carrierGuide/{id}/update_path', [CarrierGuideController::class, 'update_path']);

});

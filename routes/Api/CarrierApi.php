<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContestantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post('validatetoken', [EmailController::class, 'validatemail']);
    Route::post('desvinculatesale', [EmailController::class, 'desvincularGuideSale']);

});

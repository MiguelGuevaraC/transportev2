<?php

use App\Http\Controllers\Api\MovimentController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContestantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::delete('moviment-ticket/{id}', [MovimentController::class, 'destroy_venta_ticket']);

});

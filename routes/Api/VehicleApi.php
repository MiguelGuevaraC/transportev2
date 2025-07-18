<?php

use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Taller\BrandController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('vehicle/{id}/tires', [VehicleController::class, 'tires']);
});
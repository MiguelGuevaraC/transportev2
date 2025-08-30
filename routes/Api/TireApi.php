<?php


use App\Http\Controllers\Taller\TireController;
use App\Http\Controllers\Taller\TireOperationController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

  // TIRE
  Route::get('tire', [TireController::class, 'list']);
  Route::get('generatecodes', [TireController::class, 'generatecodes']);

  Route::get('tire/{id}', [TireController::class, 'show']);
  Route::post('tire', [TireController::class, 'store']);
  Route::delete('tire/{id}', [TireController::class, 'destroy']);
  Route::put('tire/{id}', [TireController::class, 'update']);

  // TIRE OPERATION
  Route::get('tire_operation', [TireOperationController::class, 'list']);
  Route::get('tire_operation/{id}', [TireOperationController::class, 'show']);
  Route::post('tire_operation', [TireOperationController::class, 'store']);
  Route::delete('tire_operation/{id}', [TireOperationController::class, 'destroy']);
  Route::put('tire_operation/{id}', [TireOperationController::class, 'update']);
});

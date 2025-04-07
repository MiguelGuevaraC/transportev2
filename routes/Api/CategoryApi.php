<?php


use App\Http\Controllers\Taller\CategoryController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('category', [CategoryController::class, 'list']);
    Route::get('category/{id}', [CategoryController::class, 'show']);
    Route::post('category', [CategoryController::class, 'store']);
    Route::delete('category/{id}', [CategoryController::class, 'destroy']);
    Route::put('category/{id}', [CategoryController::class, 'update']);
});

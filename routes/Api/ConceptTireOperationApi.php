<?php

use App\Http\Controllers\Taller\ConceptTireOperationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('concepttireoperation', [ConceptTireOperationController::class, 'list']);
    Route::get('concepttireoperation/{id}', [ConceptTireOperationController::class, 'show']);
    Route::post('concepttireoperation', [ConceptTireOperationController::class, 'store']);
    Route::put('concepttireoperation/{id}', [ConceptTireOperationController::class, 'update']);
    Route::delete('concepttireoperation/{id}', [ConceptTireOperationController::class, 'destroy']);
});
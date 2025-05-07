<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\CargarDocumentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContestantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('export/kardex', [CargarDocumentController::class, 'exportKardex']);

    Route::get('history-cargadocument', [CargarDocumentController::class, 'index_history']);
    Route::get('history-cargadocument-excel', [CargarDocumentController::class, 'index_export_excel']);

    Route::get('cargadocument', [CargarDocumentController::class, 'index']);
    Route::post('cargadocument', [CargarDocumentController::class, 'store']);
    Route::get('cargadocument/{id}', [CargarDocumentController::class, 'show']);
    // Route::put('cargadocument/{id}', [CargarDocumentController::class, 'update']);
    Route::delete('cargadocument/{id}', [CargarDocumentController::class, 'destroy']);

    Route::get('ticketcarga/{id}', [CargarDocumentController::class, 'ticketcarga'])->name('ticketcarga');

});

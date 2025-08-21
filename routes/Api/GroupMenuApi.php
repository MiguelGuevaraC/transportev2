<?php


use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\GroupMenuController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\UnityController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    // EXPENSES BANK
    Route::get('groupmenu-list', [GroupMenuController::class, 'list']);
    Route::get('groupmenu/{id}', [GroupMenuController::class, 'show']);
    Route::post('groupmenu', [GroupMenuController::class, 'store']);
    Route::delete('groupmenu/{id}', [GroupMenuController::class, 'destroy']);
    Route::put('groupmenu/{id}', [GroupMenuController::class, 'update']);
});

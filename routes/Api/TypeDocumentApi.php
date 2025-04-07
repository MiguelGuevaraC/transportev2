<?php

use App\Http\Controllers\AccountPayable\TypeDocumentController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('typedocument', [TypeDocumentController::class, 'index']);

});

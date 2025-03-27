<?php

use App\Http\Controllers\Api\ProgrammingController;
use App\Models\Programming;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('list-programming', [ProgrammingController::class, 'list']);

});

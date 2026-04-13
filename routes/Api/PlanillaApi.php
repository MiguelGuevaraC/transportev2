<?php

use App\Http\Controllers\Api\PlanillaController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post('planilla/asistencia', [PlanillaController::class, 'storeAttendance']);
    Route::post('planilla/falta', [PlanillaController::class, 'storeAbsence']);
    Route::get('planilla/calendario', [PlanillaController::class, 'calendar']);
});

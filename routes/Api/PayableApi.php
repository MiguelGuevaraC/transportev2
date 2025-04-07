<?php

use App\Http\Controllers\AccountPayable\PayableController;
use App\Http\Controllers\AccountPayable\TypeDocumentController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('payable', [PayableController::class, 'list']);
    Route::put('payable/{id}/pay', [PayableController::class, 'pay_payable']);

});

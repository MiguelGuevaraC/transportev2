<?php

use App\Http\Controllers\Api\AlmacenController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\SeccionController;
use App\Http\Controllers\CargarDocumentController;
use App\Http\Controllers\Compra\CompraOrderController;
use App\Http\Controllers\Taller\CheckListController;
use App\Http\Controllers\Taller\MaintananceController;
use App\Models\CargaDocument;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});
// Route::get('reportCaja', [PdfController::class, 'reportCaja'])->name('reportCaja');
// Route::get('manifiesto2/{id}', [PdfController::class, 'manifiesto2'])->name('manifiesto');

Route::get('guiamanual', [PdfController::class, 'guiamanual'])->name('guiamanual');
Route::get('guiaPDF/{id}', [PdfController::class, 'guiaPDF'])->name('guiaPDF');
Route::get('creditNote/{id}', [PdfController::class, 'creditNote'])->name('creditNote');
Route::get('documento/{id}', [PdfController::class, 'documento'])->name('documento');
Route::get('documentoA4/{id}', [PdfController::class, 'documentoA4'])->name('documentoA4');
Route::get('documentoA4F2/{id}', [PdfController::class, 'documentoA4F2'])->name('documentoA4F2');

Route::get('pruebaFacturador', [PdfController::class, 'pruebaFacturador'])->name('pruebaFacturador');
Route::get('reporteReception/{id}', [PdfController::class, 'reporteReception'])->name('reporteReception');
Route::get('ticketmov/{id}', [PdfController::class, 'ticketbox'])->name('ticketbox');
Route::get('ticketbackbox/{id}', [PdfController::class, 'ticketbackbox'])->name('ticketbackbox');

// Route::get('ticketrecepcion/{id}', [PdfController::class, 'ticketrecepcion'])->name('ticketrecepcion');
// Route::get('report-seccion/{id}', [SeccionController::class, 'report']);



Route::get('/', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
});
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('login');

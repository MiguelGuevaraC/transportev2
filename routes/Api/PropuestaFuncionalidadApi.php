<?php

use App\Http\Controllers\Api\CompraPartialReceiptGroupController;
use App\Http\Controllers\Api\MaintenanceFormActionController;
use App\Http\Controllers\Api\ProductRequirementController;
use App\Http\Controllers\Api\PurchaseQuotationController;
use App\Http\Controllers\Api\WorkerDriverDocumentController;
use App\Http\Controllers\Api\WorkerStatusHistoryController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('product-requirements', [ProductRequirementController::class, 'index']);
    Route::get('product-requirements/{id}', [ProductRequirementController::class, 'show']);
    Route::post('product-requirements', [ProductRequirementController::class, 'store']);
    Route::put('product-requirements/{id}', [ProductRequirementController::class, 'update']);
    Route::delete('product-requirements/{id}', [ProductRequirementController::class, 'destroy']);
    Route::post('product-requirements/from-checklist', [ProductRequirementController::class, 'fromChecklist']);

    Route::get('purchase-quotations', [PurchaseQuotationController::class, 'index']);
    Route::get('purchase-quotations/{id}', [PurchaseQuotationController::class, 'show']);
    Route::post('purchase-quotations', [PurchaseQuotationController::class, 'store']);
    Route::put('purchase-quotations/{id}', [PurchaseQuotationController::class, 'update']);
    Route::delete('purchase-quotations/{id}', [PurchaseQuotationController::class, 'destroy']);
    Route::post('purchase-quotations/{id}/set-winner', [PurchaseQuotationController::class, 'setWinner']);

    Route::get('compra-partial-receipt-groups', [CompraPartialReceiptGroupController::class, 'index']);
    Route::get('compra-partial-receipt-groups/{id}', [CompraPartialReceiptGroupController::class, 'show']);
    Route::post('compra-partial-receipt-groups', [CompraPartialReceiptGroupController::class, 'store']);
    Route::post('compra-partial-receipt-groups/{id}/invoice', [CompraPartialReceiptGroupController::class, 'attachInvoice']);

    Route::get('maintenance-form-actions', [MaintenanceFormActionController::class, 'index']);
    Route::get('maintenance-form-actions/{id}', [MaintenanceFormActionController::class, 'show']);
    Route::post('maintenance-form-actions', [MaintenanceFormActionController::class, 'store']);
    Route::put('maintenance-form-actions/{id}', [MaintenanceFormActionController::class, 'update']);
    Route::delete('maintenance-form-actions/{id}', [MaintenanceFormActionController::class, 'destroy']);

    Route::get('worker/{workerId}/status-history', [WorkerStatusHistoryController::class, 'index']);
    Route::post('worker/{workerId}/status-history', [WorkerStatusHistoryController::class, 'store']);

    Route::get('worker-driver-documents', [WorkerDriverDocumentController::class, 'index']);
    Route::post('worker-driver-documents', [WorkerDriverDocumentController::class, 'store']);
    Route::put('worker-driver-documents/{id}', [WorkerDriverDocumentController::class, 'update']);
    Route::delete('worker-driver-documents/{id}', [WorkerDriverDocumentController::class, 'destroy']);
});

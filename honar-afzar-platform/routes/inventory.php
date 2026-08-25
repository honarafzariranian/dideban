<?php

use App\Http\Controllers\Api\Inventory\WarehouseController;
use App\Http\Controllers\Api\Inventory\ProductController;
use App\Http\Controllers\Api\Inventory\StockMovementController;
use App\Http\Controllers\Api\Inventory\PurchaseOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'organization'])->prefix('inventory')->name('inventory.')->group(function () {
    
    // Warehouses
    Route::apiResource('warehouses', WarehouseController::class);
    
    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('products/{id}/stock', [ProductController::class, 'stockSummary']);
    
    // Stock Movements
    Route::apiResource('stock-movements', StockMovementController::class);
    Route::post('stock-movements/{id}/approve', [StockMovementController::class, 'approve']);
    Route::post('stock-movements/{id}/cancel', [StockMovementController::class, 'cancel']);
    Route::get('warehouses/{warehouseId}/stock', [StockMovementController::class, 'warehouseStock']);
    Route::get('low-stock', [StockMovementController::class, 'lowStock']);
    
    // Purchase Orders
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
    Route::post('purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);
    Route::get('purchase-orders-stats', [PurchaseOrderController::class, 'stats']);
});

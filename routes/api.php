<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\WarehouseController; 
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\StockTransactionController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/


Route::prefix('v1')->group(function () {
   
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok'
        ]);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        /*Route::get('/user', function (Request $request) {
            return $request->user();
        });*/

       
        // Product routes
        Route::apiResource('products', ProductController::class)->names('products')
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::patch('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');

        
        // Category routes
        Route::apiResource('categories', CategoryController::class)->names('categories')
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::patch(
            '/categories/{id}/restore',
            [CategoryController::class, 'restore']
        )->name('categories.restore');

        // Supplier routes
        Route::apiResource('suppliers', SupplierController::class)->names('suppliers')
            ->only(['index', 'store', 'show', 'update', 'destroy']  );
        Route::patch(
            '/suppliers/{id}/restore',
            [SupplierController::class, 'restore']
        )->name('suppliers.restore');


        // Warehouse routes
        Route::apiResource('warehouses', WarehouseController::class)->names('warehouses')
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::patch(
            '/warehouses/{id}/restore',
            [WarehouseController::class, 'restore']
        )->name('warehouses.restore');

        // Inventory routes 
        Route::get('/inventory', [InventoryController::class, 'index'])
            ->name('inventory.index');
        Route::get('/inventory/summary', [
                    InventoryController::class,
                    'summary',
                ])->name('inventory.summary');
        Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])
            ->name('inventory.show');
       


        // Stock Transaction routes 
        Route::get('/stock-transactions', [
                StockTransactionController::class,
                'index',
            ])->name('stock-transactions.index');
        Route::post('/stock-transactions', [
                StockTransactionController::class,
                'store',
            ])->name('stock-transactions.store');
        Route::get('/stock-transactions/{stockTransaction}', [
                StockTransactionController::class,
                'show',
            ])->name('stock-transactions.show');
        Route::post('/stock-transactions/transfer', [
                StockTransactionController::class,
                'transfer',
            ])->name('stock-transactions.transfer');



    });

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
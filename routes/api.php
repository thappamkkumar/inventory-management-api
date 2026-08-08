<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;

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

       
        Route::apiResource('products', ProductController::class)->names('products')
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::patch('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');

        
        Route::apiResource('categories', CategoryController::class)->names('categories')
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::patch(
            '/categories/{id}/restore',
            [CategoryController::class, 'restore']
        )->name('categories.restore');


        Route::apiResource('suppliers', SupplierController::class)->names('suppliers')
            ->only(['index', 'store', 'show', 'update', 'destroy']  );

        Route::patch(
            '/suppliers/{id}/restore',
            [SupplierController::class, 'restore']
        )->name('suppliers.restore');


    });

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
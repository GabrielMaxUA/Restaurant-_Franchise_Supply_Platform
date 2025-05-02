<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route
Route::get('/test', function () {
  return response()->json(['message' => 'API is working!']);
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // User routes - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Product routes
    Route::get('products', [ProductController::class, 'index']); // All authenticated users
    Route::get('products/{product}', [ProductController::class, 'show']); // All authenticated users
    
    // Product management - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
    });

    // Order routes
    Route::get('orders', [OrderController::class, 'index']); // Filtered by user role in controller
    Route::get('orders/{order}', [OrderController::class, 'show']); // Access checked in controller
    Route::post('orders', [OrderController::class, 'store']); // All authenticated users can create orders
    
    // Order management - Admin only
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    });
    
    // QuickBooks integration - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('orders/{order}/sync-to-quickbooks', [OrderController::class, 'syncToQuickbooks']);
    });
});
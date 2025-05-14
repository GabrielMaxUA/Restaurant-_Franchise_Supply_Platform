<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckUserStatus;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Test routes that don't require authentication
Route::get('/test', function() {
    return response()->json(['message' => 'API is working']);
});

Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'success',
            'message' => "Database is connected: " . DB::connection()->getDatabaseName()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => "Database error: " . $e->getMessage()
        ]);
    }
});

// Debug route to see all defined routes
Route::get('/routes', function () {
    $routeCollection = Route::getRoutes();
    $routes = [];
    
    foreach ($routeCollection as $route) {
        $routes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    }
    
    return response()->json($routes);
});

// Public JWT Auth Routes (no authentication required)
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// JWT Debug route - requires auth
Route::get('/jwt-debug', function () {
    try {
        $user = auth('api')->user();
        if ($user) {
            return response()->json([
                'auth_check' => true,
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
                'status' => $user->status
            ]);
        } else {
            return response()->json([
                'auth_check' => false,
                'message' => 'No authenticated user found'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth:api');

// Protected Auth Routes
Route::group(['prefix' => 'auth', 'middleware' => ['auth:api']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

// Other Protected API routes
Route::group(['middleware' => ['auth:api']], function () {
    // Admin routes
    Route::group(['prefix' => 'admin', 'middleware' => ['role:admin']], function () {
        // Users
        Route::get('users', [App\Http\Controllers\Api\Admin\UserController::class, 'index']);
        Route::post('users', [App\Http\Controllers\Api\Admin\UserController::class, 'store']);
        Route::get('users/{user}', [App\Http\Controllers\Api\Admin\UserController::class, 'show']);
        Route::put('users/{user}', [App\Http\Controllers\Api\Admin\UserController::class, 'update']);
        Route::delete('users/{user}', [App\Http\Controllers\Api\Admin\UserController::class, 'destroy']);
        Route::post('users/{user}/toggle-status', [App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus']);
        
        // Products
        Route::apiResource('products', App\Http\Controllers\Api\Admin\ProductController::class);
        
        // Categories
        Route::apiResource('categories', App\Http\Controllers\Api\Admin\CategoryController::class);
        
        // Orders
        Route::get('orders', [App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
        Route::get('orders/{order}', [App\Http\Controllers\Api\Admin\OrderController::class, 'show']);
        Route::patch('orders/{order}/status', [App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
    });
    
    // Warehouse routes
    Route::group(['prefix' => 'warehouse', 'middleware' => ['role:warehouse']], function () {
        // Products
        Route::get('products', [App\Http\Controllers\Api\Warehouse\ProductController::class, 'index']);
        Route::get('products/{product}', [App\Http\Controllers\Api\Warehouse\ProductController::class, 'show']);
        Route::put('products/{product}', [App\Http\Controllers\Api\Warehouse\ProductController::class, 'update']);
        
        // Inventory
        Route::get('inventory/low-stock', [App\Http\Controllers\Api\Warehouse\ProductController::class, 'lowStock']);
        Route::get('inventory/out-of-stock', [App\Http\Controllers\Api\Warehouse\ProductController::class, 'outOfStock']);
    });
    
    // Franchisee routes
    Route::group(['prefix' => 'franchisee', 'middleware' => ['role:franchisee']], function () {
        // Profile
        Route::get('profile', [App\Http\Controllers\Franchisee\ProfileController::class, 'index']);
        Route::put('profile', [App\Http\Controllers\Franchisee\ProfileController::class, 'update']);
        Route::get('address', [App\Http\Controllers\Franchisee\ProfileController::class, 'getAddress']);
        
        // Catalog
        Route::get('catalog', [App\Http\Controllers\Franchisee\CatalogController::class, 'index']);
        Route::post('toggle-favorite', [App\Http\Controllers\Franchisee\CatalogController::class, 'toggleFavorite']);
        
        // Cart
        Route::get('cart', [App\Http\Controllers\Franchisee\CartController::class, 'index']);
        Route::post('cart/add', [App\Http\Controllers\Franchisee\CartController::class, 'addToCart']);
        Route::post('cart/update', [App\Http\Controllers\Franchisee\CartController::class, 'updateCart']);
        Route::post('cart/remove', [App\Http\Controllers\Franchisee\CartController::class, 'removeFromCart']);
        Route::get('cart/clear', [App\Http\Controllers\Franchisee\CartController::class, 'clearCart']);
        Route::get('cart/checkout', [App\Http\Controllers\Franchisee\CartController::class, 'checkout']);
        Route::post('cart/place-order', [App\Http\Controllers\Franchisee\CartController::class, 'placeOrder']);
        
        // Orders
        Route::get('orders/pending', [App\Http\Controllers\Franchisee\OrderController::class, 'pendingOrders']);
        Route::get('orders/history', [App\Http\Controllers\Franchisee\OrderController::class, 'orderHistory']);
        Route::get('orders/{order}/details', [App\Http\Controllers\Franchisee\OrderController::class, 'orderDetails']);
        Route::get('orders/{order}/repeat', [App\Http\Controllers\Franchisee\OrderController::class, 'repeatOrder']);
        Route::get('orders/{order}/invoice', [App\Http\Controllers\Franchisee\OrderController::class, 'generateInvoice']);
    });
});

// Fallback for undefined API routes
Route::fallback(function(){
    return response()->json([
        'message' => 'API endpoint not found.'
    ], 404);
});
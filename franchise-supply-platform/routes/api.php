<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckUserStatus;

// ✅ Step 1: Enable only login route
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/franchisee/dashboard', [App\Http\Controllers\Franchisee\DashboardController::class, 'apiDashboard'])
    ->middleware(['auth:api', 'role:franchisee']);

// 🔒 Commented: Testing routes
/*
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
*/

// 🔒 Commented: JWT debug and protected routes
/*
Route::get('/jwt-debug', function () {
    ...
})->middleware('auth:api');
*/

Route::group(['prefix' => 'auth', 'middleware' => ['auth:api']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});


// 🔒 Commented: Franchisee routes
/*
Route::group(['middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'franchisee', 'middleware' => ['role:franchisee']], function () {
        Route::get('profile', [App\Http\Controllers\Franchisee\ProfileController::class, 'index']);
        Route::put('profile', [App\Http\Controllers\Franchisee\ProfileController::class, 'update']);
        Route::get('address', [App\Http\Controllers\Franchisee\ProfileController::class, 'getAddress']);
        Route::get('catalog', [App\Http\Controllers\Franchisee\CatalogController::class, 'index']);
        Route::post('toggle-favorite', [App\Http\Controllers\Franchisee\CatalogController::class, 'toggleFavorite']);
        Route::get('cart', [App\Http\Controllers\Franchisee\CartController::class, 'index']);
        Route::post('cart/add', [App\Http\Controllers\Franchisee\CartController::class, 'addToCart']);
        Route::post('cart/update', [App\Http\Controllers\Franchisee\CartController::class, 'updateCart']);
        Route::post('cart/remove', [App\Http\Controllers\Franchisee\CartController::class, 'removeFromCart']);
        Route::get('cart/clear', [App\Http\Controllers\Franchisee\CartController::class, 'clearCart']);
        Route::get('cart/checkout', [App\Http\Controllers\Franchisee\CartController::class, 'checkout']);
        Route::post('cart/place-order', [App\Http\Controllers\Franchisee\CartController::class, 'placeOrder']);
        Route::get('orders/pending', [App\Http\Controllers\Franchisee\OrderController::class, 'pendingOrders']);
        Route::get('orders/history', [App\Http\Controllers\Franchisee\OrderController::class, 'orderHistory']);
        Route::get('orders/{order}/details', [App\Http\Controllers\Franchisee\OrderController::class, 'orderDetails']);
        Route::get('orders/{order}/repeat', [App\Http\Controllers\Franchisee\OrderController::class, 'repeatOrder']);
        Route::get('orders/{order}/invoice', [App\Http\Controllers\Franchisee\OrderController::class, 'generateInvoice']);
    });
});
*/

// Fallback
Route::fallback(function(){
    return response()->json([
        'message' => 'API endpoint not found.'
    ], 404);
});

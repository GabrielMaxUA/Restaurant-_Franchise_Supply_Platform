<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Test endpoint to check if API is accessible
// Route::get('/test', function() {
//     return response()->json([
//         'message' => 'API is working',
//         'timestamp' => now()->toDateTimeString()
//     ]);
// });

// Protected routes with JWT
Route::group(['middleware' => ['auth:api']], function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
    
    // Franchisee routes
    Route::group(['prefix' => 'franchisee', 'middleware' => ['role:franchisee']], function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Franchisee\DashboardController::class, 'apiDashboard']);
        
        // Profile management
        Route::get('/profile', [App\Http\Controllers\Franchisee\ProfileController::class, 'apiProfile']);
        Route::post('/profile/update', [App\Http\Controllers\Franchisee\ProfileController::class, 'apiUpdateProfile']);
        Route::post('/profile/password', [App\Http\Controllers\Franchisee\ProfileController::class, 'apiUpdatePassword']);
        Route::delete('/profile/logo', [App\Http\Controllers\Franchisee\ProfileController::class, 'apiDeleteLogo']);
        
        // Push notification management
        Route::post('/fcm-token', [App\Http\Controllers\Franchisee\ProfileController::class, 'updateFcmToken']);
        Route::delete('/fcm-token', [App\Http\Controllers\Franchisee\ProfileController::class, 'removeFcmToken']);
        Route::post('/test-notification', [App\Http\Controllers\Franchisee\ProfileController::class, 'sendTestNotification']);
        
        //Catalog
        Route::get('/catalog', [App\Http\Controllers\Franchisee\CatalogController::class, 'index']);
        Route::post('/toggle-favorite', [App\Http\Controllers\Franchisee\CatalogController::class, 'toggleFavorite']);
        
        //Cart
        Route::get('/products/{id}/details', [\App\Http\Controllers\Franchisee\ProductController::class, 'apiGetProductDetails']);
        Route::get('/cart', [App\Http\Controllers\Franchisee\CartController::class, 'index']);
        Route::post('/cart/add', [App\Http\Controllers\Franchisee\CartController::class, 'addToCart']);
        Route::post('/cart/update', [App\Http\Controllers\Franchisee\CartController::class, 'updateCart']);
        Route::post('/cart/remove', [App\Http\Controllers\Franchisee\CartController::class, 'removeFromCart']);
        Route::get('/cart/clear', [App\Http\Controllers\Franchisee\CartController::class, 'clearCart']);
        Route::get('/cart/checkout', [App\Http\Controllers\Franchisee\CartController::class, 'checkout']);
        Route::post('/cart/place-order', [App\Http\Controllers\Franchisee\CartController::class, 'placeOrder']);
        Route::post('/cart/update-item', [App\Http\Controllers\Franchisee\CartController::class, 'updateCartItemQuantity']);
        Route::post('/cart/place-order', [App\Http\Controllers\Franchisee\CartController::class, 'placeOrder']);
        // Orders - Updated with full API functionality
        Route::prefix('orders')->group(function () {
            // Get pending orders (with optional status filter)
            Route::get('/pending', [App\Http\Controllers\Franchisee\OrderController::class, 'pendingOrders']);
            
            // Get order history (with optional filters)
            Route::get('/history', [App\Http\Controllers\Franchisee\OrderController::class, 'orderHistory']);
            
            // Get detailed information for a specific order
            Route::get('/{id}', [App\Http\Controllers\Franchisee\OrderController::class, 'orderDetails'])
                ->where('id', '[0-9]+');
            
            // Update order status
            Route::patch('/{id}/status/{status}', [App\Http\Controllers\Franchisee\OrderController::class, 'updateOrderStatus'])
                ->where('id', '[0-9]+');
            
                // Repeat a previous order - API VERSION
            Route::post('/{id}/repeat-api', [App\Http\Controllers\Franchisee\OrderController::class, 'repeatOrderApi'])
            ->where('id', '[0-9]+');
            
            // Generate invoice
            Route::get('/{id}/invoice', [App\Http\Controllers\Franchisee\OrderController::class, 'generateInvoice'])
                ->where('id', '[0-9]+');
        });
        
        // Dismiss welcome banner
        Route::post('/dismiss-welcome', [App\Http\Controllers\Franchisee\OrderController::class, 'dismissWelcomeBanner']);
    });
});

// Fallback for undefined routes
Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.'
    ], 404);
});
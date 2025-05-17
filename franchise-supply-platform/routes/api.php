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
Route::get('/test', function() {
    return response()->json([
        'message' => 'API is working',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// In your routes/api.php for testing only - remove in production
Route::post('/test-profile-update', function(Request $request) {
  return response()->json([
      'success' => true,
      'received' => [
          'fields' => $request->all(),
          'files' => $request->hasFile('logo') ? 'Has logo file' : 'No logo file',
          'method' => $request->method(),
          'content_type' => $request->header('Content-Type'),
          'authorization' => $request->hasHeader('Authorization') ? 'Present' : 'Missing'
      ]
  ]);
});
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
        
        //Catalog
        Route::get('/catalog', [App\Http\Controllers\Franchisee\CatalogController::class, 'index']);
        Route::post('/toggle-favorite', [App\Http\Controllers\Franchisee\CatalogController::class, 'toggleFavorite']);
        
        //Cart
        Route::get('/cart', [App\Http\Controllers\Franchisee\CartController::class, 'index']);
        Route::post('/cart/add', [App\Http\Controllers\Franchisee\CartController::class, 'addToCart']);
        Route::post('/cart/update', [App\Http\Controllers\Franchisee\CartController::class, 'updateCart']);
        Route::post('/cart/remove', [App\Http\Controllers\Franchisee\CartController::class, 'removeFromCart']);
        Route::get('/cart/clear', [App\Http\Controllers\Franchisee\CartController::class, 'clearCart']);
        Route::get('/cart/checkout', [App\Http\Controllers\Franchisee\CartController::class, 'checkout']);
        Route::post('/cart/place-order', [App\Http\Controllers\Franchisee\CartController::class, 'placeOrder']);
        
        //Orders
        Route::get('/orders/pending', [App\Http\Controllers\Franchisee\OrderController::class, 'pendingOrders']);
        Route::get('/orders/history', [App\Http\Controllers\Franchisee\OrderController::class, 'orderHistory']);
        Route::get('/orders/{order}/details', [App\Http\Controllers\Franchisee\OrderController::class, 'orderDetails']);
        Route::get('/orders/{order}/repeat', [App\Http\Controllers\Franchisee\OrderController::class, 'repeatOrder']);
        Route::get('/orders/{order}/invoice', [App\Http\Controllers\Franchisee\OrderController::class, 'generateInvoice']);
    });
});

// Fallback for undefined routes
Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.'
    ], 404);
});
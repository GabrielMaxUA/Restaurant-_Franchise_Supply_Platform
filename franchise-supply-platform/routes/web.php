<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Franchisee\DashboardController as FranchiseeDashboardController;
use App\Http\Controllers\Franchisee\CatalogController;
use App\Http\Controllers\Franchisee\CartController;
use App\Http\Controllers\Franchisee\OrderController;
use App\Http\Controllers\Franchisee\InventoryController;
use App\Http\Controllers\Franchisee\ProfileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Set the login page as the default landing page
Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'webLogin']);
Route::get('/logout', [AuthController::class, 'webLogout'])->name('logout');

// Admin Routes - Protected by auth middleware
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Redirect /admin to dashboard
    Route::get('/', function() {
        return redirect('/admin/dashboard');
    });
    
    // User management routes
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    
    // Product routes
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('admin.products.show');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');
    
    // Category routes
    Route::get('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/categories/{category}/edit', [App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    
    // Order management routes
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('/orders/{order}/quickbooks', [App\Http\Controllers\Admin\OrderController::class, 'syncToQuickBooks'])->name('admin.orders.sync-quickbooks');
});

// Warehouse Routes - Protected by auth and role middleware
Route::prefix('warehouse')->middleware(['auth', 'role:warehouse'])->group(function () {
  // Dashboard
  Route::get('/dashboard', [App\Http\Controllers\Warehouse\DashboardController::class, 'index'])->name('warehouse.dashboard');
  
  // Redirect /warehouse to dashboard
  Route::get('/', function() {
      return redirect('/warehouse/dashboard');
  });
  
  // Product routes - using dedicated Warehouse controllers
  Route::get('/products', [App\Http\Controllers\Warehouse\ProductController::class, 'index'])->name('warehouse.products.index');
  Route::get('/products/create', [App\Http\Controllers\Warehouse\ProductController::class, 'create'])->name('warehouse.products.create');
  Route::post('/products', [App\Http\Controllers\Warehouse\ProductController::class, 'store'])->name('warehouse.products.store');
  Route::get('/products/{product}', [App\Http\Controllers\Warehouse\ProductController::class, 'show'])->name('warehouse.products.show');
  Route::get('/products/{product}/edit', [App\Http\Controllers\Warehouse\ProductController::class, 'edit'])->name('warehouse.products.edit');
  Route::put('/products/{product}', [App\Http\Controllers\Warehouse\ProductController::class, 'update'])->name('warehouse.products.update');
  Route::delete('/products/{product}', [App\Http\Controllers\Warehouse\ProductController::class, 'destroy'])->name('warehouse.products.destroy');
  
  // Category routes - using dedicated Warehouse controllers
  Route::get('/categories', [App\Http\Controllers\Warehouse\CategoryController::class, 'index'])->name('warehouse.categories.index');
  Route::get('/categories/create', [App\Http\Controllers\Warehouse\CategoryController::class, 'create'])->name('warehouse.categories.create');
  Route::post('/categories', [App\Http\Controllers\Warehouse\CategoryController::class, 'store'])->name('warehouse.categories.store');
  Route::get('/categories/{category}', [App\Http\Controllers\Warehouse\CategoryController::class, 'show'])->name('warehouse.categories.show');
  Route::get('/categories/{category}/edit', [App\Http\Controllers\Warehouse\CategoryController::class, 'edit'])->name('warehouse.categories.edit');
  Route::put('/categories/{category}', [App\Http\Controllers\Warehouse\CategoryController::class, 'update'])->name('warehouse.categories.update');
  Route::delete('/categories/{category}', [App\Http\Controllers\Warehouse\CategoryController::class, 'destroy'])->name('warehouse.categories.destroy');
  
  // Inventory management - using dedicated Warehouse controllers
  Route::get('/inventory/low-stock', [App\Http\Controllers\Warehouse\ProductController::class, 'lowStock'])->name('warehouse.inventory.low-stock');
  Route::get('/inventory/out-of-stock', [App\Http\Controllers\Warehouse\ProductController::class, 'outOfStock'])->name('warehouse.inventory.out-of-stock');
  Route::get('/inventory/popular', [App\Http\Controllers\Warehouse\ProductController::class, 'mostPopular'])->name('warehouse.inventory.popular');
});

// Franchisee Routes - Protected by auth and role middleware
Route::prefix('franchisee')->middleware(['auth', 'role:franchisee'])->group(function () {
    
  // Dashboard
  Route::get('/dashboard', [FranchiseeDashboardController::class, 'index'])->name('franchisee.dashboard');
  
  // Redirect /franchisee to dashboard
  Route::get('/', function() {
      return redirect('/franchisee/dashboard');
  });
  
  // Product Catalog
  Route::get('/catalog', [CatalogController::class, 'index'])->name('franchisee.catalog');
  Route::post('/toggle-favorite', [CatalogController::class, 'toggleFavorite'])->name('franchisee.toggle.favorite');
  
  // Shopping Cart
  Route::get('/cart', [CartController::class, 'index'])->name('franchisee.cart');
  Route::post('/cart/add', [CartController::class, 'addToCart'])->name('franchisee.cart.add');
  Route::post('/cart/update', [CartController::class, 'updateCart'])->name('franchisee.cart.update');
  Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('franchisee.cart.remove');
  Route::get('/cart/clear', [CartController::class, 'clearCart'])->name('franchisee.cart.clear');
  Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('franchisee.cart.checkout');
  Route::post('/cart/place-order', [CartController::class, 'placeOrder'])->name('franchisee.cart.place-order');
  // Add this route for getting the franchisee address
  Route::get('/franchisee/get-address', [App\Http\Controllers\Franchisee\AddressController::class, 'getAddress'])->name('franchisee.get-address');
  
  Route::get('/test-franchisee-detail', function() {
    // Test creating a franchisee detail
    try {
        $detail = \App\Models\FranchiseeDetail::create([
            'user_id' => 1, // Make sure this is a valid user ID
            'company_name' => 'Test Company',
            'address' => 'Test Address',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'contact_name' => 'Test Contact',
        ]);
        
        return "Successfully created franchisee detail with ID: " . $detail->id;
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
  
  // Orders
  Route::get('/orders/pending', [OrderController::class, 'pendingOrders'])->name('franchisee.orders.pending');
  Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('franchisee.orders.history');
  Route::get('/orders/{order}/details', [OrderController::class, 'orderDetails'])->name('franchisee.orders.details');
  Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('franchisee.orders.cancel');
  Route::get('/orders/{order}/modify', [OrderController::class, 'modifyOrder'])->name('franchisee.orders.modify');
  Route::post('/orders/{order}/update', [OrderController::class, 'updateOrder'])->name('franchisee.orders.update');
  Route::get('/orders/{order}/invoice', [OrderController::class, 'generateInvoice'])->name('franchisee.orders.invoice');
  Route::get('/orders/{order}/repeat', [OrderController::class, 'repeatOrder'])->name('franchisee.orders.repeat');
  Route::get('/orders/reports', [OrderController::class, 'reports'])->name('franchisee.orders.reports');
  Route::get('/orders/export', [OrderController::class, 'export'])->name('franchisee.orders.export');
  Route::get('/track/{tracking_number}', [OrderController::class, 'trackOrder'])->name('franchisee.track');
  
  // Inventory
  Route::get('/inventory', [InventoryController::class, 'index'])->name('franchisee.inventory');
  Route::get('/inventory/export', [InventoryController::class, 'export'])->name('franchisee.inventory.export');
  Route::post('/inventory/update', [InventoryController::class, 'updateInventory'])->name('franchisee.inventory.update');
  
  // Profile & Settings
  Route::get('/profile', [ProfileController::class, 'index'])->name('franchisee.profile');
  Route::post('/profile/update', [ProfileController::class, 'update'])->name('franchisee.profile.update');
  Route::get('/settings', [ProfileController::class, 'settings'])->name('franchisee.settings');
  Route::post('/settings/update', [ProfileController::class, 'updateSettings'])->name('franchisee.settings.update');
});

// Catch-all redirect to login for unauthenticated users
Route::fallback(function () {
    return redirect()->route('login');
});
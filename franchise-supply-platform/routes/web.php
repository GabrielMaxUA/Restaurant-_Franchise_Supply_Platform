<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Franchisee\DashboardController as FranchiseeDashboardController;
use App\Http\Controllers\Franchisee\CatalogController;
use App\Http\Controllers\Franchisee\CartController;
use App\Http\Controllers\Franchisee\OrderController;
use App\Http\Controllers\Franchisee\InventoryController;
use App\Http\Controllers\Franchisee\ProfileController;
use App\Http\Controllers\Warehouse\WarehouseProfileController;
use App\Http\Middleware\CheckUserStatus;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes - Not requiring authentication
// -------------------------------------------

// Set the login page as the default landing page
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/test-status', function() {
  return "Status middleware is working!";
})
->middleware([CheckUserStatus::class]);

// Test route
Route::get('/web-test', function () {
    return "Web routes are working!";
});

// Order notification test route (only accessible in development environment)
Route::get('/test-order-notification', function () {
    if (!app()->environment(['local', 'development'])) {
        abort(404);
    }

    try {
        // Get an example order (most recent)
        $order = \App\Models\Order::with(['items.product', 'items.variant', 'user.franchiseeProfile'])
            ->latest()
            ->first();

        if (!$order) {
            return "No orders found to test notification with.";
        }

        // Get admin and warehouse emails
        $adminEmails = \App\Models\User::getAdminEmails();
        $warehouseEmails = \App\Models\User::getWarehouseEmails();

        // Add hardcoded fallbacks
        if (empty($adminEmails)) {
            $adminEmails = [config('company.admin_notification_email', 'maxgabrielua@gmail.com')];
        } else {
            // Make sure maxgabrielua@gmail.com is always included for admin notifications
            if (!in_array('maxgabrielua@gmail.com', $adminEmails)) {
                $adminEmails[] = 'maxgabrielua@gmail.com';
            }
        }

        // Add warehouse fallback
        if (empty($warehouseEmails)) {
            $warehouseEmails[] = config('company.warehouse_notification_email', 'warehouse@restaurantfranchisesupply.com');
        }

        // Filter emails to ensure they're valid
        $adminEmails = array_unique(array_filter($adminEmails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));

        $warehouseEmails = array_unique(array_filter($warehouseEmails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));

        // Send admin notifications
        if (!empty($adminEmails)) {
            \Illuminate\Support\Facades\Mail::to($adminEmails)
                ->send(new \App\Notifications\NewOrderNotification($order, true));
        }

        // Send warehouse notifications
        if (!empty($warehouseEmails)) {
            \Illuminate\Support\Facades\Mail::to($warehouseEmails)
                ->send(new \App\Notifications\NewOrderNotification($order, false));
        }

        // Send customer confirmation if customer email exists
        $customerEmail = $order->user->email;
        if ($customerEmail && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            \Illuminate\Support\Facades\Mail::to($customerEmail)
                ->send(new \App\Mail\OrderConfirmationEmail($order));
        }

        return "Test order notifications sent!<br><br>
                <strong>Admin Emails:</strong> " . implode(', ', $adminEmails) . "<br>
                <strong>Warehouse Emails:</strong> " . implode(', ', $warehouseEmails) . "<br>
                <strong>Customer Email:</strong> " . ($customerEmail ?: 'None') . "<br><br>
                <strong>Order ID:</strong> " . $order->id;
    } catch (\Exception $e) {
        return "Error sending test order notifications: " . $e->getMessage() . "<br><br>
                <strong>Trace:</strong><br>
                " . nl2br($e->getTraceAsString());
    }
});

// Email test route (only accessible in development environment)
Route::get('/mail-test', function () {
    if (!app()->environment(['local', 'development'])) {
        abort(404);
    }

    try {
        $recipient = request('email') ?? 'maxgabrielua@gmail.com';

        // Get mail configuration
        $mailConfig = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from' => config('mail.from'),
            'encryption' => config('mail.mailers.smtp.encryption'),
        ];

        // Send a test email
        \Mail::to($recipient)->send(new \Illuminate\Mail\Mailable\MailMessage([
            'subject' => 'Test Email from Restaurant Franchise Supply',
            'greeting' => 'Hello!',
            'introLines' => [
                'This is a test email from your Restaurant Franchise Supply system.',
                'If you received this email, your mail configuration is working correctly.'
            ],
            'outroLines' => [
                'Mail Configuration:',
                'Driver: ' . $mailConfig['driver'],
                'Host: ' . $mailConfig['host'],
                'Port: ' . $mailConfig['port'],
                'From: ' . $mailConfig['from']['address'],
            ],
        ]));

        return "Test email sent to {$recipient}! Check your inbox or mail logs.<br><br>
                <strong>Mail Configuration:</strong><br>
                Driver: {$mailConfig['driver']}<br>
                Host: {$mailConfig['host']}<br>
                Port: {$mailConfig['port']}<br>
                From: {$mailConfig['from']['address']}<br>
                Encryption: {$mailConfig['encryption']}";
    } catch (\Exception $e) {
        return "Error sending test email: " . $e->getMessage() . "<br><br>
                <strong>Trace:</strong><br>
                " . nl2br($e->getTraceAsString());
    }
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'webLogin'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'webLogout'])->name('logout');

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

// Protected Routes - Requiring authentication and active status
// ------------------------------------------------------------
// First check authentication, then check status
Route::middleware(['auth'])->middleware(CheckUserStatus::class)->group(function () {

    // Routes available to all authenticated users
    // Invoice route - accessible by admin, warehouse staff, and franchisees
    Route::get('/orders/{id}/invoice', [App\Http\Controllers\Franchisee\OrderController::class, 'generateInvoice'])
        ->name('franchisee.orders.invoice');

    // Notification Routes (available to all authenticated users)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::get('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
    });
    
    // Admin Routes - Protected by role:admin middleware
    Route::prefix('admin')->middleware(['role:admin'])->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Admin Profile Routes
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('index');
            Route::get('/settings', [App\Http\Controllers\Admin\AdminProfileController::class, 'settings'])->name('settings');
            Route::put('/update', [App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('update');
            Route::post('/change-password', [App\Http\Controllers\Admin\AdminProfileController::class, 'changePassword'])->name('change-password');
        });
        
        // User Management Routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/info', [App\Http\Controllers\Admin\UserController::class, 'getUserInfo'])->name('info');
            
            // User Status Toggle Route
            Route::post('/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        // Product Routes
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('destroy');
        });
        
        // Category Routes
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
            Route::get('/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('show');
            Route::get('/{category}/edit', [App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');
        });
        
        // Order Management Routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('edit');
            Route::patch('/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/quickbooks', [App\Http\Controllers\Admin\OrderController::class, 'syncToQuickBooks'])->name('sync-quickbooks');
        });

        // QuickBooks Integration Routes
        Route::prefix('quickbooks')->name('quickbooks.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\QuickBooksController::class, 'settings'])->name('settings');
            Route::get('/connect', [App\Http\Controllers\Admin\QuickBooksController::class, 'connect'])->name('connect');
            Route::get('/callback', [App\Http\Controllers\Admin\QuickBooksController::class, 'callback'])->name('callback');
            Route::get('/disconnect', [App\Http\Controllers\Admin\QuickBooksController::class, 'disconnect'])->name('disconnect');
            Route::get('/refresh-token', [App\Http\Controllers\Admin\QuickBooksController::class, 'refreshToken'])->name('refresh-token');
            Route::get('/test-connection', [App\Http\Controllers\Admin\QuickBooksController::class, 'testConnection'])->name('test-connection');
        });
    });

    // Warehouse Routes - Protected by role:warehouse middleware
    Route::prefix('warehouse')->middleware(['role:warehouse'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Warehouse\DashboardController::class, 'index'])->name('warehouse.dashboard');
        
        // Redirect /warehouse to dashboard
        Route::get('/', function() {
            return redirect('/warehouse/dashboard');
        });
        
        // Warehouse Profile
        Route::get('/profile', [WarehouseProfileController::class, 'index'])->name('warehouse.profile');
        Route::post('/profile/update', [WarehouseProfileController::class, 'update'])->name('warehouse.profile.update');
        Route::get('/settings', [WarehouseProfileController::class, 'settings'])->name('warehouse.settings');
        Route::post('/profile/change-password', [WarehouseProfileController::class, 'changePassword'])->name('warehouse.profile.change-password');
        
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

        // Order management - shared controller with admin, but with role-based access control
        Route::prefix('orders')->name('warehouse.orders.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
            Route::get('/pending', [App\Http\Controllers\Admin\OrderController::class, 'pendingOrders'])->name('pending');
            Route::get('/in-progress', [App\Http\Controllers\Admin\OrderController::class, 'inProgress'])->name('in-progress');
            Route::get('/shipped', [App\Http\Controllers\Admin\OrderController::class, 'shipped'])->name('shipped');
            Route::get('/completed', [App\Http\Controllers\Admin\OrderController::class, 'completed'])->name('completed');
            Route::get('/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('show');
            Route::patch('/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('update-status');
            Route::get('/{order}/packing-slip', [App\Http\Controllers\Admin\OrderController::class, 'packingSlip'])->name('packing-slip');
            Route::get('/{order}/shipping-label', [App\Http\Controllers\Admin\OrderController::class, 'shippingLabel'])->name('shipping-label');
            Route::get('/reports/fulfillment', [App\Http\Controllers\Admin\OrderController::class, 'fulfillmentReport'])->name('fulfillment-report');
            Route::get('/check-new', [App\Http\Controllers\Admin\OrderController::class, 'checkNewOrders'])->name('check-new');
        });
    });

    // Franchisee Routes - Protected by role:franchisee middleware
    Route::prefix('franchisee')->middleware(['role:franchisee'])->group(function () {
        
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

        // Route for getting the franchisee address
        Route::get('/get-address', [App\Http\Controllers\Franchisee\ProfileController::class, 'getAddress'])->name('franchisee.get-address');
        
        // Orders
        Route::get('/orders/pending', [OrderController::class, 'pendingOrders'])->name('franchisee.orders.pending');
        Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('franchisee.orders.history');
        Route::get('/orders/{order}/details', [OrderController::class, 'orderDetails'])->name('franchisee.orders.details');
        Route::get('/orders/{order}/modify', [OrderController::class, 'modifyOrder'])->name('franchisee.orders.modify');
        Route::post('/orders/{order}/update', [OrderController::class, 'updateOrder'])->name('franchisee.orders.update');
        Route::get('/orders/{order}/repeat', [OrderController::class, 'repeatOrder'])->name('franchisee.orders.repeat');
        Route::get('/orders/reports', [OrderController::class, 'reports'])->name('franchisee.orders.reports');
        Route::get('/orders/export', [OrderController::class, 'export'])->name('franchisee.orders.export');
        Route::get('/track/{tracking_number}', [OrderController::class, 'trackOrder'])->name('franchisee.track');
        
        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])->name('franchisee.inventory');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('franchisee.inventory.export');
        Route::post('/inventory/update', [InventoryController::class, 'updateInventory'])->name('franchisee.inventory.update');
        
        // Profile routes
        Route::get('/profile', [ProfileController::class, 'index'])->name('franchisee.profile');
        Route::put('/profile/update', [ProfileController::class, 'update'])->name('franchisee.profile.update');
        Route::delete('/profile/delete-logo', [App\Http\Controllers\Franchisee\ProfileController::class, 'deleteLogo'])->name('franchisee.profile.delete-logo');
        
        // Settings routes
        Route::get('/settings', [ProfileController::class, 'settings'])->name('franchisee.settings');
        Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('franchisee.profile.update-password');
        
        // Address API route
        Route::get('/address', [ProfileController::class, 'getAddress'])->name('franchisee.address');
    });
});

// Catch-all redirect to login for unauthenticated users
Route::fallback(function () {
    return redirect()->route('login');
});
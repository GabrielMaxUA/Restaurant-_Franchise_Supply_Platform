<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of the orders with comprehensive filtering options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Start with base query including relationships
        $query = Order::with(['user', 'user.franchiseeProfile'])->orderBy('created_at', 'desc');

        // For warehouse users, only show orders that are approved or further in the process
        if (Auth::user()->isWarehouse()) {
            $query->whereIn('status', ['approved', 'packed', 'shipped', 'delivered']);
        }

        // Apply Order Number filter (renamed from Order ID)
        if ($request->filled('order_number')) {
            $query->where('id', $request->order_number);
        }

        // Apply Invoice Number filter
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        // Apply Status filter - limit options for warehouse users
        if ($request->filled('status')) {
            $statusFilter = $request->status;

            // Check if status is an array (multiple statuses)
            if (is_array($statusFilter)) {
                if (Auth::user()->isWarehouse()) {
                    // For warehouse users, only allow valid statuses
                    $validStatuses = array_intersect($statusFilter, ['approved', 'packed', 'shipped', 'delivered']);
                    if (!empty($validStatuses)) {
                        $query->whereIn('status', $validStatuses);
                    }
                } else {
                    // For admin, allow all statuses
                    $query->whereIn('status', $statusFilter);
                }
            } else {
                // Single status filter
                if (Auth::user()->isWarehouse() && !in_array($statusFilter, ['approved', 'packed', 'shipped', 'delivered'])) {
                    // For warehouse users, ignore invalid status filters
                } else {
                    $query->where('status', $statusFilter);
                }
            }
        }

        // Apply Username filter
        if ($request->filled('username')) {
            $username = $request->username;
            $query->whereHas('user', function($q) use ($username) {
                $q->where('username', 'like', "%{$username}%");
            });
        }

        // Apply Company Name filter
        if ($request->filled('company_name')) {
            $companyName = $request->company_name;
            $query->whereHas('user.franchiseeProfile', function($q) use ($companyName) {
                $q->where('company_name', 'like', "%{$companyName}%");
            });
        }

        // Apply Date Range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by specific user if requested (maintain existing functionality)
        if ($request->has('user_id')) {
            $userId = $request->user_id;
            $query->where('user_id', $userId);

            // Get the user for the page title
            $user = User::find($userId);
            $username = $user ? $user->username : 'User';

            // Set custom page title to indicate we're viewing a specific user's orders
            $pageTitle = "Orders for $username";
        } else {
            $pageTitle = "Order Management";
            $username = null;
        }

        $orders = $query->paginate(15);

        // Count orders by status for dashboard metrics
        $pendingCount = Auth::user()->isAdmin() ? Order::where('status', 'pending')->count() : 0;
        $approvedCount = Order::where('status', 'approved')->count();
        $packedCount = Order::where('status', 'packed')->count();
        $shippedCount = Order::where('status', 'shipped')->count();
        $deliveredCount = Order::where('status', 'delivered')->count();

        // Use different views based on user role
        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.index' : 'warehouse.orders.index';

        return view($viewPath, [
            'orders' => $orders,
            'pageTitle' => $pageTitle,
            'username' => $username,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'packedCount' => $packedCount,
            'shippedCount' => $shippedCount,
            'deliveredCount' => $deliveredCount
        ]);
    }

    /**
     * Display only pending orders (for admins) or approved orders (for warehouse)
     */
    public function pendingOrders()
    {
        // For warehouse staff, show approved orders that need to be packed
        // For admins, show pending orders that need approval/rejection
        $status = Auth::user()->isAdmin() ? 'pending' : 'approved';

        $orders = Order::with(['user', 'user.franchiseeProfile'])
            ->where('status', $status)
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        $pageTitle = Auth::user()->isAdmin() ? 'Orders Awaiting Approval' : 'Orders Awaiting Fulfillment';
        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.pending' : 'warehouse.orders.pending';

        return view($viewPath, [
            'orders' => $orders,
            'pageTitle' => $pageTitle,
        ]);
    }

    /**
     * Display orders in progress (packed status)
     */
    public function inProgress()
    {
        // This endpoint is primarily for warehouse users
        if (!Auth::user()->isWarehouse() && !Auth::user()->isAdmin()) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'You do not have permission to view this page.');
        }

        $orders = Order::with(['user', 'user.franchiseeProfile'])
            ->where('status', 'packed')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.in-progress' : 'warehouse.orders.in-progress';

        return view($viewPath, [
            'orders' => $orders,
            'pageTitle' => 'Orders In Progress',
        ]);
    }

    /**
     * Display shipped orders
     */
    public function shipped()
    {
        $orders = Order::with(['user', 'user.franchiseeProfile'])
            ->where('status', 'shipped')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.shipped' : 'warehouse.orders.shipped';

        return view($viewPath, [
            'orders' => $orders,
            'pageTitle' => 'Shipped Orders',
        ]);
    }

    /**
     * Display completed (delivered) orders
     */
    public function completed()
    {
        $orders = Order::with(['user', 'user.franchiseeProfile'])
            ->where('status', 'delivered')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.completed' : 'warehouse.orders.completed';

        return view($viewPath, [
            'orders' => $orders,
            'pageTitle' => 'Completed Orders',
        ]);
    }

    /**
     * Check for new orders and low inventory items via AJAX
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkNewOrders()
    {
        if (Auth::user()->isAdmin()) {
            $pendingOrdersCount = Order::where('status', 'pending')->count();
            $lowInventoryCount = Product::where('inventory_count', '<=', 10)->count();

            return response()->json([
                'pending_orders_count' => $pendingOrdersCount,
                'low_inventory_count' => $lowInventoryCount
            ]);
        } else {
            // For warehouse users, check for new approved orders
            $approvedOrdersCount = Order::where('status', 'approved')->count();
            $lowInventoryCount = Product::where('inventory_count', '<=', 10)->count();

            return response()->json([
                'approved_orders_count' => $approvedOrdersCount,
                'low_inventory_count' => $lowInventoryCount
            ]);
        }
    }

    /**
     * Display the specified order details
     *
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        // For warehouse users, verify the order is in a status they should see
        if (Auth::user()->isWarehouse() && !in_array($order->status, ['approved', 'packed', 'shipped', 'delivered'])) {
            return redirect()->route('warehouse.orders.index')
                ->with('error', 'You do not have permission to view this order.');
        }

        // Eager load the order items with products and variants
        $order->load(['items.product.images', 'items.variant', 'user', 'user.franchiseeProfile']);

        // Use different views based on user role
        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.show' : 'warehouse.orders.show';

        return view($viewPath, compact('order'));
    }

    /**
     * Update the order status
     * This method handles both PATCH and POST requests to /admin/orders/{order}/status
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Different validation rules based on role
        if (Auth::user()->isAdmin()) {
            $request->validate([
                'status' => 'required|in:pending,approved,rejected,packed,shipped,delivered'
            ]);
        } else if (Auth::user()->isWarehouse()) {
            $request->validate([
                'status' => 'required|in:packed,shipped,delivered'
            ]);

            // Make sure the warehouse user can only update orders in appropriate statuses
            // Explicitly check for rejected/cancelled/pending orders
            if ($order->status === 'rejected') {
                return redirect()->back()->with('error', 'This order has been rejected by an administrator and cannot be processed.');
            } else if ($order->status === 'cancelled') {
                return redirect()->back()->with('error', 'This order has been cancelled and cannot be processed.');
            } else if ($order->status === 'pending') {
                return redirect()->back()->with('error', 'This order is pending approval and cannot be processed yet.');
            } else if (!in_array($order->status, ['approved', 'packed', 'shipped'])) {
                return redirect()->back()->with('error', 'You cannot update the status of this order.');
            }

            // For warehouse users, ensure logical status progression
            $oldStatus = $order->status;
            $newStatus = $request->status;

            if (
                ($oldStatus === 'approved' && $newStatus !== 'packed') ||
                ($oldStatus === 'packed' && $newStatus !== 'shipped') ||
                ($oldStatus === 'shipped' && $newStatus !== 'delivered')
            ) {
                return redirect()->back()->with('error', "Invalid status transition from {$oldStatus} to {$newStatus}");
            }
        } else {
            return redirect()->back()->with('error', 'You do not have permission to update order status.');
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Debug logging
        Log::info('Order status update request', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'user_role' => Auth::user()->role ? Auth::user()->role->name : 'unknown'
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // If status is changing to rejected or cancelled, restore inventory
            if (($newStatus === 'rejected' || $newStatus === 'cancelled') &&
                ($oldStatus !== 'rejected' && $oldStatus !== 'cancelled')) {

                Log::info('Restoring inventory for order', [
                    'order_id' => $order->id,
                    'new_status' => $newStatus
                ]);

                // Load order items if not already loaded
                if (!$order->relationLoaded('items')) {
                    $order->load('items');
                }

                // Restore inventory for each item
                foreach ($order->items as $item) {
                    $success = $this->inventoryService->increaseInventory(
                        $item->product_id,
                        $item->quantity,
                        $item->variant_id
                    );

                    if (!$success) {
                        throw new \Exception("Failed to restore inventory for product ID: {$item->product_id}");
                    }

                    Log::info('Inventory restored for item', [
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity
                    ]);
                }
            }

            // Update the order status
            $order->status = $newStatus;

            // Generate invoice number and set approval timestamp when status is changed to approved
            if ($newStatus === 'approved' && $oldStatus !== 'approved') {
                // Check if invoice_number column exists
                if (Schema::hasColumn('orders', 'invoice_number')) {
                    // Generate invoice number (prefix + order ID + year + month)
                    $order->invoice_number = 'INV-' . $order->id . '-' . date('Ym');
                }

                // Check if approved_at column exists
                if (Schema::hasColumn('orders', 'approved_at')) {
                    $order->approved_at = now();
                }

                Log::info('Order approved and invoice generated', [
                    'order_id' => $order->id,
                    'invoice_number' => $order->invoice_number ?? 'Not set'
                ]);
            }

            // Set shipping tracking number if provided (when status is changed to shipped)
            if ($newStatus === 'shipped') {
                if ($request->filled('tracking_number')) {
                    // Check if tracking_number column exists
                    if (Schema::hasColumn('orders', 'tracking_number')) {
                        $order->tracking_number = $request->tracking_number;
                    }
                }
                // Check if shipped_at column exists
                if (Schema::hasColumn('orders', 'shipped_at')) {
                    $order->shipped_at = now();
                }
            }

            // Set delivery timestamp if status is changed to delivered
            if ($newStatus === 'delivered') {
                // Check if delivered_at column exists
                if (Schema::hasColumn('orders', 'delivered_at')) {
                    $order->delivered_at = now();
                }
            }

            $order->save();

            // Commit the transaction
            DB::commit();

            return redirect()->back()->with('success', "Order status updated to {$newStatus}");

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            Log::error('Error updating order status', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', "Failed to update order status: {$e->getMessage()}");
        }
    }

    /**
     * Print packing slip for an order
     *
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function packingSlip(Order $order)
    {
        // Verify the order is in a valid status
        if (Auth::user()->isWarehouse() && !in_array($order->status, ['approved', 'packed', 'shipped', 'delivered'])) {
            return redirect()->route('warehouse.orders.index')
                ->with('error', 'You do not have permission to view this order.');
        }

        // Eager load the order items with products and variants
        $order->load(['items.product.images', 'items.variant', 'user', 'user.franchiseeProfile']);

        return view('warehouse.orders.packing-slip', compact('order'));
    }

    /**
     * Print shipping label for an order
     *
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function shippingLabel(Order $order)
    {
        // Verify the order is in a valid status
        if (Auth::user()->isWarehouse() && !in_array($order->status, ['approved', 'packed', 'shipped', 'delivered'])) {
            return redirect()->route('warehouse.orders.index')
                ->with('error', 'You do not have permission to view this order.');
        }

        // Eager load the order items with products and variants
        $order->load(['items.product.images', 'items.variant', 'user', 'user.franchiseeProfile']);

        return view('warehouse.orders.shipping-label', compact('order'));
    }

    /**
     * Sync order to QuickBooks (admin only)
     *
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncToQuickBooks(Order $order)
    {
        // Only admins can sync to QuickBooks
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'You do not have permission to sync orders to QuickBooks.');
        }

        // Only sync approved orders that haven't been synced yet
        if ($order->status == 'approved' && !$order->qb_invoice_id) {
            $qbService = new \App\Services\QuickBooksService();
            $qbInvoiceId = $qbService->syncInvoice($order);

            return redirect()->route('admin.orders.show', $order)
                ->with('success', "Order synced to QuickBooks successfully. Invoice ID: $qbInvoiceId");
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('error', 'Order could not be synced to QuickBooks. It must be approved and not already synced.');
    }

    /**
     * Update the QuickBooks invoice ID (admin only)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateQbInvoice(Request $request, Order $order)
    {
        // Only admins can update QuickBooks invoice IDs
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'You do not have permission to update QuickBooks invoice IDs.');
        }

        $request->validate([
            'qb_invoice_id' => 'required|string|max:100',
        ]);

        $order->qb_invoice_id = $request->qb_invoice_id;
        $order->save();

        return redirect()->back()->with('success', "QuickBooks invoice ID updated successfully");
    }

    /**
     * Generate a fulfillment report for a date range
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fulfillmentReport(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');

        // Get counts by status for the date range
        $pendingCount = Auth::user()->isAdmin() ?
            Order::where('status', 'pending')
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->count() : 0;

        $approvedCount = Order::where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();

        $packedCount = Order::where('status', 'packed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();

        $shippedCount = Order::where('status', 'shipped')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();

        $deliveredCount = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();

        // Get fulfillment metrics (average time from approved to shipped)
        $fulfilledOrders = Order::whereIn('status', ['shipped', 'delivered'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->get();

        $totalFulfillmentTime = 0;
        $orderCount = count($fulfilledOrders);

        foreach ($fulfilledOrders as $order) {
            // Calculate time from approval to shipping
            // In a real-world app, you would have timestamps for each status change
            // Here we're estimating based on the created_at and updated_at fields
            $totalFulfillmentTime += $order->updated_at->diffInHours($order->created_at);
        }

        $avgFulfillmentTime = $orderCount > 0 ? $totalFulfillmentTime / $orderCount : 0;

        // Most ordered products in the date range
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereIn('status', ['approved', 'packed', 'shipped', 'delivered'])
                    ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();

        // Use different views based on user role
        $viewPath = Auth::user()->isAdmin() ? 'admin.orders.fulfillment-report' : 'warehouse.orders.fulfillment-report';

        return view($viewPath, [
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'packedCount' => $packedCount,
            'shippedCount' => $shippedCount,
            'deliveredCount' => $deliveredCount,
            'avgFulfillmentTime' => $avgFulfillmentTime,
            'topProducts' => $topProducts,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
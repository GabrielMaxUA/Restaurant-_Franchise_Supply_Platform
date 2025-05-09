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
        
        // Apply Order Number filter (renamed from Order ID)
        if ($request->filled('order_number')) {
            $query->where('id', $request->order_number);
        }
        
        // Apply Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        
        return view('admin.orders.index', [
            'orders' => $orders,
            'pageTitle' => $pageTitle,
            'username' => $username,
        ]);
    }
    
    /**
     * Check for new orders and low inventory items via AJAX
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkNewOrders()
    {
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $lowInventoryCount = Product::where('inventory_count', '<=', 10)->count();
        
        return response()->json([
            'pending_orders_count' => $pendingOrdersCount,
            'low_inventory_count' => $lowInventoryCount
        ]);
    }
    
    /**
     * Display the specified order details
     * 
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        // Eager load the order items with products and variants
        $order->load(['items.product.images', 'items.variant', 'user']);
        
        return view('admin.orders.show', compact('order'));
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
        // Validate the request
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,packed,shipped,delivered,cancelled'
        ]);
        
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Debug logging
        Log::info('Order status update request', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'request_method' => $request->method(),
            'request_path' => $request->path()
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
     * Sync order to QuickBooks
     * 
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncToQuickBooks(Order $order)
    {
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
     * Update the QuickBooks invoice ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateQbInvoice(Request $request, Order $order)
    {
        $request->validate([
            'qb_invoice_id' => 'required|string|max:100',
        ]);
        
        $order->qb_invoice_id = $request->qb_invoice_id;
        $order->save();
        
        return redirect()->back()->with('success', "QuickBooks invoice ID updated successfully");
    }
}
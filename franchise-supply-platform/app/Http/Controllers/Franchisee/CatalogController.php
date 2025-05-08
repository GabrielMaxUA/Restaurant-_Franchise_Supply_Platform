<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductFavorite;

class CatalogController extends Controller
{
    /**
     * Display the product catalog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Build the query with eager loading
        $query = Product::with(['category', 'images', 'variants', 'variants.images']);
        
        // Add favorite status check for the current user
        $query->withCount(['favoritedBy as is_favorite' => function($query) {
            $query->where('users.id', Auth::id());
        }]);
        
        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // For "show favorites only" filter
        if ($request->has('favorites') && $request->favorites == 1) {
            $query->whereHas('favoritedBy', function($query) {
                $query->where('users.id', Auth::id());
            });
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Apply inventory filter
        if ($request->filled('inventory')) {
            switch ($request->inventory) {
                case 'in_stock':
                    // Include products with stock OR products with in-stock variants
                    $query->where(function($q) {
                        $q->where('inventory_count', '>', 0)
                          ->orWhereHas('variants', function($sq) {
                              $sq->where('inventory_count', '>', 0);
                          });
                    });
                    break;
                case 'low_stock':
                    $query->where(function($q) {
                        $q->where('inventory_count', '>', 0)
                          ->where('inventory_count', '<=', 10)
                          ->orWhereHas('variants', function($sq) {
                              $sq->where('inventory_count', '>', 0)
                                ->where('inventory_count', '<=', 10);
                          });
                    });
                    break;
                case 'out_of_stock':
                    // Only truly out of stock items (no variants with stock either)
                    $query->where('inventory_count', 0)
                          ->whereDoesntHave('variants', function($q) {
                              $q->where('inventory_count', '>', 0);
                          });
                    break;
                case 'variants_only':
                    $query->where('inventory_count', 0)
                          ->whereHas('variants', function($q) {
                              $q->where('inventory_count', '>', 0);
                          });
                    break;
            }
        }
        
        // Apply sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'price_asc':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('base_price', 'desc');
                    break;
                case 'popular':
                    $query->withCount('orderItems')
                          ->orderBy('order_items_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            // Default sort by newest
            $query->orderBy('created_at', 'desc');
        }
        
        // Paginate the results
        $products = $query->paginate(12);
        
        // Format products for display
        foreach ($products as $product) {
            // Check if any variants are in stock
            $hasInStockVariants = false;
            $totalVariantInventory = 0;
            
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count > 0) {
                        $hasInStockVariants = true;
                        $totalVariantInventory += $variant->inventory_count;
                    }
                }
            }
            
            $product->has_in_stock_variants = $hasInStockVariants;
            $product->total_variant_inventory = $totalVariantInventory;
            
            // Determine if product is available to purchase (either main product or variants have stock)
            $product->is_purchasable = ($product->inventory_count > 0 || $hasInStockVariants);
            
            // Convert inventory_count to stock_status for the views
            if ($product->inventory_count <= 0) {
                if ($hasInStockVariants) {
                    $product->stock_status = 'variants_only';
                } else {
                    $product->stock_status = 'out_of_stock';
                }
            } elseif ($product->inventory_count <= 10) {
                $product->stock_status = 'low_stock';
            } else {
                $product->stock_status = 'in_stock';
            }
            
            // Map base_price to price for consistency in views
            $product->price = $product->base_price;
            
            // Set stock_quantity from inventory_count
            $product->stock_quantity = $product->inventory_count;
            
            // Get the primary image URL
            if ($product->images->isNotEmpty()) {
                $product->image_url = $product->images->first()->image_url;
            } else {
                $product->image_url = null;
            }
        }
        
        // Get categories for filter
        $categories = Category::withCount('products')->get();
        
        // Get total products count
        $total_products = Product::count();
        
        return view('franchisee.catalog', compact('products', 'categories', 'total_products'));
    }
    
    /**
     * Display details for a specific product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::with(['category', 'images', 'variants', 'variants.images'])
            ->findOrFail($id);
            
        // Check if any variants are in stock
        $hasInStockVariants = false;
        $totalVariantInventory = 0;
        
        if ($product->variants->isNotEmpty()) {
            foreach ($product->variants as $variant) {
                if ($variant->inventory_count > 0) {
                    $hasInStockVariants = true;
                    $totalVariantInventory += $variant->inventory_count;
                }
            }
        }
        
        $product->has_in_stock_variants = $hasInStockVariants;
        $product->total_variant_inventory = $totalVariantInventory;
        
        // Determine if product is available to purchase (either main product or variants have stock)
        $product->is_purchasable = ($product->inventory_count > 0 || $hasInStockVariants);
            
        // Convert inventory_count to stock_status
        if ($product->inventory_count <= 0) {
            if ($hasInStockVariants) {
                $product->stock_status = 'variants_only';
            } else {
                $product->stock_status = 'out_of_stock';
            }
        } elseif ($product->inventory_count <= 10) {
            $product->stock_status = 'low_stock';
        } else {
            $product->stock_status = 'in_stock';
        }
        
        // Map base_price to price for consistency
        $product->price = $product->base_price;
        
        // Set stock_quantity from inventory_count
        $product->stock_quantity = $product->inventory_count;
        
        // Get the primary image URL
        if ($product->images->isNotEmpty()) {
            $product->image_url = $product->images->first()->image_url;
        } else {
            $product->image_url = null;
        }
        
        // Get related products from the same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();
            
        foreach ($relatedProducts as $related) {
            if ($related->images->isNotEmpty()) {
                $related->image_url = $related->images->first()->image_url;
            } else {
                $related->image_url = null;
            }
            
            $related->price = $related->base_price;
        }
        
        return view('franchisee.product_details', compact('product', 'relatedProducts'));
    }

    public function catalog(Request $request)
    {
        // Start with the base query
        $query = Product::with(['category', 'images', 'variants', 'variants.images']);
        
        // Add this to check favorite status for the current user
        $query->withCount(['favoritedBy as is_favorite' => function($query) {
            $query->where('users.id', Auth::id());
        }]);
        
        // Apply category filter if present
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }
        
        // For "show favorites only" filter
        if ($request->has('favorites') && $request->favorites == 1) {
            $query->whereHas('favoritedBy', function($query) {
                $query->where('users.id', Auth::id());
            });
        }
        
        // Apply other filters as needed...
        
        // Get paginated results
        $products = $query->paginate(15);
        
        // Check each product for in-stock variants
        foreach ($products as $product) {
            // Check if any variants are in stock
            $hasInStockVariants = false;
            $totalVariantInventory = 0;
            
            if ($product->variants && $product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count > 0) {
                        $hasInStockVariants = true;
                        $totalVariantInventory += $variant->inventory_count;
                    }
                }
            }
            
            $product->has_in_stock_variants = $hasInStockVariants;
            $product->total_variant_inventory = $totalVariantInventory;
            
            // Determine if product is available to purchase (either main product or variants have stock)
            $product->is_purchasable = ($product->inventory_count > 0 || $hasInStockVariants);
            
            // Set stock status with variants-only check
            if ($product->inventory_count <= 0) {
                if ($hasInStockVariants) {
                    $product->stock_status = 'variants_only';
                } else {
                    $product->stock_status = 'out_of_stock';
                }
            } elseif ($product->inventory_count <= 10) {
                $product->stock_status = 'low_stock';
            } else {
                $product->stock_status = 'in_stock';
            }
        }
        
        // Load categories for the sidebar
        $categories = Category::withCount('products')->get();
        $total_products = Product::count();
        
        return view('franchisee.catalog', compact('products', 'categories', 'total_products'));
    }

    public function toggleFavorite(Request $request)
    {
        $productId = $request->input('product_id');
        $userId = Auth::id();
        
        $favorite = ProductFavorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
            
        if ($favorite) {
            $favorite->delete();
            return response()->json(['success' => true, 'is_favorite' => false]);
        } else {
            ProductFavorite::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            return response()->json(['success' => true, 'is_favorite' => true]);
        }
    }
}
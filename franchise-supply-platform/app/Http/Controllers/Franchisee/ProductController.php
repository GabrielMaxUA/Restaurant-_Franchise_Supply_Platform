<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of products for franchisees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    { 

        $query = Product::with(['category', 'images', 'variants.images']);
        
        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by inventory status
        if ($request->filled('inventory')) {
            switch ($request->inventory) {
                case 'in_stock':
                    $query->where('inventory_count', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('inventory_count', '>', 0)
                          ->where('inventory_count', '<=', 10);
                    break;
                case 'out_of_stock':
                    $query->where('inventory_count', 0);
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
                    break;
            }
        } else {
            // Default sorting by newest
            $query->orderBy('created_at', 'desc');
        }
        
        // Get the franchisee's favorites to mark products
        $franchisee = Auth::user();
        $favoriteIds = Favorite::where('franchisee_id', $franchisee->id)
            ->pluck('product_id')
            ->toArray();
        
        $products = $query->paginate(12);
        
        // Mark favorites
        foreach ($products as $product) {
            $product->is_favorite = in_array($product->id, $favoriteIds);
            
            // Convert inventory_count to stock_status for the views
            if ($product->inventory_count <= 0) {
                $product->stock_status = 'out_of_stock';
            } elseif ($product->inventory_count <= 10) {
                $product->stock_status = 'low_stock';
            } else {
                $product->stock_status = 'in_stock';
            }
            
            // Map base_price to price for consistency in views
            $product->price = $product->base_price;
            
            // Set stock_quantity from inventory_count
            $product->stock_quantity = $product->inventory_count;
        }
        
        $categories = Category::withCount('products')->get();
        $total_products = Product::count();
        
        return view('franchisee.catalog', compact('products', 'categories', 'total_products'));
    }
    
  /**
 * Display the specified product details.
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function show($id)
{
    $product = Product::with(['category', 'variants.images', 'images'])->findOrFail($id);
    
    // Convert inventory_count to stock_status for the views
    if ($product->inventory_count <= 0) {
        $product->stock_status = 'out_of_stock';
    } elseif ($product->inventory_count <= 10) {
        $product->stock_status = 'low_stock';
    } else {
        $product->stock_status = 'in_stock';
    }
    
    // Map base_price to price for consistency in views
    $product->price = $product->base_price;
    
    // Set stock_quantity from inventory_count
    $product->stock_quantity = $product->inventory_count;
    
    // Get related products from the same category
    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->take(4)
        ->get();
        
    return view('franchisee.product_details', compact('product', 'relatedProducts'));
}
    
    /**
     * Toggle favorite status for a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggleFavorite(Request $request)
    {
        $franchisee = Auth::user();
        $productId = $request->input('product_id');
        
        // Check if the product is already a favorite
        $favorite = Favorite::where('franchisee_id', $franchisee->id)
            ->where('product_id', $productId)
            ->first();
            
        if ($favorite) {
            // If it exists, remove it
            $favorite->delete();
            return response()->json([
                'success' => true,
                'is_favorite' => false,
                'message' => 'Product removed from favorites.'
            ]);
        } else {
            // If it doesn't exist, add it
            Favorite::create([
                'franchisee_id' => $franchisee->id,
                'product_id' => $productId
            ]);
            
            return response()->json([
                'success' => true,
                'is_favorite' => true,
                'message' => 'Product added to favorites!'
            ]);
        }
    }
    
    /**
     * Show favorites list.
     *
     * @return \Illuminate\Http\Response
     */
    public function showFavorites()
    {
        $franchisee = Auth::user();
        
        // Get favorites with products
        $favorites = Favorite::where('franchisee_id', $franchisee->id)
            ->with(['product.category', 'product.images'])
            ->get()
            ->pluck('product');
            
        // Process products for display
        foreach ($favorites as $product) {
            $product->is_favorite = true;
            
            // Convert inventory_count to stock_status for the views
            if ($product->inventory_count <= 0) {
                $product->stock_status = 'out_of_stock';
            } elseif ($product->inventory_count <= 10) {
                $product->stock_status = 'low_stock';
            } else {
                $product->stock_status = 'in_stock';
            }
            
            // Map base_price to price for consistency in views
            $product->price = $product->base_price;
            
            // Set stock_quantity from inventory_count
            $product->stock_quantity = $product->inventory_count;
        }
        
        return view('franchisee.favorites', compact('favorites'));
    }

/**
 * API method to get product details for mobile app
 * Ensures format matches what the mobile app expects
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function apiGetProductDetails($id)
{
    try {
        // Force JSON response even on errors
        request()->headers->set('Accept', 'application/json');
        
        // Find the product or return a JSON 404
        $product = Product::with(['category', 'variants.images', 'images'])->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        // Format the product data to match the expected structure in the mobile app
        $formattedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description ?? 'No description available',
            'price' => (float)$product->base_price,
            'base_price' => (float)$product->base_price,
            'inventory_count' => (int)$product->inventory_count,
            'stock_quantity' => (int)$product->inventory_count,
            'unit_size' => $product->unit_size ?? '',
            'unit_type' => $product->unit_type ?? '',
            'sku' => $product->sku ?? '',
            'min_order_quantity' => $product->min_order_quantity ?? 1,
            'category' => [
                'id' => $product->category->id ?? 0,
                'name' => $product->category->name ?? 'Uncategorized'
            ],
            'stock_status' => $product->inventory_count <= 0 ? 'out_of_stock' : 
                             ($product->inventory_count <= 10 ? 'low_stock' : 'in_stock'),
        ];
        
        // Handle images with absolute URLs for mobile app
        $formattedProduct['images'] = [];
        $formattedProduct['image_url'] = null;
        
        if ($product->images && $product->images->count() > 0) {
            // Format primary image URL to be fully qualified
            $primaryImage = $product->images->first();
            $formattedProduct['image_url'] = $this->getFullImageUrl($primaryImage->image_url);
            
            // Format all images with full URLs
            $formattedProduct['images'] = $product->images->map(function($image) {
                return [
                    'id' => $image->id,
                    'image_url' => $this->getFullImageUrl($image->image_url),
                    'is_primary' => $image->is_primary ?? false
                ];
            })->values()->all();
        }
        
        // Process variants with proper price handling
        $formattedProduct['variants'] = [];
        
        if ($product->variants && $product->variants->count() > 0) {
            $formattedProduct['variants'] = $product->variants->map(function($variant) use ($formattedProduct) {
                // For variants, handle images same way as products
                $variantImageUrl = null;
                $variantImages = [];
                
                if ($variant->images && $variant->images->count() > 0) {
                    $variantImageUrl = $this->getFullImageUrl($variant->images->first()->image_url);
                    
                    $variantImages = $variant->images->map(function($image) {
                        return [
                            'id' => $image->id,
                            'image_url' => $this->getFullImageUrl($image->image_url),
                            'is_primary' => $image->is_primary ?? false
                        ];
                    })->values()->all();
                } elseif ($variant->image_url) {
                    $variantImageUrl = $this->getFullImageUrl($variant->image_url);
                }
                
                // Handle price correctly - note we get either price_adjustment or direct price
                $variantPrice = 0;
                
                if (isset($variant->price) && is_numeric($variant->price)) {
                    $variantPrice = (float)$variant->price;
                } elseif (isset($variant->price_adjustment) && is_numeric($variant->price_adjustment)) {
                    $variantPrice = (float)$variant->price_adjustment;
                }
                
                return [
                    'id' => $variant->id,
                    'name' => $variant->name ?? 'Unnamed Variant',
                    'description' => $variant->description ?? $formattedProduct['description'],
                    'price' => $variantPrice,
                    'inventory_count' => (int)$variant->inventory_count,
                    'image_url' => $variantImageUrl,
                    'images' => $variantImages,
                    'is_purchasable' => $variant->is_purchasable ?? ($variant->inventory_count > 0),
                    'stock_status' => $variant->inventory_count <= 0 ? 'out_of_stock' : 
                                    ($variant->inventory_count <= 10 ? 'low_stock' : 'in_stock')
                ];
            })->values()->all();
        }
        
        // Format related products
        $relatedProducts = [];
        
        if ($product->category) {
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get()
                ->map(function($relatedProduct) {
                    $imageUrl = null;
                    if ($relatedProduct->images && $relatedProduct->images->count() > 0) {
                        $imageUrl = $this->getFullImageUrl($relatedProduct->images->first()->image_url);
                    }
                    
                    return [
                        'id' => $relatedProduct->id,
                        'name' => $relatedProduct->name,
                        'price' => (float)$relatedProduct->base_price,
                        'image_url' => $imageUrl
                    ];
                })->values()->all();
        }
        
        // Return JSON response with the expected structure
        return response()->json([
            'success' => true,
            'product' => $formattedProduct,
            'relatedProducts' => $relatedProducts
        ]);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('API Product Details Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'product_id' => $id
        ]);
        
        // Always return JSON, even for errors
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving product details: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper function to get full image URL
 * 
 * @param string|null $imagePath
 * @return string|null
 */
protected function getFullImageUrl($imagePath)
{
    if (!$imagePath) {
        return null;
    }
    
    // If it's already a full URL, return it
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        return $imagePath;
    }
    
    // If it starts with '/storage', add the app URL
    if (strpos($imagePath, '/storage') === 0) {
        return url($imagePath);
    }
    
    // Otherwise, assume it needs storage path
    return url('/storage/' . $imagePath);
}
}
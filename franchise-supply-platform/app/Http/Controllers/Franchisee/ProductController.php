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
        $query = Product::with(['category', 'images']);
        
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
        
        // Get the franchisee
        $franchisee = Auth::user();
        
        // Check if the product is a favorite
        $isFavorite = Favorite::where('franchisee_id', $franchisee->id)
            ->where('product_id', $product->id)
            ->exists();
            
        $product->is_favorite = $isFavorite;
        
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
}
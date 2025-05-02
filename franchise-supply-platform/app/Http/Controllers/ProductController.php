<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with(['category', 'variants'])->orderBy('name')->get();
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'inventory_count' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        // Handle variants if provided
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['name'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variant['name'],
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'inventory_count' => $variant['inventory_count'] ?? 0,
                    ]);
                }
            }
        }

        // Handle product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product-images', 'public');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $path,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'variants', 'images']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load(['variants', 'images']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'inventory_count' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        // Handle existing variants
        if ($request->has('existing_variants')) {
            foreach ($request->existing_variants as $id => $variantData) {
                $variant = ProductVariant::find($id);
                if ($variant) {
                    if (isset($variantData['delete']) && $variantData['delete']) {
                        $variant->delete();
                    } else {
                        $variant->update([
                            'name' => $variantData['name'],
                            'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                            'inventory_count' => $variantData['inventory_count'] ?? 0,
                        ]);
                    }
                }
            }
        }

        // Handle new variants
        if ($request->has('new_variants')) {
            foreach ($request->new_variants as $variant) {
                if (!empty($variant['name'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variant['name'],
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'inventory_count' => $variant['inventory_count'] ?? 0,
                    ]);
                }
            }
        }

        // Handle product images
        if ($request->hasFile('images')) {
          foreach ($request->file('images') as $image) {
              $path = $image->store('product-images', 'public');
              
              ProductImage::create([
                  'product_id' => $product->id,
                  'image_url' => $path,
              ]);
          }
      }

        // Handle image deletions
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image) {
                    // Delete the file from storage
                    if (Storage::disk('public')->exists($image->image_url)) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                    // Delete the database record
                    $image->delete();
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete related images from storage
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
        }

        // The database cascade will delete related records
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
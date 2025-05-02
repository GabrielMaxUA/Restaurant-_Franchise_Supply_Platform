<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

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
        // Log EVERYTHING to help debug
        Log::error('COMPLETE REQUEST DUMP: ' . json_encode([
            'all' => $request->all(),
            'hasFile_images' => $request->hasFile('images'),
            'hasFile_variant_image_0' => $request->hasFile('variant_image_0'),
            'files' => array_keys($request->allFiles()),
            'file_details' => array_map(function($file) {
                return [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ];
            }, $request->allFiles())
        ]));

        // Basic validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'inventory_count' => 'required|integer|min:0',
        ]);

        // Create product
        $product = Product::create($validated);
        
        // SUPER SIMPLE APPROACH - Create just one variant for testing
        if (!empty($request->variants[0]['name'])) {
            // Create variant directly
            $variant = new ProductVariant();
            $variant->product_id = $product->id;
            $variant->name = $request->variants[0]['name'];
            $variant->price_adjustment = $request->variants[0]['price_adjustment'] ?? 0;
            $variant->inventory_count = $request->variants[0]['inventory_count'] ?? 0;
            $variant->save();
            
            Log::error('CREATED VARIANT: ' . $variant->id);
            
            // Check for image in the most basic way possible
            if ($request->hasFile('variant_image_0')) {
                try {
                    $file = $request->file('variant_image_0');
                    
                    Log::error('FOUND VARIANT FILE: ' . $file->getClientOriginalName());
                    
                    // Make sure directory exists - CHANGED to use product-images
                    if (!Storage::disk('public')->exists('product-images')) {
                        Storage::disk('public')->makeDirectory('product-images');
                    }
                    
                    // Store file with explicit path - CHANGED to use product-images
                    $path = 'product-images/' . time() . '_variant_' . $file->getClientOriginalName();
                    $saved = Storage::disk('public')->put($path, file_get_contents($file));
                    
                    Log::error('FILE SAVED STATUS: ' . ($saved ? 'SUCCESS' : 'FAILED'));
                    
                    if ($saved) {
                        // Update variant with very direct database query to avoid any potential model issues
                        $updated = \DB::table('product_variants')
                            ->where('id', $variant->id)
                            ->update(['image_url' => $path]);
                            
                        Log::error('DB UPDATE STATUS: ' . ($updated ? 'SUCCESS' : 'FAILED'));
                        
                        // Verify result
                        $check = \DB::table('product_variants')->where('id', $variant->id)->first();
                        Log::error('VERIFIED IMAGE_URL: ' . ($check->image_url ?? 'NULL'));
                    }
                } catch (\Exception $e) {
                    Log::error('VARIANT IMAGE ERROR: ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                }
            } else {
                Log::error('NO VARIANT IMAGE FOUND FOR variant_image_0');
            }
        }
        
        // Handle product images for comparison
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                try {
                    $path = $image->store('product-images', 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $path,
                    ]);
                    
                    Log::error('PRODUCT IMAGE SAVED: ' . $path);
                } catch (\Exception $e) {
                    Log::error('PRODUCT IMAGE ERROR: ' . $e->getMessage());
                }
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
                        // If variant has an image, delete it
                        if ($variant->image_url && Storage::disk('public')->exists($variant->image_url)) {
                            Storage::disk('public')->delete($variant->image_url);
                        }
                        $variant->delete();
                    } else {
                        $variant->update([
                            'name' => $variantData['name'],
                            'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                            'inventory_count' => $variantData['inventory_count'] ?? 0,
                        ]);
                        
                        // Handle variant image update if provided
                        $inputName = "existing_variant_image_{$id}";
                        if ($request->hasFile($inputName)) {
                            // Delete old image if exists
                            if ($variant->image_url && Storage::disk('public')->exists($variant->image_url)) {
                                Storage::disk('public')->delete($variant->image_url);
                            }
                            
                            $this->storeVariantImage($variant, $request->file($inputName));
                        }
                    }
                }
            }
        }

        // Handle new variants
        if ($request->has('variants')) {
            foreach ($request->variants as $key => $variant) {
                if (!empty($variant['name'])) {
                    $variantModel = ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variant['name'],
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'inventory_count' => $variant['inventory_count'] ?? 0,
                    ]);
                    
                    // Check for variant image using direct named field
                    $inputName = "variant_image_{$key}";
                    if ($request->hasFile($inputName)) {
                        $this->storeVariantImage($variantModel, $request->file($inputName));
                    }
                }
            }
        }

        // Handle product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product-images', 'public');
                
                // Set proper permissions
                $fullPath = storage_path('app/public/' . $path);
                chmod($fullPath, 0644);
                
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
        
        // Delete variant images
        foreach ($product->variants as $variant) {
            if ($variant->image_url && Storage::disk('public')->exists($variant->image_url)) {
                Storage::disk('public')->delete($variant->image_url);
            }
        }

        // The database cascade will delete related records
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
    
    /**
     * Store a variant image and update the variant model.
     *
     * @param ProductVariant $variant
     * @param UploadedFile $image
     * @return void
     */
    private function storeVariantImage(ProductVariant $variant, UploadedFile $image)
    {
        try {
            // Ensure the directory exists - CHANGED to use product-images
            if (!Storage::disk('public')->exists('product-images')) {
                Storage::disk('public')->makeDirectory('product-images');
                Log::info("Created 'product-images' directory");
            }
            
            // Store the image - CHANGED to use product-images directory
            $filename = time() . '_variant_' . $image->getClientOriginalName();
            $path = 'product-images/' . $filename;
            
            // Use direct Storage put method instead of store
            $saved = Storage::disk('public')->put($path, file_get_contents($image));
            Log::info("Stored variant image at: {$path}, result: " . ($saved ? 'success' : 'failed'));
            
            // Set file permissions
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                chmod($fullPath, 0644);
                Log::info("Set permissions for: {$fullPath}");
            } else {
                Log::warning("File not found after storage: {$fullPath}");
            }
            
            // Update the variant with image URL directly using DB to bypass model
            $updated = \DB::table('product_variants')
                ->where('id', $variant->id)
                ->update(['image_url' => $path]);
            
            // Log the result
            Log::info("Updated variant ID {$variant->id} with image_url: {$path}, result: " . ($updated ? 'success' : 'failed'));
            
            // Verify the update was saved
            $check = \DB::table('product_variants')->where('id', $variant->id)->first();
            Log::info("Verified variant ID {$variant->id} image_url: " . ($check->image_url ?? 'NULL'));
        } catch (\Exception $e) {
            Log::error("Error storing variant image: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\VariantImage;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
  protected $inventoryService;

  /**
   * Create a new controller instance.
   *
   * @param InventoryService $inventoryService
   * @return void
   */
  public function __construct(InventoryService $inventoryService)
  {
      $this->inventoryService = $inventoryService;
  }
/**
 * Display a listing of the products.
 */
public function index(Request $request)
{
    $query = Product::with(['category', 'images', 'variants']);
    
    // Filter by name
    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }
    
    // Filter by category
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }
    
    // Check the route to determine which inventory filter to apply
    $routeName = $request->route()->getName();
    $isWarehouse = (strpos($routeName, 'warehouse.') === 0);
    
    // Filter by inventory status
    if ($request->filled('inventory')) {
        $this->applyInventoryFilter($query, $request->inventory, $isWarehouse);
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
            case 'inventory_asc':
                $query->orderBy('inventory_count', 'asc');
                break;
            case 'inventory_desc':
                $query->orderBy('inventory_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
                break;
        }
    } else {
        // Default sorting by newest
        $query->orderBy('created_at', 'desc');
    }
    
    $products = $query->paginate(15);
    
    // Check for available variants on out-of-stock products
    foreach ($products as $product) {
        // Check if any variants are in stock
        $hasInStockVariants = false;
        if ($product->variants->isNotEmpty()) {
            foreach ($product->variants as $variant) {
                if ($variant->inventory_count > 0) {
                    $hasInStockVariants = true;
                    break;
                }
            }
        }
        $product->has_in_stock_variants = $hasInStockVariants;
        
        // Set stock_status property for view 
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
    
    // Get inventory stats based on role
    $inventoryStats = $this->calculateInventoryStats($isWarehouse);
    
    $categories = Category::all();
    
    // Return the appropriate view based on role
    if ($isWarehouse) {
        return view('warehouse.products.index', 
            compact('products', 'categories') + $inventoryStats
        );
    }
    
    return view('admin.products.index', 
        compact('products', 'categories') + $inventoryStats
    );
}

/**
 * Apply inventory status filter to the query
 * 
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param string $inventoryStatus
 * @param bool $isWarehouse
 * @return void
 */
protected function applyInventoryFilter($query, $inventoryStatus, $isWarehouse = false)
{
    switch ($inventoryStatus) {
        case 'in_stock':
            if ($isWarehouse) {
                // Warehouse considers in_stock as inventory > 10
                $query->where(function($q) {
                    $q->where('inventory_count', '>', 10)
                      ->orWhereHas('variants', function($vq) {
                          $vq->where('inventory_count', '>', 10);
                      });
                });
            } else {
                // Admin considers in_stock as inventory > 0
                $query->where(function($q) {
                    $q->where('inventory_count', '>', 0)
                      ->orWhereHas('variants', function($vq) {
                          $vq->where('inventory_count', '>', 0);
                      });
                });
            }
            break;
        case 'low_stock':
            // This gets products where either:
            // 1. The main product has low stock (1-10 items)
            // 2. OR any of its variants has low stock (1-10 items)
            $query->where(function($q) {
                $q->where(function($mq) {
                    $mq->where('inventory_count', '>', 0)
                        ->where('inventory_count', '<=', 10);
                })
                ->orWhereHas('variants', function($vq) {
                    $vq->where('inventory_count', '>', 0)
                        ->where('inventory_count', '<=', 10);
                });
            });
            break;
        case 'out_of_stock':
            $query->where(function($q) {
                $q->where('inventory_count', 0)
                  ->orWhereHas('variants', function($vq) {
                      $vq->where('inventory_count', 0);
                  });
            });
            break;
    }
}

/**
 * Calculate inventory statistics
 * 
 * @param bool $isWarehouse
 * @return array
 */
protected function calculateInventoryStats($isWarehouse = false)
{
    // Low stock count
    $lowStockCount = Product::where(function($q) {
        $q->where(function($mq) {
            $mq->where('inventory_count', '>', 0)
               ->where('inventory_count', '<=', 10);
        })
        ->orWhereHas('variants', function($vq) {
            $vq->where('inventory_count', '>', 0)
               ->where('inventory_count', '<=', 10);
        });
    })->count();
    
    // Out of stock count
    $outOfStockCount = Product::where(function($q) {
        $q->where('inventory_count', 0)
          ->orWhereHas('variants', function($vq) {
              $vq->where('inventory_count', 0);
          });
    })->count();
    
    if ($isWarehouse) {
        // In stock count for warehouse (inventory > 0)
        $inStockCount = Product::where(function($q) {
            $q->where('inventory_count', '>', 0)
              ->orWhereHas('variants', function($vq) {
                  $vq->where('inventory_count', '>', 0);
              });
        })->count();
        
        return compact('inStockCount', 'lowStockCount', 'outOfStockCount');
    }
    
    return compact('lowStockCount', 'outOfStockCount');
}

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return view('warehouse.products.create', compact('categories'));
        }
        
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Compress and store an uploaded image using native PHP functions
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string Path to the stored image
     */
    private function compressAndStoreImage($file, $directory)
    {
        // Create unique filename
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        
        // Get image info
        $imageInfo = getimagesize($tempPath);
        if ($imageInfo === false) {
            // Not a valid image, just store original
            $path = $file->store($directory, 'public');
            return $path;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Calculate new dimensions if needed
        $maxDimension = 1024;
        $newWidth = $width;
        $newHeight = $height;
        
        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $newWidth = $maxDimension;
                $newHeight = intval($height * ($maxDimension / $width));
            } else {
                $newHeight = $maxDimension;
                $newWidth = intval($width * ($maxDimension / $height));
            }
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Load source image based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($tempPath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($tempPath);
                // Handle transparency
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($tempPath);
                break;
            default:
                // Unsupported format, store original
                $path = $file->store($directory, 'public');
                return $path;
        }
        
        // Copy and resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save compressed image
        $path = $directory . '/' . $filename;
        $fullPath = Storage::disk('public')->path($path);
        
        // Make sure the directory exists
        $directoryPath = dirname($fullPath);
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        
        // Save based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $fullPath, 80); // 80% quality
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $fullPath, 6); // Compression level 6 (0-9)
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $fullPath);
                break;
        }
        
        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $path;
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
            foreach ($request->variants as $index => $variantData) {
                if (!empty($variantData['name'])) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variantData['name'],
                        'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                        'inventory_count' => $variantData['inventory_count'] ?? 0,
                    ]);
                    
                    // Handle multiple variant images if provided
                    $variantImageKey = "variant_image_" . $index;
                    if ($request->hasFile($variantImageKey)) {
                        foreach ($request->file($variantImageKey) as $image) {
                            // Compress and store the image
                            $path = $this->compressAndStoreImage($image, 'variant-images');
                            
                            VariantImage::create([
                                'variant_id' => $variant->id,
                                'image_url' => $path,
                            ]);
                        }
                    }
                }
            }
        }
    
        // Handle product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Compress and store the image
                $path = $this->compressAndStoreImage($image, 'product-images');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $path,
                ]);
            }
        }
        
        // Log debug information to help troubleshoot issues
        \Log::debug('Product creation with variants', [
            'variants' => $request->has('variants') ? count($request->variants) : 0,
            'route' => $request->route()->getName(),
        ]);
        
        // Check if this is a warehouse route
        $routeName = $request->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return redirect()->route('warehouse.products.index')
                ->with('success', 'Product created successfully.');
        }
    
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show($product = null)
    {
        // Handle the case where the parameter isn't type-hinted as Product
        if (!($product instanceof Product)) {
            $product = Product::findOrFail($product);
        }
        
        $product->load(['category', 'variants.images', 'images']);
        
        // Add stock status metadata
        $hasInStockVariants = false;
        if ($product->variants->isNotEmpty()) {
            foreach ($product->variants as $variant) {
                if ($variant->inventory_count > 0) {
                    $hasInStockVariants = true;
                    break;
                }
            }
        }
        
        // Set stock_status based on inventory
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
        
        // Get related products for warehouse view
        $relatedProducts = null;
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get();
                
            return view('warehouse.products.show', compact('product', 'relatedProducts'));
        }
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($product = null)
    {
        // Handle the case where the parameter isn't type-hinted as Product
        if (!($product instanceof Product)) {
            $product = Product::findOrFail($product);
        }
        
        $categories = Category::orderBy('name')->get();
        $product->load(['variants.images', 'images']);
        
        // Check the route to determine which view to use
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return view('warehouse.products.edit', compact('product', 'categories'));
        }
        
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    /**
 * Update the specified product in storage.
 */
public function update(Request $request, $product = null)
{
    // Handle the case where the parameter isn't type-hinted as Product
    if (!($product instanceof Product)) {
        $product = Product::findOrFail($product);
    }

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
        // Log the structure of the existing variants data for debugging
        \Log::debug('Processing existing variants', [
            'data' => $request->input('existing_variants')
        ]);
        
        foreach ($request->existing_variants as $id => $variantData) {
            $variant = ProductVariant::find($id);
            if ($variant) {
                if (isset($variantData['delete']) && $variantData['delete']) {
                    // Delete related variant images before deleting variant
                    foreach ($variant->images as $image) {
                        if (Storage::disk('public')->exists($image->image_url)) {
                            Storage::disk('public')->delete($image->image_url);
                        }
                        $image->delete();
                    }
                    $variant->delete();
                    \Log::debug("Deleted variant ID: $id");
                } else {
                    $variant->update([
                        'name' => $variantData['name'],
                        'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                        'inventory_count' => $variantData['inventory_count'] ?? 0,
                    ]);
                    \Log::debug("Updated variant ID: $id", ['data' => $variantData]);
                    
                    // Handle variant image update
                    $variantImageKey = "variant_image_existing_" . $id;
                    
                    // Handle individual variant image deletion
                    if ($request->has('delete_variant_images')) {
                        foreach ($variant->images as $image) {
                            // Check if this specific image ID is in the deletion array
                            if (isset($request->delete_variant_images[$image->id])) {
                                if (Storage::disk('public')->exists($image->image_url)) {
                                    Storage::disk('public')->delete($image->image_url);
                                }
                                $image->delete();
                                \Log::debug("Deleted variant image ID: {$image->id}");
                            }
                        }
                    }
                    
                    // Add new images if provided
                    if ($request->hasFile($variantImageKey)) {
                        \Log::debug("Processing new images for variant $id", [
                            'file_count' => count($request->file($variantImageKey))
                        ]);
                        
                        foreach ($request->file($variantImageKey) as $image) {
                            // Compress and store the image
                            $path = $this->compressAndStoreImage($image, 'variant-images');
                            
                            VariantImage::create([
                                'variant_id' => $variant->id,
                                'image_url' => $path,
                            ]);
                        }
                    } else {
                        \Log::debug("No new images for variant $id");
                    }
                }
            } else {
                \Log::warning("Variant not found for ID: $id");
            }
        }
    } else {
        \Log::debug('No existing variants in request');
    }

    // Handle new variants
    if ($request->has('new_variants')) {
        \Log::debug('Processing new variants', [
            'count' => count($request->new_variants),
            'keys' => array_keys($request->new_variants),
            'data' => $request->new_variants
        ]);
        
        foreach ($request->new_variants as $index => $variant) {
            \Log::debug("Processing new variant index: $index", ['data' => $variant]);
            
            if (!empty($variant['name'])) {
                $newVariant = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variant['name'],
                    'price_adjustment' => $variant['price_adjustment'] ?? 0,
                    'inventory_count' => $variant['inventory_count'] ?? 0,
                ]);
                
                \Log::debug("Created new variant ID: {$newVariant->id}", [
                    'name' => $newVariant->name,
                    'price_adjustment' => $newVariant->price_adjustment,
                    'inventory_count' => $newVariant->inventory_count
                ]);
                
                // Handle multiple variant images if provided
                $variantImageKey = "variant_image_new_" . $index;
                if ($request->hasFile($variantImageKey)) {
                    \Log::debug("Processing images for new variant", [
                        'variant_id' => $newVariant->id,
                        'image_key' => $variantImageKey,
                        'file_count' => count($request->file($variantImageKey))
                    ]);
                    
                    foreach ($request->file($variantImageKey) as $image) {
                        // Compress and store the image
                        $path = $this->compressAndStoreImage($image, 'variant-images');
                        
                        $variantImage = VariantImage::create([
                            'variant_id' => $newVariant->id,
                            'image_url' => $path,
                        ]);
                        
                        \Log::debug("Created variant image", [
                            'variant_id' => $newVariant->id,
                            'image_id' => $variantImage->id,
                            'path' => $path
                        ]);
                    }
                } else {
                    \Log::debug("No images for new variant", [
                        'variant_id' => $newVariant->id,
                        'image_key' => $variantImageKey,
                        'has_file' => $request->hasFile($variantImageKey)
                    ]);
                    
                    // List all files in the request for debugging
                    $files = [];
                    foreach ($request->allFiles() as $key => $file) {
                        $files[] = $key;
                    }
                    \Log::debug("All files in request", ['files' => $files]);
                }
            } else {
                \Log::debug("Skipping empty variant at index $index");
            }
        }
    } else {
        \Log::debug('No new variants in request');
    }
    
    // Log debug information to help troubleshoot issues
    \Log::debug('Variant processing completed', [
        'existing_variants' => $request->has('existing_variants') ? array_keys($request->existing_variants) : [],
        'new_variants' => $request->has('new_variants') ? array_keys($request->new_variants) : [],
        'request_files' => $request->hasFile('variant_image_new_0') ? 'Has files' : 'No files',
    ]);

    // Handle product images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Compress and store the image
            $path = $this->compressAndStoreImage($image, 'product-images');
            
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

    // Check if this is a warehouse route
    $routeName = $request->route()->getName();
    if (strpos($routeName, 'warehouse.') === 0) {
        return redirect()->route('warehouse.products.index')
            ->with('success', 'Product updated successfully.');
    }
    
    return redirect()->route('admin.products.index')
        ->with('success', 'Product updated successfully.');
}

    /**
     * Remove the specified product from storage.
     */
    public function destroy($product = null)
    {
        // Handle the case where the parameter isn't type-hinted as Product
        if (!($product instanceof Product)) {
            $product = Product::findOrFail($product);
        }
        
        // Delete related images from storage
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }
        }
        
        // Delete variant images
        foreach ($product->variants as $variant) {
            foreach ($variant->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
        }
    
        // The database cascade will delete related records
        $product->delete();
        
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return redirect()->route('warehouse.products.index')
                ->with('success', 'Product deleted successfully.');
        }
    
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
    
    /**
     * Display products with low stock.
     */
    public function lowStock()
    {
        $products = Product::with(['category', 'variants', 'images'])
            ->where(function($q) {
                $q->where(function($mq) {
                    $mq->where('inventory_count', '>', 0)
                       ->where('inventory_count', '<=', 10);
                })
                ->orWhereHas('variants', function($vq) {
                    $vq->where('inventory_count', '>', 0)
                       ->where('inventory_count', '<=', 10);
                });
            })
            ->orderBy('inventory_count', 'asc')
            ->paginate(15);
        
        // Process products for display
        foreach ($products as $product) {
            // Check if any variants are in stock
            $hasInStockVariants = false;
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count > 0) {
                        $hasInStockVariants = true;
                        break;
                    }
                }
            }
            
            // Set stock_status property
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
        
        return view('warehouse.inventory.low-stock', compact('products'));
    }
    
    /**
     * Display products that are out of stock OR have out-of-stock variants.
     */
    public function outOfStock()
    {
        // Get products that are out of stock OR have out of stock variants
        $products = Product::with(['category', 'variants', 'images'])
            ->where(function($q) {
                $q->where('inventory_count', 0)
                  ->orWhereHas('variants', function($vq) {
                      $vq->where('inventory_count', 0);
                  });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
        
        // Process products for display and identify which have out-of-stock variants
        foreach ($products as $product) {
            $hasOutOfStockVariants = false;
            
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count == 0) {
                        $hasOutOfStockVariants = true;
                        $variant->is_out_of_stock = true;
                    } else {
                        $variant->is_out_of_stock = false;
                    }
                }
            }
            
            // Flag whether the main product, variants, or both are out of stock
            $product->has_out_of_stock_variants = $hasOutOfStockVariants;
            $product->main_is_out_of_stock = ($product->inventory_count == 0);
        }
        
        return view('warehouse.inventory.out-of-stock', compact('products'));
    }
        
    /**
     * Display most popular products.
     */
    public function mostPopular()
    {
        $products = Product::with(['category', 'variants', 'images'])
            ->select('products.*', \DB::raw('COUNT(order_items.id) as orders_count'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy(
                'products.id', 
                'products.name', 
                'products.description', 
                'products.base_price',
                'products.inventory_count', 
                'products.category_id', 
                'products.created_at', 
                'products.updated_at'
            )
            ->having('orders_count', '>', 0)
            ->orderBy('orders_count', 'desc')
            ->paginate(15);
        
        // Process products for display
        foreach ($products as $product) {
            // Check if any variants are in stock
            $hasInStockVariants = false;
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count > 0) {
                        $hasInStockVariants = true;
                        break;
                    }
                }
            }
            
            $product->has_in_stock_variants = $hasInStockVariants;
            
            // Set stock_status property
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
        
        return view('warehouse.inventory.popular', compact('products'));
    }
}
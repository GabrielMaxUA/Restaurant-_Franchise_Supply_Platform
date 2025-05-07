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
     
     $categories = Category::all();
     
     return view('admin.products.index', compact('products', 'categories'));
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
    
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'variants.images', 'images']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load(['variants.images', 'images']);
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
        
        // Get the original inventory count before updating
        $originalProductInventory = $product->inventory_count;
        
        // Update the product
        $product->update($validated);
        
        // Calculate inventory change and update if necessary
        $inventoryChange = $product->inventory_count - $originalProductInventory;
        
        if ($inventoryChange != 0) {
            if ($inventoryChange > 0) {
                // Inventory increased
                $this->inventoryService->increaseInventory($product->id, $inventoryChange);
            } else {
                // Inventory decreased
                $this->inventoryService->decreaseInventory($product->id, abs($inventoryChange));
            }
        }
    
        // Handle existing variants
        if ($request->has('existing_variants')) {
            foreach ($request->existing_variants as $id => $variantData) {
                $variant = ProductVariant::find($id);
                if ($variant) {
                    // Store original variant inventory
                    $originalVariantInventory = $variant->inventory_count;
                    
                    if (isset($variantData['delete']) && $variantData['delete']) {
                        // Delete related variant images before deleting variant
                        foreach ($variant->images as $image) {
                            if (Storage::disk('public')->exists($image->image_url)) {
                                Storage::disk('public')->delete($image->image_url);
                            }
                            $image->delete();
                        }
                        $variant->delete();
                    } else {
                        // Update the variant
                        $variant->update([
                            'name' => $variantData['name'],
                            'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                            'inventory_count' => $variantData['inventory_count'] ?? 0,
                        ]);
                        
                        // Calculate variant inventory change
                        $variantInventoryChange = $variant->inventory_count - $originalVariantInventory;
                        
                        if ($variantInventoryChange != 0) {
                            if ($variantInventoryChange > 0) {
                                // Variant inventory increased
                                $this->inventoryService->increaseInventory($product->id, $variantInventoryChange, $variant->id);
                            } else {
                                // Variant inventory decreased
                                $this->inventoryService->decreaseInventory($product->id, abs($variantInventoryChange), $variant->id);
                            }
                        }
                        
                        // Handle variant image update
                        $variantImageKey = "variant_image_existing_" . $id;
                        if ($request->hasFile($variantImageKey)) {
                            // Delete old images if deleting is requested
                            if (isset($request->delete_variant_images[$id])) {
                                foreach ($variant->images as $image) {
                                    if (Storage::disk('public')->exists($image->image_url)) {
                                        Storage::disk('public')->delete($image->image_url);
                                    }
                                    $image->delete();
                                }
                            }
                            
                            // Add new images
                            foreach ($request->file($variantImageKey) as $image) {
                                // Compress and store the image
                                $path = $this->compressAndStoreImage($image, 'variant-images');
                                
                                VariantImage::create([
                                    'variant_id' => $variant->id,
                                    'image_url' => $path,
                                ]);
                            }
                        } else if (isset($request->delete_variant_images[$id])) {
                            // Just delete images without adding new ones
                            foreach ($variant->images as $image) {
                                if (Storage::disk('public')->exists($image->image_url)) {
                                    Storage::disk('public')->delete($image->image_url);
                                }
                                $image->delete();
                            }
                        }
                    }
                }
            }
        }
    
        // Handle new variants
        if ($request->has('new_variants')) {
            foreach ($request->new_variants as $index => $variant) {
                if (!empty($variant['name'])) {
                    $newVariant = ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variant['name'],
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'inventory_count' => $variant['inventory_count'] ?? 0,
                    ]);
                    
                    // If new variant has inventory, update it through the service
                    if ($newVariant->inventory_count > 0) {
                        $this->inventoryService->increaseInventory($product->id, $newVariant->inventory_count, $newVariant->id);
                    }
                    
                    // Handle multiple variant images if provided
                    $variantImageKey = "variant_image_new_" . $index;
                    if ($request->hasFile($variantImageKey)) {
                        foreach ($request->file($variantImageKey) as $image) {
                            // Compress and store the image
                                $path = $this->compressAndStoreImage($image, 'variant-images');
                            
                            VariantImage::create([
                                'variant_id' => $newVariant->id,
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
            foreach ($variant->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
        }
    
        // The database cascade will delete related records
        $product->delete();
    
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
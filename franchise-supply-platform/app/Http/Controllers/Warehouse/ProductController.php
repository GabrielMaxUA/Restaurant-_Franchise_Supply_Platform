<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\VariantImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
          // Show products where EITHER:
          // 1. The base product has inventory > 10, OR
          // 2. Any of its variants has inventory > 10
          $query->where(function($q) {
              $q->where('inventory_count', '>', 10)
                ->orWhereHas('variants', function($vq) {
                    $vq->where('inventory_count', '>', 10);
                });
          });
          break;
      case 'low_stock':
          // Show products where EITHER:
          // 1. The base product has low stock (1-10 items), OR
          // 2. Any of its variants has low stock (1-10 items)
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
          // Show products where EITHER:
          // 1. The base product is out of stock, OR
          // 2. Any of its variants is out of stock
          $query->where(function($q) {
              $q->where('inventory_count', 0)
                ->orWhereHas('variants', function($vq) {
                    $vq->where('inventory_count', 0);
                });
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
        
        // Process each product for display
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
        
        // Get aggregated inventory stats for the view
        
        // In stock count - products with base inventory > 0 OR any variant with inventory > 0
        $inStockCount = Product::where(function($q) {
            $q->where('inventory_count', '>', 0)
              ->orWhereHas('variants', function($vq) {
                  $vq->where('inventory_count', '>', 0);
              });
        })->count();
        
        // Low stock count - products with base inventory between 1-10 OR any variant with inventory between 1-10
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
        
        // Out of stock count - products with base inventory = 0 AND either no variants OR all variants with inventory = 0
        $outOfStockCount = Product::where('inventory_count', 0)
            ->where(function($q) {
                $q->doesntHave('variants')
                  ->orWhereDoesntHave('variants', function($vq) {
                      $vq->where('inventory_count', '>', 0);
                  });
            })->count();
        
        $categories = Category::all();
        
        return view('warehouse.products.index', compact(
            'products', 
            'categories', 
            'inStockCount',
            'lowStockCount', 
            'outOfStockCount'
        ));
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
        
        // Check for available variants 
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
        
        // Get related products from the same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();
            
        return view('warehouse.products.show', compact('product', 'relatedProducts'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('warehouse.products.create', compact('categories'));
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
    
        return redirect()->route('warehouse.products.index')
            ->with('success', 'Product created successfully.');
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::with(['category', 'variants', 'images'])->findOrFail($id);
        $categories = Category::all();
        
        return view('warehouse.products.edit', compact('product', 'categories'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'inventory_count' => 'required|integer|min:0',
        ]);
    
        $product = Product::findOrFail($id);
        $product->update($validated);
    
        // Handle existing variants
        if ($request->has('existing_variants')) {
            foreach ($request->existing_variants as $variantId => $variantData) {
                $variant = ProductVariant::find($variantId);
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
                    } else {
                        $variant->update([
                            'name' => $variantData['name'],
                            'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                            'inventory_count' => $variantData['inventory_count'] ?? 0,
                        ]);
                        
                        // Handle variant image update
                        $variantImageKey = "variant_image_existing_" . $variantId;
                        
                        // Handle individual variant image deletion
                        if ($request->has('delete_variant_images')) {
                            foreach ($variant->images as $image) {
                                // Check if this specific image ID is in the deletion array
                                if (isset($request->delete_variant_images[$image->id])) {
                                    if (Storage::disk('public')->exists($image->image_url)) {
                                        Storage::disk('public')->delete($image->image_url);
                                    }
                                    $image->delete();
                                }
                            }
                        }
                        
                        // Add new images if provided
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
    
        return redirect()->route('warehouse.products.index')
            ->with('success', 'Product updated successfully.');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
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
    
        return redirect()->route('warehouse.products.index')
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
     * Display fully stocked products (inventory > 10).
     */
    public function inStock()
    {
        $products = Product::with(['category', 'variants', 'images'])
            ->where(function($q) {
                $q->where('inventory_count', '>', 10)
                  ->orWhereHas('variants', function($vq) {
                      $vq->where('inventory_count', '>', 10);
                  });
            })
            ->orderBy('inventory_count', 'desc')
            ->paginate(15);
        
        // Process products for display
        foreach ($products as $product) {
            // Check if any variants are in stock
            $hasInStockVariants = false;
            $hasFullyStockedVariants = false;
            
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count > 0) {
                        $hasInStockVariants = true;
                    }
                    if ($variant->inventory_count > 10) {
                        $hasFullyStockedVariants = true;
                    }
                }
            }
            
            $product->has_in_stock_variants = $hasInStockVariants;
            
            // Set stock_status property
            if ($product->inventory_count > 10 || $hasFullyStockedVariants) {
                $product->stock_status = 'in_stock';
            } elseif ($product->inventory_count > 0) {
                $product->stock_status = 'low_stock';
            } else {
                if ($hasInStockVariants) {
                    $product->stock_status = 'variants_only';
                } else {
                    $product->stock_status = 'out_of_stock';
                }
            }
        }
        
        return view('warehouse.inventory.in-stock', compact('products'));
    }
    
    /**
     * Display most popular products.
     */
    public function mostPopular()
    {
        $products = Product::with(['category', 'variants', 'images'])
            ->select('products.*', DB::raw('COUNT(order_items.id) as orders_count'))
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
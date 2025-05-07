<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Decrease product inventory count
     *
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return bool
     */
    public function decreaseInventory(int $productId, int $quantity, ?int $variantId = null): bool
    {
        DB::beginTransaction();
        try {
            if ($variantId) {
                // If variant exists, update both product and variant inventory
                $variant = ProductVariant::where('id', $variantId)
                    ->where('inventory_count', '>=', $quantity)
                    ->lockForUpdate()
                    ->first();
                
                if (!$variant) {
                    throw new \Exception('Insufficient variant inventory');
                }
                
                $variant->inventory_count -= $quantity;
                $variant->save();
                
                // Also decrease the main product inventory
                $product = Product::where('id', $productId)
                    ->where('inventory_count', '>=', $quantity)
                    ->lockForUpdate()
                    ->first();
                
                if (!$product) {
                    throw new \Exception('Insufficient product inventory');
                }
                
                $product->inventory_count -= $quantity;
                $product->save();
            } else {
                // Only update product inventory
                $product = Product::where('id', $productId)
                    ->where('inventory_count', '>=', $quantity)
                    ->lockForUpdate()
                    ->first();
                
                if (!$product) {
                    throw new \Exception('Insufficient product inventory');
                }
                
                $product->inventory_count -= $quantity;
                $product->save();
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory decrease failed: ' . $e->getMessage(), [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Increase product inventory count
     *
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return bool
     */
    public function increaseInventory(int $productId, int $quantity, ?int $variantId = null): bool
    {
        DB::beginTransaction();
        try {
            if ($variantId) {
                // If variant exists, update both product and variant inventory
                $variant = ProductVariant::where('id', $variantId)
                    ->lockForUpdate()
                    ->first();
                
                if (!$variant) {
                    throw new \Exception('Variant not found');
                }
                
                $variant->inventory_count += $quantity;
                $variant->save();
                
                // Also increase the main product inventory
                $product = Product::where('id', $productId)
                    ->lockForUpdate()
                    ->first();
                
                if (!$product) {
                    throw new \Exception('Product not found');
                }
                
                $product->inventory_count += $quantity;
                $product->save();
            } else {
                // Only update product inventory
                $product = Product::where('id', $productId)
                    ->lockForUpdate()
                    ->first();
                
                if (!$product) {
                    throw new \Exception('Product not found');
                }
                
                $product->inventory_count += $quantity;
                $product->save();
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory increase failed: ' . $e->getMessage(), [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
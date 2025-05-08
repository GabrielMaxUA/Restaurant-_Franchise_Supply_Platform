<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['cart_id', 'product_id', 'variant_id', 'quantity'];
    
    /**
     * Get the cart that owns the item.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    
    /**
     * Get the product for this cart item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the variant for this cart item.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    
    /**
     * Get the unit price for this item.
     */
    public function getUnitPriceAttribute()
    {
        if ($this->variant_id) {
            $variant = $this->variant;
            $product = $this->product;
            if ($variant && $product) {
                return $product->base_price + $variant->price_adjustment;
            }
        } else {
            $product = $this->product;
            if ($product) {
                return $product->base_price;
            }
        }
        
        return 0;
    }
    
    /**
     * Get the subtotal for this item.
     */
    public function getSubtotalAttribute()
    {
        return $this->unit_price * $this->quantity;
    }
}
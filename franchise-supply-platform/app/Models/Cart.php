<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id'];
    
    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the items in the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    
    /**
     * Get the total number of items in the cart.
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('quantity');
    }
    
    /**
     * Get the total price of all items in the cart.
     */
    public function getTotalPriceAttribute()
    {
        $total = 0;
        
        foreach ($this->items as $item) {
            if ($item->variant_id) {
                $variant = $item->variant;
                $product = $item->product;
                if ($variant && $product) {
                    $price = $product->base_price + $variant->price_adjustment;
                    $total += $price * $item->quantity;
                }
            } else {
                $product = $item->product;
                if ($product) {
                    $total += $product->base_price * $item->quantity;
                }
            }
        }
        
        return $total;
    }
}
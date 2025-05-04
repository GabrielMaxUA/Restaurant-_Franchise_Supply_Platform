<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'category_id',
        'inventory_count'
    ];

    // Disable timestamps since we don't have the updated_at column
    public $timestamps = false;

    // If you have a created_at column that should still be used
    protected $dates = [
        'created_at'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'product_favorites', 'product_id', 'user_id');
    }
    
    // Helper method to check if product is favorited by user
    public function isFavoritedBy($userId)
    {
        return $this->favoritedBy()->where('users.id', $userId)->exists();
    }

}
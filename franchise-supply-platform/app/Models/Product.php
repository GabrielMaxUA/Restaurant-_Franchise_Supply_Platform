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

    // Enable timestamps since we have both created_at and updated_at columns
    public $timestamps = true;

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
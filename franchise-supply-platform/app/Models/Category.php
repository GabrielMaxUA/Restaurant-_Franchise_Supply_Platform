<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // Disable timestamps since we don't have the updated_at column
    public $timestamps = false;

    // If you have a created_at column that should still be used
    protected $dates = [
        'created_at'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
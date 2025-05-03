<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'image_url'
    ];

    // Disable timestamps
    public $timestamps = false;

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
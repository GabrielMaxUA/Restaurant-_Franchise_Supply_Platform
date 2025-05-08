<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'email',
        'website',
        'tax_id',
        'logo_path',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the user that owns these admin details.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the formatted full address.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        
        if ($this->postal_code) {
            $address .= ' ' . $this->postal_code;
        }
        
        return $address;
    }
}
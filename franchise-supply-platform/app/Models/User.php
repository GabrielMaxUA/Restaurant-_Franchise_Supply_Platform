<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users'; // Explicitly set the table name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'phone',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the password for the user (mapping to password_hash field).
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relationship with the Role model.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relationship with the Order model.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get franchisee details associated with this user.
     */
    public function franchiseeDetail()
    {
        return $this->hasOne(FranchiseeDetail::class);
    }

    /**
     * Check if user is a franchisee.
     */
    public function isFranchisee()
    {
        return $this->role && $this->role->name === 'franchisee';
    }

    /**
     * Get the franchisee address from the related table
     */
    public function getAddressAttribute()
    {
        if ($this->franchiseeDetail) {
            return $this->franchiseeDetail->address;
        }
        return $this->attributes['address'] ?? null;
    }

    /**
     * Get the franchisee city from the related table
     */
    public function getCityAttribute()
    {
        return $this->franchiseeDetail ? $this->franchiseeDetail->city : null;
    }

    /**
     * Get the franchisee state from the related table
     */
    public function getStateAttribute()
    {
        return $this->franchiseeDetail ? $this->franchiseeDetail->state : null;
    }

    /**
     * Get the franchisee postal_code from the related table
     */
    public function getPostalCodeAttribute()
    {
        return $this->franchiseeDetail ? $this->franchiseeDetail->postal_code : null;
    }

    /**
     * Get the franchisee company name from the related table
     */
    public function getCompanyNameAttribute()
    {
        if ($this->franchiseeDetail) {
            return $this->franchiseeDetail->company_name;
        }
        return $this->attributes['company_name'] ?? null;
    }

    // In User.php
    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'product_favorites', 'user_id', 'product_id');
    }

    /**
     * Get the admin details associated with the user.
    */
    public function adminDetail()
    {
        return $this->hasOne(AdminDetail::class);
    }
}
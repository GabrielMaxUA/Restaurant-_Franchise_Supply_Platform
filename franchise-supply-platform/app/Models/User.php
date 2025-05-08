<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'updated_by',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the role that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the franchisee profile associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function franchiseeProfile()
    {
        return $this->hasOne(FranchiseeProfile::class);
    }

    /**
     * Compatibility method for the controller - returns the franchisee profile
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function franchisee()
    {
        return $this->franchiseeProfile();
    }

    /**
     * Get the cart associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get all orders for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Check if the user is a warehouse staff.
     *
     * @return bool
     */
    public function isWarehouse()
    {
        return $this->role && $this->role->name === 'warehouse';
    }

    /**
     * Check if the user is a franchisee.
     *
     * @return bool
     */
    public function isFranchisee()
    {
        return $this->role && $this->role->name === 'franchisee';
    }

    /**
     * Get or create a cart for this user.
     *
     * @return \App\Models\Cart
     */
    public function getOrCreateCart()
    {
        if (!$this->cart) {
            return $this->cart()->create();
        }
        
        return $this->cart;
    }

    /**
     * Get the user who updated this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user's full address as a string.
     *
     * @return string|null
     */
    public function getFullAddressAttribute()
    {
        if ($this->franchiseeProfile) {
            $profile = $this->franchiseeProfile;
            $addressParts = [
                $profile->address,
                $profile->city,
                $profile->state,
                $profile->postal_code
            ];
            
            return implode(', ', array_filter($addressParts));
        }
        
        return null;
    }

    /**
     * Get the user's company name (if any).
     * 
     * @return string|null
     */
    public function getCompanyNameAttribute()
    {
        return $this->franchiseeProfile ? $this->franchiseeProfile->company_name : null;
    }

    /**
     * Get the user's role name.
     *
     * @return string
     */
    public function getRoleNameAttribute()
    {
        return $this->role ? ucfirst($this->role->name) : 'Unknown';
    }

    /**
     * Get the badge class for the user's role.
     *
     * @return string
     */
    public function getRoleBadgeClassAttribute()
    {
        if (!$this->role) {
            return 'bg-secondary';
        }
        
        switch ($this->role->name) {
            case 'admin':
                return 'bg-danger';
            case 'warehouse':
                return 'bg-primary';
            case 'franchisee':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Scope a query to only include users of a given role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('role', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Search users by keyword in username, email, or phone.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('username', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%")
              ->orWhere('phone', 'like', "%{$keyword}%");
        });
    }

    /**
     * Search users by company name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $company
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByCompany($query, $company)
    {
        return $query->whereHas('franchiseeProfile', function ($q) use ($company) {
            $q->where('company_name', 'like', "%{$company}%");
        });
    }
}
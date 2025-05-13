<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
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
        'status',
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
        'role_id' => 'integer',
        'status' => 'boolean', // Cast status to boolean
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
     * Get the admin detail associated with the user.
     * Returns a default empty model if none exists.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function adminDetail()
    {
        return $this->hasOne(AdminDetail::class)->withDefault([
            'company_name' => config('company.name'),
            'address' => config('company.address'),
            'city' => config('company.city'),
            'state' => config('company.state'),
            'postal_code' => config('company.zip'),
            'website' => config('company.website'),
            'email' => $this->email,
            'phone' => $this->phone
        ]);
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
     * Get all notifications for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderNotifications()
    {
        return $this->hasMany(OrderNotification::class);
    }

    /**
     * Get all unread notifications for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unreadNotifications()
    {
        return $this->orderNotifications()->where('is_read', false);
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
     * Check if the user account is active.
     * Handles NULL values properly.
     *
     * @return bool
     */
    public function isActive()
    {
        // If status is null, use default value 1 (active)
        if (is_null($this->status)) {
            return true;
        }
        
        // Convert to int to ensure consistent comparison
        return (int)$this->status == true;
    }
    
    /**
     * Check if the user account is blocked.
     * Handles NULL values properly.
     *
     * @return bool
     */
    public function isBlocked()
    {
        // If status is null, use default value 1 (active)
        if (is_null($this->status)) {
            return false;
        }
        
        // Convert to int to ensure consistent comparison
        return (int)$this->status === 0;
    }
    
    /**
     * Scope a query to only include active users.
     * Includes both status=1 and status=NULL users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('status', 1)
              ->orWhereNull('status'); // Include NULL status as active (default is 1)
        });
    }
    
    /**
     * Scope a query to only include blocked users.
     * Only includes status=0 users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 0);
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
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->isActive() ? 'bg-success' : 'bg-danger';
    }

    /**
     * Get the status badge text.
     *
     * @return string
     */
    public function getStatusTextAttribute()
    {
        return $this->isActive() ? 'Active' : 'Blocked';
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role ? $this->role->name : null,
            'username' => $this->username
        ];
    }

    /**
     * Get all admin users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAdminUsers()
    {
        return self::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })->get();
    }

    /**
     * Get all warehouse users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getWarehouseUsers()
    {
        return self::whereHas('role', function($query) {
            $query->where('name', 'warehouse');
        })->get();
    }

    /**
     * Get admin email addresses
     *
     * @return array
     */
    public static function getAdminEmails()
    {
        return self::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })
        ->whereNotNull('email')
        ->pluck('email')
        ->toArray();
    }

    /**
     * Get warehouse email addresses
     *
     * @return array
     */
    public static function getWarehouseEmails()
    {
        return self::whereHas('role', function($query) {
            $query->where('name', 'warehouse');
        })
        ->whereNotNull('email')
        ->pluck('email')
        ->toArray();
    }
}
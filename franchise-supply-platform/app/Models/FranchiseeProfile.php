<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FranchiseeProfile extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * Keep using the existing franchisee_details table.
     *
     * @var string
     */
    protected $table = 'franchisee_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'city',
        'state',
        'postal_code',
        'contact_name',
        'logo_path',
        'updated_by',
        
    ];

    /**
     * Get the user that owns the franchisee profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the user profile information as an array for API responses
     * 
     * @return array
     */
    public function getAddressArray()
    {
        return [
            'company_name' => $this->company_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'contact_name' => $this->contact_name
        ];
    }
    
    /**
     * Check if the profile has a complete address
     * 
     * @return bool
     */
    public function hasCompleteAddress()
    {
        return !empty($this->address) && 
               !empty($this->city) && 
               !empty($this->state) && 
               !empty($this->postal_code);
    }
}
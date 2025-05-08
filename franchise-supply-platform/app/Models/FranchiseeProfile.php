<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseeProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'franchisee_profiles';
    
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
        'updated_by'
    ];
    
    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the user that last updated the profile.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
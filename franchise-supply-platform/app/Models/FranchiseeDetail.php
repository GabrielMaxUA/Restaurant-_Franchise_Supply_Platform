<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FranchiseeDetail extends Model
{
    use HasFactory;

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
    ];

    /**
     * Get the user that owns the franchisee details.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
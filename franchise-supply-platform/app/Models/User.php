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
}
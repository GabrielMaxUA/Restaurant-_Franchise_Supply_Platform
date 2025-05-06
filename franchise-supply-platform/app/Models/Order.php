<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'delivery_date',
        'delivery_time',
        'delivery_preference',
        'shipping_cost',
        'notes',
        'manager_name',
        'contact_phone',
        'payment_method',
        'purchase_order',
        'qb_invoice_id'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Get the formatted delivery time.
     */
    public function getFormattedDeliveryTimeAttribute()
    {
        $times = [
            'morning' => 'Morning (8:00 AM - 12:00 PM)',
            'afternoon' => 'Afternoon (12:00 PM - 4:00 PM)',
            'evening' => 'Evening (4:00 PM - 8:00 PM)',
        ];
        
        return $times[$this->delivery_time] ?? $this->delivery_time;
    }
    
    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }
    
    /**
     * Get the full shipping address.
     */
    public function getFullShippingAddressAttribute()
    {
        return "{$this->shipping_address}, {$this->shipping_city}, {$this->shipping_state} {$this->shipping_zip}";
    }
    
    /**
     * Get total items count.
     */
    public function getTotalItemsCountAttribute()
    {
        return $this->items->sum('quantity');
    }
}
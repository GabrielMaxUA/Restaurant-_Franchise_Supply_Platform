<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'shipped_at',
        'delivered_at',
        'approved_at',
        'tracking_number',
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
        'purchase_order',
        'qb_invoice_id',
        'invoice_number'
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => \App\Events\OrderSaved::class,
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
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
     * Get the notifications associated with the order.
     */
    public function notifications()
    {
        return $this->hasMany(OrderNotification::class);
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
    
    /**
     * Get the delivery date in user's timezone
     */
    public function getLocalDeliveryDateAttribute()
    {
      if (!$this->delivery_date) {
          return null;
      }
      
      $timezone = $this->user->timezone ?? 'America/Toronto'; // Default to Toronto timezone
      return Carbon::parse($this->delivery_date)->setTimezone($timezone);
  }

    /**
     * Get the created_at date in user's timezone
     */
    public function getLocalCreatedAtAttribute()
    {
        $timezone = $this->user->timezone ?? 'America/Toronto'; // Default to Toronto timezone
        return $this->created_at->setTimezone($timezone);
    }
    
    /**
     * Get formatted local created date
     */
    public function getFormattedLocalCreatedAtAttribute()
    {
        return $this->local_created_at->format('Y-m-d h:i A');
    }
    
    /**
     * Get formatted local delivery date
     */
    public function getFormattedLocalDeliveryDateAttribute()
    {
        return $this->local_delivery_date ? $this->local_delivery_date->format('Y-m-d') : 'Not scheduled';
    }
    
}
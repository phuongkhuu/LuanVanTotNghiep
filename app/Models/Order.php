<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    
    protected $fillable = [
        'user_id', 'discount_id', 'campaign_id', 'order_code', 'receiver_name',
        'receiver_phone', 'shipping_fee', 'total_amount', 'discount_amount',
        'final_amount', 'order_status', 'shipping_address', 'note'
    ];

    protected $casts = [
        'shipping_fee' => 'integer',
        'total_amount' => 'integer',
        'discount_amount' => 'integer',
        'final_amount' => 'integer',
        'order_status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
    
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
    
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
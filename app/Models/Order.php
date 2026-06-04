<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'discount_id', 'campaign_id', 'order_code', 'receiver_name',
        'receiver_phone', 'shipping_fee', 'total_amount', 'discount_amount',
        'final_amount', 'order_status', 'shipping_address', 'note'
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:0',
        'total_amount' => 'decimal:0',
        'discount_amount' => 'decimal:0',
        'final_amount' => 'decimal:0',
        'order_status' => 'integer',
    ];

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
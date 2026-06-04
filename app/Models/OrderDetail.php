<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_variant_id', 'quantity', 'unit_price', 'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:0',
        'subtotal' => 'decimal:0',
        'quantity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function logoPrintRequest()
    {
        return $this->hasOne(LogoPrintRequest::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_quantity',
        'discount_percent',
        'order_code',
        'type',
        'min_amount',
        'is_active'
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Thêm accessor cho order_code_label
    public function getOrderCodeLabelAttribute()
    {
        $labels = [
            'wholesale' => 'Bán sỉ',
            'event' => 'Sự kiện',
        ];
        
        return $labels[$this->order_code] ?? 'Chung';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
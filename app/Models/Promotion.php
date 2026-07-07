<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'target_type',
        'discount_type',
        'discount_value',
        'min_order',
        'limit',
        'used',
        'expiry',
        'active',
        'description',
        'product_id',
        'tiers',
        'current_buyers',
        'start_date',
        'end_date',
        'campaign_id'
    ];

    protected $casts = [
        'tiers' => 'array',
        'active' => 'boolean',
        'discount_value' => 'decimal:0',
        'min_order' => 'decimal:0',
        'expiry' => 'date',
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',    
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
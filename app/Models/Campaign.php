<?php
// app/Models/Campaign.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'start_time',
        'end_time',
        'status',
        'priority',
        'featured'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
    ];

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'campaign_products');
    }

    public function configs()
    {
        return $this->hasMany(CampaignConfig::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ĐÃ XÓA quan hệ với Banner

    public function getNameAttribute()
    {
        if ($this->attributes['name'] ?? false) {
            return $this->attributes['name'];
        }
        if ($this->start_time && $this->end_time) {
            $start = $this->start_time->format('d/m/Y');
            $end = $this->end_time->format('d/m/Y');
            return "Chiến dịch {$start} - {$end}";
        }
        return 'Chiến dịch';
    }

    public function getStartDateAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : null;
    }

    public function getEndDateAttribute()
    {
        return $this->end_time ? $this->end_time->format('Y-m-d') : null;
    }

    public function getDiscountAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->discount_percent . '%' : '0%';
    }

    public function getMinQuantityAttribute()
    {
        $config = $this->configs()->first();
        return $config ? $config->quantity : 0;
    }

    public function getProductIdsAttribute()
    {
        return $this->productVariants->pluck('id')->toArray();
    }
}
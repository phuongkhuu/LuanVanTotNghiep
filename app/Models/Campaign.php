<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'code',
        'target_type',
        'discount_type',
        'discount_value',
        'min_order',
        'limit',
        'used',
        'expiry',
        'description',
        'start_time',
        'end_time',
        'status',
        'banner',
        'priority',
        'featured',
        'product_id',
        'tiers',
        'current_buyers',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'featured' => 'boolean',
        'tiers' => 'array',
        'discount_value' => 'decimal:0',
        'min_order' => 'decimal:0',
        'expiry' => 'date',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // =============== THÊM QUAN HỆ VỚI BANNER ===============
    
    /**
     * Quan hệ với bảng banners (một campaign có nhiều banner)
     */
    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    /**
     * Lấy banner đầu tiên của campaign
     */
    public function banner()
    {
        return $this->hasOne(Banner::class)->latest();
    }

    /**
     * Lấy banner đang hoạt động của campaign
     */
    public function activeBanner()
    {
        return $this->hasOne(Banner::class)
            ->where('status', Banner::STATUS_ACTIVE)
            ->latest();
    }

    // =============== CÁC METHODS HIỆN CÓ ===============

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

    /**
     * Lấy URL banner cho campaign (ưu tiên từ bảng banners, fallback từ cột banner)
     */
    public function getBannerUrlAttribute()
    {
        // Ưu tiên lấy từ bảng banners
        $banner = $this->banners()->where('status', Banner::STATUS_ACTIVE)->first();
        if ($banner && $banner->image) {
            return $banner->image;
        }
        
        // Fallback: lấy từ cột banner cũ
        if ($this->attributes['banner'] ?? false) {
            return $this->attributes['banner'];
        }
        
        return null;
    }
}
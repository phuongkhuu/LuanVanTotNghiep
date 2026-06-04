<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = ['start_time', 'end_time'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }
}
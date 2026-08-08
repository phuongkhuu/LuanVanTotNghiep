<?php
// app/Models/ProductVariant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_id',
        'size_name',
        'rating',
        'stock',
        'price',
        'sale_price',
        'is_on_sale',
        'sale_type',
        'sale_campaign_id',
        'import_quantity',      
        'import_price',         
        'last_import_date',    
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'price' => 'decimal:0',
        'sale_price' => 'decimal:0',
        'stock' => 'integer',
        'import_quantity' => 'integer',     
        'import_price' => 'decimal:0',     
        'last_import_date' => 'datetime',   
        'is_on_sale' => 'boolean',
    ];

    // Quan hệ (giữ nguyên)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products');
    }

    public function quoteRequestDetails()
    {
        return $this->hasMany(QuoteRequestDetail::class);
    }

    public function scopeHasColor($query)
    {
        return $query->whereNotNull('color_id');
    }

    public function getProfitAttribute()
    {
        if ($this->import_price && $this->price) {
            return $this->price - $this->import_price;
        }
        return null;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->import_price && $this->price && $this->import_price != 0) {
            return round((($this->price - $this->import_price) / $this->import_price) * 100, 2);
        }
        return null;
    }
}
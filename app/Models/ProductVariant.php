<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'rating', 'stock', 'price'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'price' => 'decimal:0',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
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
}
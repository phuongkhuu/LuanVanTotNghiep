<?php
// app/Models/Brand.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
    ];

    // Relationship với Product (nếu có bảng products)
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relationship trực tiếp với ProductVariant thông qua Product
    public function productVariants()
    {
        return $this->hasManyThrough(ProductVariant::class, Product::class, 'brand_id', 'product_id');
    }
}
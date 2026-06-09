<?php
// app/Models/Color.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // Relationship với ProductVariant (một màu có nhiều biến thể sản phẩm)
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'color_id');
    }
}
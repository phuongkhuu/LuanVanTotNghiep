<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'material',
        'image_url', 'description', 'thumbnail', 'is_featured',
        'is_preorder', 'status'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_preorder' => 'boolean',
        'status' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
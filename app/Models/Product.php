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
        'image_url' => 'array',
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

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, ProductVariant::class, 'product_id', 'product_variant_id');
    }

    /**
     * Lấy ảnh đầu tiên của sản phẩm
     */
    public function getFirstImageAttribute()
    {
        if ($this->image_url && is_array($this->image_url) && count($this->image_url) > 0) {
            return $this->image_url[0];
        }
        return null;
    }

    /**
     * Lấy tất cả ảnh của sản phẩm
     */
    public function getImagesAttribute()
    {
        if ($this->image_url && is_array($this->image_url)) {
            return $this->image_url;
        }
        return [];
    }

    public function getThumbnailAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return $this->first_image;
    }
}
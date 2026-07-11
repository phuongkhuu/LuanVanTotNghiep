<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'image',
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quan hệ với sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Quan hệ cha (danh mục cha)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Quan hệ con (danh mục con)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Scope lấy danh mục đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope lấy danh mục cha
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessor lấy đường dẫn đầy đủ
    public function getFullPathAttribute()
    {
        $path = $this->name;
        $parent = $this->parent;
        while ($parent) {
            $path = $parent->name . ' > ' . $path;
            $parent = $parent->parent;
        }
        return $path;
    }
}
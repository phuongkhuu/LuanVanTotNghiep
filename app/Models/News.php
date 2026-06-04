<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id', 'author_id', 'title', 'slug', 'thumbnail', 'content'
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
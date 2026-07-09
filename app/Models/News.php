<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'content',
        'status',
        'author_name',
        'campaign_id',
        'banner_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }
    
    // Accessor để chuẩn hóa status
    public function getStatusAttribute($value)
    {
        return $value ? 1 : 0;
    }
}
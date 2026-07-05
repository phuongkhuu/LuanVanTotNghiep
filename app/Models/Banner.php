<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'image', 'link', 'status', 'order'];

    protected $casts = [
        'status' => 'integer',
        'order' => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
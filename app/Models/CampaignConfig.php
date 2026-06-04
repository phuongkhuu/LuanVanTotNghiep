<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignConfig extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'quantity', 'discount_percent'];

    protected $casts = [
        'discount_percent' => 'decimal:2',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
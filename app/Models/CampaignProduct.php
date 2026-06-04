<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CampaignProduct extends Pivot
{
    use HasFactory;

    protected $table = 'campaign_products';
    public $incrementing = true;

    protected $fillable = ['product_variant_id', 'campaign_id'];
}
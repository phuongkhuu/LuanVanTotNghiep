<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = ['quote_request_id', 'product_variant_id', 'quantity'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
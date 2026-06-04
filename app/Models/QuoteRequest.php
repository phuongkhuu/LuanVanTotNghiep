<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'email', 'phone', 'total_quantity',
        'total', 'requirement', 'logo_file', 'status'
    ];

    protected $casts = [
        'total' => 'decimal:0',
        'total_quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(QuoteRequestDetail::class);
    }
}
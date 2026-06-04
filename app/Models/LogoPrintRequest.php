<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogoPrintRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_detail_id', 'logo_image', 'print_position', 'print_size', 'note', 'status'
    ];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
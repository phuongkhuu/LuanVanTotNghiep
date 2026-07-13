<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'link',
        'campaign_id',
        'order',
        'status', // -1: Đã khóa, 0: Đang chờ, 1: Hoạt động
    ];

    // Constants cho trạng thái
    const STATUS_INACTIVE = -1;   // Đã khóa
    const STATUS_PENDING = 0;     // Đang chờ
    const STATUS_ACTIVE = 1;      // Hoạt động

    // Quan hệ với Campaign
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    // Scope: Lấy banner đang hoạt động (chỉ status = 1)
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope: Lấy banner đang chờ (status = 0)
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Scope: Lấy banner đã khóa (status = -1)
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInactive()
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    // Lấy label hiển thị cho status
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_PENDING => 'Đang chờ',
            self::STATUS_INACTIVE => 'Đã khóa',
            default => 'Không xác định',
        };
    }

    // Lấy màu cho status (dùng cho badge)
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_INACTIVE => 'danger',
            default => 'secondary',
        };
    }
}
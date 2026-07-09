<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'image', 
        'link', 
        'description',
        'status', 
        'order',
        'campaign_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    // Quan hệ với Campaign
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    // Scope lấy banner đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // Scope lấy banner theo campaign đang active hoặc scheduled
    public function scopeWithValidCampaign($query)
    {
        return $query->whereHas('campaign', function($q) {
            $q->whereIn('status', ['active', 'scheduled']);
        })->orWhereNull('campaign_id');
    }

    // Kiểm tra banner có hợp lệ để hiển thị không
    public function isValidToShow()
    {
        if (!$this->status) {
            return false;
        }

        if (!$this->campaign_id) {
            return true;
        }

        return in_array($this->campaign->status ?? '', ['active', 'scheduled']);
    }

    // Cập nhật trạng thái banner dựa trên campaign
    public function updateStatusByCampaign()
    {
        if ($this->campaign_id && $this->campaign) {
            if ($this->campaign->status === 'ended') {
                $this->update(['status' => false]);
                return true;
            }
        }
        return false;
    }
}
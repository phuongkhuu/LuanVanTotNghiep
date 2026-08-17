<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'discount_id',
        'campaign_id',
        'order_code',          
        'customer_name',
        'customer_phone',
        'customer_email', // Thêm nếu có
        'receiver_name',
        'receiver_phone',
        'shipping_address',
        'note',
        'shipping_fee',
        'total_amount',
        'discount_amount',
        'promo_code', 
        'final_amount',
        'deposit_amount',
        'remaining_amount',
        'payment_status',
        'order_status',    
        'confirmation_token',
        'token_expires_at',
        'is_confirmed',
        'order_number', // Thêm nếu chưa có
    ];

    protected $casts = [
        'shipping_fee'      => 'integer',
        'total_amount'      => 'integer',
        'discount_amount'   => 'integer',
        'final_amount'      => 'integer',
        'deposit_amount'    => 'integer',
        'remaining_amount'  => 'integer',
        'order_status'      => 'integer',
        'is_confirmed'      => 'boolean',
        'token_expires_at'  => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ========== RELATIONSHIPS ==========
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ========== STATUS HELPERS ==========
    public function getStatusTextAttribute()
    {
        return $this->getStatusText();
    }

    public function getStatusLabelAttribute()
    {
        return $this->getStatusLabel();
    }

    /**
     * Lấy mã trạng thái (dạng text) dựa trên order_code và order_status
     */
    public function getStatusText()
    {
        $orderCode = $this->order_code ?? 'retail';
        $status = $this->order_status;

        $maps = [
            'retail' => [
                0 => 'pending',
                1 => 'processing',
                2 => 'shipping',
                3 => 'completed',
                4 => 'cancelled',
            ],
            'wholesale' => [
                0 => 'pending',
                1 => 'approved',
                2 => 'production',
                3 => 'shipping',
                4 => 'completed',
                5 => 'cancelled',
            ],
            'preorder' => [
                0 => 'pending',
                1 => 'confirmed',
                2 => 'waiting',
                3 => 'shipping',
                4 => 'completed',
                5 => 'cancelled',
            ],
            // ===== THÊM CUSTOMIZE =====
            'customize' => [
                0 => 'pending',
                1 => 'approved',
                2 => 'rejected',
                3 => 'production',
                4 => 'completed',
            ],
        ];

        return $maps[$orderCode][$status] ?? 'pending';
    }

    /**
     * Lấy nhãn trạng thái hiển thị (tiếng Việt)
     */
    public function getStatusLabel()
    {
        $orderCode = $this->order_code ?? 'retail';
        $status = $this->order_status;

        $maps = [
            'retail' => [
                0 => 'Chờ xử lý',
                1 => 'Đang xử lý',
                2 => 'Đang giao',
                3 => 'Hoàn thành',
                4 => 'Đã hủy',
            ],
            'wholesale' => [
                0 => 'Chờ xác nhận',
                1 => 'Đã duyệt',
                2 => 'Đang sản xuất',
                3 => 'Đang giao',
                4 => 'Hoàn thành',
                5 => 'Đã hủy',
            ],
            'preorder' => [
                0 => 'Chờ xác nhận',
                1 => 'Đã xác nhận',
                2 => 'Chờ hàng',
                3 => 'Đang giao',
                4 => 'Hoàn thành',
                5 => 'Đã hủy',
            ],
            // ===== THÊM CUSTOMIZE (theo hình) =====
            'customize' => [
                0 => 'Chờ duyệt',
                1 => 'Đã duyệt',
                2 => 'Từ chối',
                3 => 'Đang SX',
                4 => 'Hoàn thành',
            ],
        ];

        return $maps[$orderCode][$status] ?? 'Chờ xử lý';
    }

    /**
     * Lấy map trạng thái (text => int) để cập nhật
     */
    public function getStatusMap()
    {
        $orderCode = $this->order_code ?? 'retail';

        $maps = [
            'retail' => [
                'pending'    => 0,
                'processing' => 1,
                'shipping'   => 2,
                'completed'  => 3,
                'cancelled'  => 4,
            ],
            'wholesale' => [
                'pending'    => 0,
                'approved'   => 1,
                'production' => 2,
                'shipping'   => 3,
                'completed'  => 4,
                'cancelled'  => 5,
            ],
            'preorder' => [
                'pending'    => 0,
                'confirmed'  => 1,
                'waiting'    => 2,
                'shipping'   => 3,
                'completed'  => 4,
                'cancelled'  => 5,
            ],
            // ===== THÊM CUSTOMIZE =====
            'customize' => [
                'pending'    => 0,
                'approved'   => 1,
                'rejected'   => 2,
                'production' => 3,
                'completed'  => 4,
            ],
        ];

        return $maps[$orderCode] ?? [];
    }

    // ========== TOKEN HELPERS ==========
    public function hasValidToken(): bool
    {
        if (empty($this->confirmation_token)) return false;
        if ($this->is_confirmed) return false;
        if ($this->token_expires_at && $this->token_expires_at->isPast()) return false;
        return true;
    }

    public function invalidateToken(): self
    {
        $this->confirmation_token = null;
        $this->token_expires_at = null;
        return $this;
    }

    public function generateToken(int $expireDays = 7): self
    {
        $this->confirmation_token = \Illuminate\Support\Str::random(64);
        $this->token_expires_at = now()->addDays($expireDays);
        $this->is_confirmed = false;
        return $this;
    }

    // ========== BOOT ==========
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $prefix = match ($order->order_code) {
                    'retail'    => 'L',
                    'wholesale' => 'S',
                    'preorder'  => 'P',
                    'customize' => 'C', // Thêm prefix cho customize
                    default     => 'DH',
                };
                $date = now()->format('dmy');
                $today = now()->toDateString();

                $lastOrder = static::whereDate('created_at', $today)
                    ->where('order_number', 'like', $prefix . $date . '%')
                    ->orderBy('order_number', 'desc')
                    ->first();

                $seq = $lastOrder ? str_pad((int) substr($lastOrder->order_number, -5) + 1, 5, '0', STR_PAD_LEFT) : '00001';
                $order->order_number = $prefix . $date . $seq;
            }

            // Tự động tạo token nếu chưa có và đơn hàng chưa được xác nhận
            if (empty($order->confirmation_token) && !$order->is_confirmed) {
                $order->generateToken();
            }
        });
    }
}
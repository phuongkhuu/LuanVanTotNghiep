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
        'order_code',           // 'retail', 'wholesale', 'preorder'
        'customer_name',
        'customer_phone',
        'receiver_name',
        'receiver_phone',
        'shipping_address',
        'note',
        'shipping_fee',
        'total_amount',
        'discount_amount',
        'final_amount',
        'order_status',         // int (0,1,2,... tùy loại)
    ];

    protected $casts = [
        'shipping_fee'   => 'integer',
        'total_amount'   => 'integer',
        'discount_amount'=> 'integer',
        'final_amount'   => 'integer',
        'order_status'   => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // ---------- RELATIONSHIPS ----------
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

    /**
     * Chi tiết đơn hàng (sản phẩm)
     */
    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Thanh toán (nếu bảng payments có khóa ngoại order_id)
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ---------- ACCESSORS (tuỳ chọn) ----------
    // Nếu bạn muốn lấy trạng thái dạng text ngay từ model, có thể thêm accessor
    // nhưng controller đã xử lý rồi, nên không bắt buộc.
    // Tôi vẫn thêm để dùng khi cần.

    public function getStatusTextAttribute()
    {
        return $this->getStatusText();
    }

    public function getStatusLabelAttribute()
    {
        return $this->getStatusLabel();
    }

    // ---------- HELPER METHODS ----------
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
        ];

        return $maps[$orderCode] ?? [];
    }
}
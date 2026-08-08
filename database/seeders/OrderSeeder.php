<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Lấy user đầu tiên (hoặc tạo mới)
        $user = DB::table('users')->first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Default User',
                'email' => 'user@bigbag.vn',
                'password' => bcrypt('password'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }

        $variants = DB::table('product_variants')->get();

        $customers = [
            ['name' => 'Nguyễn Văn A', 'phone' => '0901234567'],
            ['name' => 'Công ty TNHH ABC', 'phone' => '0987654321'],
            ['name' => 'Trần Thị B', 'phone' => '0912345678'],
            ['name' => 'Lê Văn C', 'phone' => '0934567890'],
            ['name' => 'Phạm Thị D', 'phone' => '0945678901'],
            ['name' => 'Doanh nghiệp XYZ', 'phone' => '0956789012'],
            ['name' => 'Ngô Văn E', 'phone' => '0967890123'],
            ['name' => 'Công ty TNHH Tech', 'phone' => '0978901234'],
            ['name' => 'Hoàng Thị F', 'phone' => '0981234567'],
            ['name' => 'Công ty CP Đầu tư', 'phone' => '0991234567'],
        ];

        $orderCodes = ['retail', 'wholesale', 'preorder'];
        $statuses = [0, 1, 2, 3, 4];

        // Mảng đếm số thứ tự theo từng loại và ngày
        $counters = [];

        // Tạo 30 đơn hàng mới (tổng sẽ là 80 đơn)
        for ($i = 0; $i < 30; $i++) {
            // Tạo ngày trong 30 ngày gần đây
            $createdAt = Carbon::now()->subDays(rand(0, 30))->setTime(rand(8, 22), rand(0, 59), rand(0, 59));

            // Chọn loại đơn hàng (có trọng số)
            $rand = rand(1, 10);
            if ($rand <= 5) $orderCode = 'retail';
            elseif ($rand <= 8) $orderCode = 'wholesale';
            else $orderCode = 'preorder';

            // Xác định prefix
            $prefix = match($orderCode) {
                'retail'    => 'L',
                'wholesale' => 'S',
                'preorder'  => 'P',
                default     => 'DH',
            };
            $dateKey = $createdAt->format('dmy'); // ddMMyy
            $counterKey = $prefix . $dateKey;

            // Tăng số thứ tự cho ngày và loại này
            if (!isset($counters[$counterKey])) {
                // Kiểm tra trong DB xem đã có đơn hàng nào cùng ngày và prefix chưa
                $lastOrder = DB::table('orders')
                    ->where('order_number', 'like', $prefix . $dateKey . '%')
                    ->orderBy('order_number', 'desc')
                    ->first();
                $counters[$counterKey] = $lastOrder ? (int) substr($lastOrder->order_number, -5) + 1 : 1;
            } else {
                $counters[$counterKey]++;
            }

            $seq = str_pad($counters[$counterKey], 5, '0', STR_PAD_LEFT);
            $orderNumber = $prefix . $dateKey . $seq;

            // Chọn khách hàng và người nhận
            $customer = $customers[array_rand($customers)];
            $receiver = $customers[array_rand($customers)];

            // Địa chỉ chi tiết hơn
            $districts = ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 11', 'Quận 12', 'Bình Thạnh', 'Gò Vấp', 'Tân Bình', 'Tân Phú'];
            $streets = ['Nguyễn Văn Cừ', 'Lê Lợi', 'Nguyễn Huệ', 'Điện Biên Phủ', 'Võ Văn Tần', 'Cách Mạng Tháng Tám', 'Trần Hưng Đạo', 'Nguyễn Trãi', 'Lý Thường Kiệt', 'Hai Bà Trưng'];

            $shippingAddress = $streets[array_rand($streets)] . ' ' . rand(1, 200) . ', ' . $districts[array_rand($districts)] . ', TP.HCM';

            // Tạo đơn hàng
            $orderId = DB::table('orders')->insertGetId([
                'user_id'          => $userId,
                'customer_name'    => $customer['name'],
                'customer_phone'   => $customer['phone'],
                'discount_id'      => null,
                'campaign_id'      => null,
                'order_code'       => $orderCode,
                'order_number'     => $orderNumber,
                'receiver_name'    => $receiver['name'],
                'receiver_phone'   => $receiver['phone'],
                'shipping_fee'     => $orderCode == 'retail' ? rand(20000, 50000) : 0,
                'total_amount'     => 0,
                'discount_amount'  => 0,
                'final_amount'     => 0,
                'order_status'     => $statuses[array_rand($statuses)],
                'shipping_address' => $shippingAddress,
                'note'             => rand(0, 1) ? 'Ghi chú đơn hàng #' . ($i + 51) : null,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            // Tạo chi tiết đơn hàng
            $numItems = rand(1, 4);
            $total = 0;
            $usedVariantIds = [];
            
            for ($j = 0; $j < $numItems; $j++) {
                $available = $variants->filter(fn($v) => !in_array($v->id, $usedVariantIds));
                if ($available->isEmpty()) break;
                $variant = $available->random();
                $usedVariantIds[] = $variant->id;

                $quantity = rand(1, 5);
                $unitPrice = $variant->price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                DB::table('order_details')->insert([
                    'order_id'           => $orderId,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $quantity,
                    'unit_price'         => $unitPrice,
                    'subtotal'           => $subtotal,
                    'created_at'         => $createdAt,
                    'updated_at'         => $createdAt,
                ]);
            }

            // Cập nhật tổng tiền
            $shippingFee = DB::table('orders')->where('id', $orderId)->value('shipping_fee');
            $final = $total + $shippingFee;
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'total_amount' => $total,
                    'final_amount' => $final,
                ]);
        }
    }
}
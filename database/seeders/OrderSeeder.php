<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Lấy user đầu tiên (hoặc tạo mới nếu chưa có)
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

        // Lấy danh sách sản phẩm (để tính tổng tiền sau)
        $variants = DB::table('product_variants')->get();

        // Danh sách khách hàng mẫu
        $customers = [
            ['name' => 'Nguyễn Văn A', 'phone' => '0901234567'],
            ['name' => 'Công ty TNHH ABC', 'phone' => '0987654321'],
            ['name' => 'Trần Thị B', 'phone' => '0912345678'],
            ['name' => 'Lê Văn C', 'phone' => '0934567890'],
            ['name' => 'Phạm Thị D', 'phone' => '0945678901'],
            ['name' => 'Doanh nghiệp XYZ', 'phone' => '0956789012'],
            ['name' => 'Ngô Văn E', 'phone' => '0967890123'],
            ['name' => 'Công ty TNHH Tech', 'phone' => '0978901234'],
        ];

        $orderCodes = ['retail', 'wholesale', 'preorder'];
        $statuses = [0, 1, 2, 3, 4];

        // Tạo 50 đơn hàng với ngày tháng ngẫu nhiên trong 30 ngày qua
        for ($i = 0; $i < 50; $i++) {
            // Chọn ngẫu nhiên ngày trong 30 ngày qua
            $createdAt = Carbon::now()->subDays(rand(0, 30))->setTime(rand(8, 22), rand(0, 59), rand(0, 59));

            // Chọn loại đơn hàng (ưu tiên retail nhiều hơn)
            $orderCode = $orderCodes[array_rand($orderCodes)];
            // Điều chỉnh tỉ lệ: retail 50%, wholesale 30%, preorder 20%
            $rand = rand(1, 10);
            if ($rand <= 5) $orderCode = 'retail';
            elseif ($rand <= 8) $orderCode = 'wholesale';
            else $orderCode = 'preorder';

            // Chọn khách hàng ngẫu nhiên
            $customer = $customers[array_rand($customers)];
            $receiver = $customers[array_rand($customers)];

            // Tạo đơn hàng (sau đó sẽ cập nhật total_amount và final_amount)
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderCode,
                'receiver_name' => $receiver['name'],
                'receiver_phone' => $receiver['phone'],
                'shipping_fee' => $orderCode == 'retail' ? rand(20000, 50000) : 0,
                'total_amount' => 0, // sẽ tính sau
                'discount_amount' => 0,
                'final_amount' => 0,  // sẽ tính sau
                'order_status' => $statuses[array_rand($statuses)],
                'shipping_address' => 'Địa chỉ ' . rand(1, 100) . ', Quận ' . rand(1, 12) . ', TP.HCM',
                'note' => rand(0, 1) ? 'Ghi chú đơn hàng ' . $i : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Tạo chi tiết đơn hàng (gọi riêng hoặc để OrderDetailSeeder xử lý)
            // Ở đây ta sẽ tạo trực tiếp để có thể tính total ngay
            $numItems = rand(1, 3);
            $total = 0;
            $usedVariantIds = [];
            for ($j = 0; $j < $numItems; $j++) {
                // Chọn variant ngẫu nhiên chưa dùng
                $variant = $variants->filter(function ($v) use ($usedVariantIds) {
                    return !in_array($v->id, $usedVariantIds);
                })->random();
                $usedVariantIds[] = $variant->id;

                $quantity = rand(1, 5);
                $unitPrice = $variant->price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                DB::table('order_details')->insert([
                    'order_id' => $orderId,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // Cập nhật total và final cho đơn hàng
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
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $userId = DB::table('users')->where('role', 'user')->value('id') ?? 1;
        $normalDiscountId = DB::table('discounts')->where('order_code', 'normal')->value('id');

        DB::table('orders')->insert([
            [
                'user_id' => $userId,
                'discount_id' => $normalDiscountId,
                'campaign_id' => null,
                'order_code' => 'normal',
                'receiver_name' => 'Nguyễn Văn A',
                'receiver_phone' => '0901234567',
                'shipping_fee' => 30000,
                'total_amount' => 1450000,
                'discount_amount' => 0,
                'final_amount' => 1480000,
                'order_status' => 2, // completed
                'shipping_address' => '123 Đường Láng, Đống Đa, Hà Nội',
                'note' => 'Gọi trước khi giao',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => 'wholesale',
                'receiver_name' => 'Công ty TNHH ABC',
                'receiver_phone' => '0987654321',
                'shipping_fee' => 0,
                'total_amount' => 2100000,
                'discount_amount' => 0,
                'final_amount' => 2100000,
                'order_status' => 1, // processing
                'shipping_address' => '456 Nguyễn Trãi, Quận 1, TP.HCM',
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Đảm bảo có ít nhất một user
        $user = DB::table('users')->first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Default User',
                'email' => 'user@bigbag.vn',
                'password' => Hash::make('password'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }

        // Discount có thể null
        $discount = DB::table('discounts')->where('order_code', 'retail')->first();
        $discountId = $discount ? $discount->id : null;

        DB::table('orders')->insert([
            [
                'user_id' => $userId,
                'discount_id' => $discountId,
                'campaign_id' => null,
                'order_code' => 'retail',
                'receiver_name' => 'Nguyễn Văn A',
                'receiver_phone' => '0901234567',
                'shipping_fee' => 30000,
                'total_amount' => 1450000,
                'discount_amount' => 0,
                'final_amount' => 1480000,
                'order_status' => 2,
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
                'order_status' => 1,
                'shipping_address' => '456 Nguyễn Trãi, Quận 1, TP.HCM',
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => 'preorder',
                'receiver_name' => 'Trần Thị B',
                'receiver_phone' => '0912345678',
                'shipping_fee' => 0,
                'total_amount' => 3500000,
                'discount_amount' => 0,
                'final_amount' => 3500000,
                'order_status' => 0,
                'shipping_address' => '789 Lê Lợi, Quận 3, TP.HCM',
                'note' => 'Giao hàng sau ngày 15/06',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
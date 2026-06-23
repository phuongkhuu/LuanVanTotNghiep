<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('discounts')->insert([
            [
                'min_quantity' => 50,
                'discount_percent' => 5.00,
                'order_code' => 'wholesale',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'min_quantity' => 100,
                'discount_percent' => 10.00,
                'order_code' => 'wholesale',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'min_quantity' => 200,
                'discount_percent' => 15.00,
                'order_code' => 'wholesale',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'min_quantity' => 1,
                'discount_percent' => 0,
                'order_code' => null,  // ← thay 'normal' bằng null
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
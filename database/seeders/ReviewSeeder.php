<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $users = DB::table('users')->where('role', 'user')->get();
        $productVariants = DB::table('product_variants')->get();

        if ($users->isEmpty() || $productVariants->isEmpty()) {
            return;
        }

        foreach ($productVariants as $variant) {
            DB::table('reviews')->insert([
                'user_id' => $users->random()->id,
                'product_variant_id' => $variant->id,
                'rating' => rand(3, 5),
                'comment' => 'Sản phẩm chất lượng tốt, đóng gói cẩn thận.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
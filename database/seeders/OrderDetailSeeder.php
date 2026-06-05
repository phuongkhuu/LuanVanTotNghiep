<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    public function run()
    {
        $orders = DB::table('orders')->get();
        $productVariants = DB::table('product_variants')->get();

        if ($orders->isEmpty() || $productVariants->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            $variant = $productVariants->random();
            $quantity = rand(1, 3);
            $unitPrice = $variant->price;
            DB::table('order_details')->insert([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
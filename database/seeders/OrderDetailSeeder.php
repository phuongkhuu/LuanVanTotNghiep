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

            $numProducts = rand(1, 3);
            $usedVariants = [];

            for ($i = 0; $i < $numProducts; $i++) {
                $variant = $productVariants->random();

                while (in_array($variant->id, $usedVariants) && $productVariants->count() > $usedVariants) {
                    $variant = $productVariants->random();
                }
                $usedVariants[] = $variant->id;

                $quantity = rand(1, 3);
                $unitPrice = $variant->price;
                $subtotal = $unitPrice * $quantity;

                DB::table('order_details')->insert([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
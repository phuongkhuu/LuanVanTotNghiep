<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $orders = DB::table('orders')->get();
        $methods = ['cod', 'bank_transfer', 'momo', 'vnpay'];
        $statuses = ['pending', 'success', 'failed'];

        foreach ($orders as $order) {
            if (rand(0, 1)) { // 50% số đơn có thanh toán
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'transaction_code' => 'TXN' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'payment_method' => $methods[array_rand($methods)],
                    'amount' => $order->final_amount,
                    'payment_date' => $order->created_at,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            }
        }
    }
}
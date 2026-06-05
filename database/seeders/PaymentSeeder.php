<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $orders = DB::table('orders')->get();

        foreach ($orders as $order) {
            DB::table('payments')->insert([
                'order_id' => $order->id,
                'transaction_code' => 'TXN' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                'payment_method' => $order->order_code === 'wholesale' ? 'bank_transfer' : 'cod',
                'amount' => $order->final_amount,
                'payment_date' => $order->created_at,
                'status' => $order->order_status == 2 ? 'success' : 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
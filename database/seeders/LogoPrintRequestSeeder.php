<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogoPrintRequestSeeder extends Seeder
{
    public function run()
    {
        $firstOrderDetailId = DB::table('order_details')->value('id');

        if (!$firstOrderDetailId) {
            return;
        }

        DB::table('logo_print_requests')->insert([
            'order_detail_id' => $firstOrderDetailId,
            'logo_image' => 'https://example.com/logos/logo1.png',
            'print_position' => 'front',
            'print_size' => 'medium',
            'note' => 'In màu trắng, kích thước 5x5cm',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
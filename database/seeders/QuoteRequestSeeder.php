<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteRequestSeeder extends Seeder
{
    public function run()
    {
        $userId = DB::table('users')->where('role', 'user')->value('id') ?? 1;

        DB::table('quote_requests')->insert([
            'user_id' => $userId,
            'company_name' => 'Công ty TNHH Giải Pháp Xanh',
            'email' => 'contact@green.com',
            'phone' => '0912345678',
            'total_quantity' => 150,
            'total' => null,
            'requirement' => 'Cần in logo công ty lên mặt trước balo, size 8x8cm',
            'logo_file' => null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
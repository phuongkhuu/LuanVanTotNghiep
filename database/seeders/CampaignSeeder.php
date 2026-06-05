<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignSeeder extends Seeder
{
    public function run()
    {
        DB::table('campaigns')->insert([
            [
                'start_time' => '2025-06-01 00:00:00',
                'end_time' => '2025-06-30 23:59:59',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'start_time' => '2025-07-01 00:00:00',
                'end_time' => '2025-07-31 23:59:59',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
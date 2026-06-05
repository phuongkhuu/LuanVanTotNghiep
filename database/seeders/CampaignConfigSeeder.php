<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignConfigSeeder extends Seeder
{
    public function run()
    {
        $campaigns = DB::table('campaigns')->pluck('id')->toArray();

        foreach ($campaigns as $campaignId) {
            DB::table('campaign_configs')->insert([
                [
                    'campaign_id' => $campaignId,
                    'quantity' => 1,
                    'discount_percent' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'campaign_id' => $campaignId,
                    'quantity' => 50,
                    'discount_percent' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'campaign_id' => $campaignId,
                    'quantity' => 100,
                    'discount_percent' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
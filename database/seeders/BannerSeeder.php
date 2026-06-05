<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run()
    {
        $firstCampaignId = DB::table('campaigns')->value('id');

        DB::table('banners')->insert([
            [
                'campaign_id' => $firstCampaignId,
                'image' => 'https://example.com/banners/summer-sale.jpg',
                'link' => '/khuyen-mai',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'campaign_id' => $firstCampaignId,
                'image' => 'https://example.com/banners/preorder.jpg',
                'link' => '/san-pham?preorder=true',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
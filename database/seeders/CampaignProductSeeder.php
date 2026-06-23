<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignProductSeeder extends Seeder
{
    public function run()
    {
        $productVariants = DB::table('product_variants')->pluck('id')->toArray();
        $campaigns = DB::table('campaigns')->pluck('id')->toArray();

        if (empty($productVariants) || empty($campaigns)) {
            return;
        }

        foreach ($campaigns as $campaignId) {

            $assigned = array_slice($productVariants, 0, 2);
            foreach ($assigned as $variantId) {
                DB::table('campaign_products')->insert([
                    'product_variant_id' => $variantId,
                    'campaign_id' => $campaignId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
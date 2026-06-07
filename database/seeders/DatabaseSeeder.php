<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            UserSeeder::class,
            DiscountSeeder::class,
            CampaignSeeder::class,
            CampaignProductSeeder::class,
            CampaignConfigSeeder::class,
            BannerSeeder::class,
            OrderSeeder::class,
            OrderDetailSeeder::class,
            PaymentSeeder::class,
            ReviewSeeder::class,
            NewsSeeder::class,
            QuoteRequestSeeder::class,
            QuoteRequestDetailSeeder::class,
            LogoPrintRequestSeeder::class,
            ChatbotMessageSeeder::class,
        ]);
    }
}
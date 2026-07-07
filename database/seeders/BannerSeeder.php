<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run()
    {
        DB::table('banners')->insert([
            [
                'title' => 'Summer Sale',
                'image' => 'https://example.com/banners/summer-sale.jpg',
                'link' => '/khuyen-mai',
                'description' => 'Chương trình khuyến mãi mùa hè',
                'status' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Preorder',
                'image' => 'https://example.com/banners/preorder.jpg',
                'link' => '/san-pham?preorder=true',
                'description' => 'Chương trình đặt trước',
                'status' => true,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
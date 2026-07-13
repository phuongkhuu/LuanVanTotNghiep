<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;
use App\Models\Campaign;

class BannerSeeder extends Seeder
{
    public function run()
    {
        // Tạo campaign nếu chưa có
        $campaign = Campaign::firstOrCreate(
            ['name' => 'Campaign Summer 2024'],
            [
                'status' => 'active',
                'type' => 'normal',
                'start_time' => now(),
                'end_time' => now()->addDays(30),
            ]
        );

        // Tạo banners với các trạng thái khác nhau
        $banners = [
            [
                'title' => 'Banner mùa hè 2024',
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=1200&h=585&fit=crop',
                'link' => 'https://example.com/summer-sale',
                'status' => 1, // Hoạt động
                'order' => 0,
            ],
            [
                'title' => 'Banner khuyến mãi lớn',
                'image' => 'https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?w=1200&h=585&fit=crop',
                'link' => 'https://example.com/big-sale',
                'status' => 1, // Hoạt động
                'order' => 1,
            ],
            [
                'title' => 'Banner sắp ra mắt',
                'image' => 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=1200&h=585&fit=crop',
                'link' => 'https://example.com/coming-soon',
                'status' => 0, // Đang chờ
                'order' => 2,
            ],
            [
                'title' => 'Banner cũ đã khóa',
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=1200&h=585&fit=crop',
                'link' => 'https://example.com/old-campaign',
                'status' => -1, // Đã khóa
                'order' => 3,
            ],
        ];

        foreach ($banners as $bannerData) {
            Banner::create(array_merge($bannerData, ['campaign_id' => $campaign->id]));
        }

        $this->command->info('Đã tạo ' . count($banners) . ' banners mẫu!');
    }
}

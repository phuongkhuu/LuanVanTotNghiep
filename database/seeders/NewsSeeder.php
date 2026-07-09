<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Campaign;
use App\Models\Banner;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy campaign và banner đầu tiên để làm dữ liệu mẫu
        $campaign = Campaign::whereNotIn('type', ['voucher', 'preorder'])->first();
        $banner = Banner::whereHas('campaign', function($query) {
            $query->whereNotIn('type', ['voucher', 'preorder']);
        })->first();

        if (!$campaign || !$banner) {
            // Nếu không có campaign/banner, tạo dữ liệu mẫu
            $news = [
                [
                    'title' => 'BigBag ra mắt dòng sản phẩm mới - Balo chống sốc cao cấp',
                    'slug' => 'bigbag-ra-mat-dong-san-pham-moi',
                    'thumbnail' => 'https://example.com/news/thumb1.jpg',
                    'content' => '<p>BigBag vừa cho ra mắt dòng balo chống sốc mới với công nghệ đệm tổ ong, bảo vệ laptop tối đa...</p>',
                    'status' => 1,
                    'author_name' => 'BigBag Admin',
                    'campaign_id' => null,
                    'banner_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Chương trình khuyến mãi hè: Giảm giá lên đến 30% cho đơn hàng sỉ',
                    'slug' => 'chuong-trinh-khuyen-mai-he',
                    'thumbnail' => 'https://example.com/news/thumb2.jpg',
                    'content' => '<p>Áp dụng cho tất cả khách hàng doanh nghiệp từ ngày 1/6 đến 30/6...</p>',
                    'status' => 1,
                    'author_name' => 'BigBag Admin',
                    'campaign_id' => null,
                    'banner_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
            
            News::insert($news);
            return;
        }

        // Tạo dữ liệu mẫu với campaign và banner có sẵn
        $news = [
            [
                'title' => 'BigBag ra mắt dòng sản phẩm mới - Balo chống sốc cao cấp',
                'slug' => 'bigbag-ra-mat-dong-san-pham-moi',
                'thumbnail' => $banner->image ?? 'https://example.com/news/thumb1.jpg',
                'content' => '<p>BigBag vừa cho ra mắt dòng balo chống sốc mới với công nghệ đệm tổ ong, bảo vệ laptop tối đa...</p>',
                'status' => 1,
                'author_name' => 'BigBag Admin',
                'campaign_id' => $campaign->id,
                'banner_id' => $banner->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Chương trình khuyến mãi hè: Giảm giá lên đến 30% cho đơn hàng sỉ',
                'slug' => 'chuong-trinh-khuyen-mai-he',
                'thumbnail' => $banner->image ?? 'https://example.com/news/thumb2.jpg',
                'content' => '<p>Áp dụng cho tất cả khách hàng doanh nghiệp từ ngày 1/6 đến 30/6...</p>',
                'status' => 1,
                'author_name' => 'BigBag Admin',
                'campaign_id' => $campaign->id,
                'banner_id' => $banner->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        News::insert($news);
    }
}
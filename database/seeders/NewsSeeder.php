<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run()
    {
        $adminId = DB::table('users')->where('role', 'admin')->value('id') ?? 1;
        $firstVariantId = DB::table('product_variants')->value('id');

        DB::table('news')->insert([
            [
                'product_variant_id' => $firstVariantId,
                'author_id' => $adminId,
                'title' => 'BigBag ra mắt dòng sản phẩm mới - Balo chống sốc cao cấp',
                'slug' => 'bigbag-ra-mat-dong-san-pham-moi',
                'thumbnail' => 'https://example.com/news/thumb1.jpg',
                'content' => '<p>BigBag vừa cho ra mắt dòng balo chống sốc mới với công nghệ đệm tổ ong, bảo vệ laptop tối đa...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_variant_id' => null,
                'author_id' => $adminId,
                'title' => 'Chương trình khuyến mãi hè: Giảm giá lên đến 30% cho đơn hàng sỉ',
                'slug' => 'chuong-trinh-khuyen-mai-he',
                'thumbnail' => 'https://example.com/news/thumb2.jpg',
                'content' => '<p>Áp dụng cho tất cả khách hàng doanh nghiệp từ ngày 1/6 đến 30/6...</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
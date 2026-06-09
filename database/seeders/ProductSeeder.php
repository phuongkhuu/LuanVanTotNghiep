<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Tạo danh mục
        $categories = [
            ['name' => 'Balo Laptop', 'slug' => 'balo-laptop', 'description' => 'Balo chuyên dụng cho laptop các size'],
            ['name' => 'Balo Du lịch', 'slug' => 'balo-du-lich', 'description' => 'Balo đi phượt, du lịch dung tích lớn'],
            ['name' => 'Túi đeo chéo', 'slug' => 'tui-deo-cheo', 'description' => 'Túi đeo chéo thời trang'],
        ];
        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Tạo thương hiệu
        $brands = [
            ['name' => 'BigBag', 'logo' => null, 'description' => 'Thương hiệu chính'],
            ['name' => 'Solo', 'logo' => null, 'description' => 'Solo Professional'],
            ['name' => 'KingBag', 'logo' => null, 'description' => 'KingBag'],
        ];
        foreach ($brands as $brand) {
            Brand::updateOrCreate(['name' => $brand['name']], $brand);
        }

        // Tạo màu sắc (cột 'name' sau khi đã rename)
        $colors = ['Đen', 'Xám', 'Xanh Navy', 'Nâu', 'Đỏ'];
        foreach ($colors as $colorName) {
            Color::updateOrCreate(['name' => $colorName], ['name' => $colorName]);
        }

        // Lấy ID các bản ghi
        $catLaptop = Category::where('slug', 'balo-laptop')->first();
        $catTravel = Category::where('slug', 'balo-du-lich')->first();
        $catCrossbody = Category::where('slug', 'tui-deo-cheo')->first();

        $brandBigbag = Brand::where('name', 'BigBag')->first();
        $brandSolo = Brand::where('name', 'Solo')->first();
        $brandKing = Brand::where('name', 'KingBag')->first();

        // Lấy màu sắc
        $colorBlack = Color::where('name', 'Đen')->first();
        $colorGray = Color::where('name', 'Xám')->first();
        $colorBrown = Color::where('name', 'Nâu')->first();

        // Tạo sản phẩm 1: Balo Laptop BigBag Pro 15.6 inch
        $product1 = Product::updateOrCreate(
            ['slug' => 'balo-laptop-bigbag-pro-15-6'],
            [
                'category_id' => $catLaptop->id,
                'brand_id' => $brandBigbag->id,
                'name' => 'Balo Laptop BigBag Pro 15.6 inch',
                'material' => 'Ballistic Nylon 1680D',
                'image_url' => 'https://example.com/images/product1.jpg',
                'description' => 'Balo chống nước cao cấp, ngăn laptop riêng biệt.',
                'thumbnail' => 'https://example.com/images/product1-thumb.jpg',
                'is_featured' => true,
                'is_preorder' => false,
                'status' => 1,
            ]
        );

        // Biến thể cho product1 – dùng size_name (string)
        ProductVariant::updateOrCreate(
            [
                'product_id' => $product1->id,
                'color_id' => $colorBlack->id,
                'size_name' => 'M'
            ],
            [
                'price' => 1450000,
                'stock' => 50,
                'rating' => 4.8,
            ]
        );
        ProductVariant::updateOrCreate(
            [
                'product_id' => $product1->id,
                'color_id' => $colorGray->id,
                'size_name' => 'M'
            ],
            [
                'price' => 1450000,
                'stock' => 30,
                'rating' => 4.7,
            ]
        );

        // Sản phẩm 2: Balo du lịch Solo Adventure 40L
        $product2 = Product::updateOrCreate(
            ['slug' => 'solo-adventure-40l'],
            [
                'category_id' => $catTravel->id,
                'brand_id' => $brandSolo->id,
                'name' => 'Solo Adventure 40L',
                'material' => 'Polyester 600D',
                'image_url' => 'https://example.com/images/product2.jpg',
                'description' => 'Balo du lịch dung tích lớn, nhiều ngăn tiện lợi.',
                'thumbnail' => 'https://example.com/images/product2-thumb.jpg',
                'is_featured' => true,
                'is_preorder' => false,
                'status' => 1,
            ]
        );

        ProductVariant::updateOrCreate(
            [
                'product_id' => $product2->id,
                'color_id' => $colorBlack->id,
                'size_name' => 'L'
            ],
            [
                'price' => 2100000,
                'stock' => 25,
                'rating' => 4.9,
            ]
        );

        // Sản phẩm 3: Túi đeo chéo KingBag
        $product3 = Product::updateOrCreate(
            ['slug' => 'kingbag-crossbody-mini'],
            [
                'category_id' => $catCrossbody->id,
                'brand_id' => $brandKing->id,
                'name' => 'KingBag Crossbody Mini',
                'material' => 'Da PU cao cấp',
                'image_url' => 'https://example.com/images/product3.jpg',
                'description' => 'Túi đeo chéo thời trang, thiết kế tối giản.',
                'thumbnail' => 'https://example.com/images/product3-thumb.jpg',
                'is_featured' => false,
                'is_preorder' => true,
                'status' => 1,
            ]
        );

        ProductVariant::updateOrCreate(
            [
                'product_id' => $product3->id,
                'color_id' => $colorBrown->id,
                'size_name' => 'Free'
            ],
            [
                'price' => 450000,
                'stock' => 100,
                'rating' => 4.5,
            ]
        );
    }
}
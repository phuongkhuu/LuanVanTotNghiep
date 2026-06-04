<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Size;
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
            Category::create($cat);
        }

        // Tạo thương hiệu
        $brands = [
            ['name' => 'BigBag', 'logo' => null, 'description' => 'Thương hiệu chính'],
            ['name' => 'Solo', 'logo' => null, 'description' => 'Solo Professional'],
            ['name' => 'KingBag', 'logo' => null, 'description' => 'KingBag'],
        ];
        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Tạo kích thước
        $sizes = ['S', 'M', 'L', 'XL', 'Free'];
        foreach ($sizes as $size) {
            Size::create(['size' => $size]);
        }

        // Tạo màu sắc
        $colors = ['Đen', 'Xám', 'Xanh Navy', 'Nâu', 'Đỏ'];
        foreach ($colors as $color) {
            Color::create(['color' => $color]);
        }

        // Lấy ID các bản ghi vừa tạo
        $catLaptop = Category::where('slug', 'balo-laptop')->first();
        $catTravel = Category::where('slug', 'balo-du-lich')->first();
        $brandBigbag = Brand::where('name', 'BigBag')->first();
        $brandSolo = Brand::where('name', 'Solo')->first();

        $sizeM = Size::where('size', 'M')->first();
        $sizeL = Size::where('size', 'L')->first();
        $colorBlack = Color::where('color', 'Đen')->first();
        $colorGray = Color::where('color', 'Xám')->first();

        // Tạo sản phẩm 1: Balo Laptop BigBag Pro
        $product1 = Product::create([
            'category_id' => $catLaptop->id,
            'brand_id' => $brandBigbag->id,
            'name' => 'Balo Laptop BigBag Pro 15.6 inch',
            'slug' => 'balo-laptop-bigbag-pro-15-6',
            'material' => 'Ballistic Nylon 1680D',
            'image_url' => 'https://example.com/images/product1.jpg',
            'description' => 'Balo chống nước cao cấp, ngăn laptop riêng biệt.',
            'thumbnail' => 'https://example.com/images/product1-thumb.jpg',
            'is_featured' => true,
            'is_preorder' => false,
            'status' => 1,
        ]);

        // Biến thể cho product1
        ProductVariant::create([
            'product_id' => $product1->id,
            'color_id' => $colorBlack->id,
            'size_id' => $sizeM->id,
            'rating' => 4.8,
            'stock' => 50,
            'price' => 1450000,
        ]);
        ProductVariant::create([
            'product_id' => $product1->id,
            'color_id' => $colorGray->id,
            'size_id' => $sizeM->id,
            'rating' => 4.7,
            'stock' => 30,
            'price' => 1450000,
        ]);

        // Sản phẩm 2: Balo du lịch Solo Adventure
        $product2 = Product::create([
            'category_id' => $catTravel->id,
            'brand_id' => $brandSolo->id,
            'name' => 'Solo Adventure 40L',
            'slug' => 'solo-adventure-40l',
            'material' => 'Polyester 600D',
            'image_url' => 'https://example.com/images/product2.jpg',
            'description' => 'Balo du lịch dung tích lớn, nhiều ngăn tiện lợi.',
            'thumbnail' => 'https://example.com/images/product2-thumb.jpg',
            'is_featured' => true,
            'is_preorder' => false,
            'status' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product2->id,
            'color_id' => $colorBlack->id,
            'size_id' => $sizeL->id,
            'rating' => 4.9,
            'stock' => 25,
            'price' => 2100000,
        ]);

        // Thêm sản phẩm 3: Túi đeo chéo KingBag
        $catCrossbody = Category::where('slug', 'tui-deo-cheo')->first();
        $brandKing = Brand::where('name', 'KingBag')->first();
        $sizeFree = Size::where('size', 'Free')->first();
        $colorBrown = Color::where('color', 'Nâu')->first();

        $product3 = Product::create([
            'category_id' => $catCrossbody->id,
            'brand_id' => $brandKing->id,
            'name' => 'KingBag Crossbody Mini',
            'slug' => 'kingbag-crossbody-mini',
            'material' => 'Da PU cao cấp',
            'image_url' => 'https://example.com/images/product3.jpg',
            'description' => 'Túi đeo chéo thời trang, thiết kế tối giản.',
            'thumbnail' => 'https://example.com/images/product3-thumb.jpg',
            'is_featured' => false,
            'is_preorder' => true,
            'status' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product3->id,
            'color_id' => $colorBrown->id,
            'size_id' => $sizeFree->id,
            'rating' => 4.5,
            'stock' => 100,
            'price' => 450000,
        ]);
    }
}
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {

        $categories = [
            [
                'name' => 'Balo Laptop',
                'slug' => 'balo-laptop',
                'description' => 'Balo chuyên dụng cho laptop các size',
                'image' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=500',
            ],
            [
                'name' => 'Balo Du lịch',
                'slug' => 'balo-du-lich',
                'description' => 'Balo đi phượt, du lịch dung tích lớn',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500',
            ],
            [
                'name' => 'Túi đeo chéo',
                'slug' => 'tui-deo-cheo',
                'description' => 'Túi đeo chéo thời trang',
                'image' => 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=500',
            ],
            [
                'name' => 'Balo thời trang',
                'slug' => 'balo-thoi-trang',
                'description' => 'Balo thiết kế đẹp, dùng hàng ngày',
                'image' => 'https://images.unsplash.com/photo-1594221708778-0f5e0b6d9a7a?w=500',
            ],
            [
                'name' => 'Balo chống sốc',
                'slug' => 'balo-chong-soc',
                'description' => 'Balo có đệm bảo vệ laptop, máy tính bảng',
                'image' => 'https://images.unsplash.com/photo-1601924582970-9238bcb495d4?w=500',
            ],
        ];
        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }


        $brands = [
            ['name' => 'BigBag', 'logo' => 'https://logo.clearbit.com/bigbag.com', 'description' => 'Thương hiệu chính'],
            ['name' => 'Solo', 'logo' => 'https://logo.clearbit.com/solo.com', 'description' => 'Solo Professional'],
            ['name' => 'KingBag', 'logo' => 'https://logo.clearbit.com/kingbag.com', 'description' => 'KingBag'],
            ['name' => 'Everki', 'logo' => 'https://logo.clearbit.com/everki.com', 'description' => 'Balo cao cấp Mỹ'],
            ['name' => 'Targus', 'logo' => 'https://logo.clearbit.com/targus.com', 'description' => 'Chuyên gia balo doanh nghiệp'],
            ['name' => 'Samsonite', 'logo' => 'https://file.hstatic.net/200000321545/file/logo_samsonite_4fcb80dea8d547f781ddff7aebfd59a5.svg', 'description' => 'Thương hiệu du lịch toàn cầu'],
        ];
        foreach ($brands as $brand) {
            $brand['slug'] = Str::slug($brand['name']);
            Brand::updateOrCreate(['slug' => $brand['slug']], $brand);
        }


        $colors = ['Đen', 'Xám', 'Xanh Navy', 'Nâu', 'Đỏ', 'Xanh lá', 'Cam', 'Trắng'];
        foreach ($colors as $colorName) {
            Color::updateOrCreate(['name' => $colorName], ['name' => $colorName]);
        }


        $catLaptop = Category::where('slug', 'balo-laptop')->first();
        $catTravel = Category::where('slug', 'balo-du-lich')->first();
        $catCrossbody = Category::where('slug', 'tui-deo-cheo')->first();
        $catFashion = Category::where('slug', 'balo-thoi-trang')->first();
        $catShock = Category::where('slug', 'balo-chong-soc')->first();

        $brandBigbag = Brand::where('name', 'BigBag')->first();
        $brandSolo = Brand::where('name', 'Solo')->first();
        $brandKing = Brand::where('name', 'KingBag')->first();
        $brandEverki = Brand::where('name', 'Everki')->first();
        $brandTargus = Brand::where('name', 'Targus')->first();
        $brandSamsonite = Brand::where('name', 'Samsonite')->first();

        $colorBlack = Color::where('name', 'Đen')->first();
        $colorGray = Color::where('name', 'Xám')->first();
        $colorNavy = Color::where('name', 'Xanh Navy')->first();
        $colorBrown = Color::where('name', 'Nâu')->first();
        $colorRed = Color::where('name', 'Đỏ')->first();
        $colorGreen = Color::where('name', 'Xanh lá')->first();
        $colorOrange = Color::where('name', 'Cam')->first();
        $colorWhite = Color::where('name', 'Trắng')->first();



        Product::updateOrCreate(
            ['slug' => 'balo-laptop-bigbag-pro-15-6'],
            [
                'category_id' => $catLaptop->id,
                'brand_id' => $brandBigbag->id,
                'name' => 'Balo Laptop BigBag Pro 15.6 inch',
                'material' => 'Ballistic Nylon 1680D',
                'image_url' => 'https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-12.png?v=1754064998880',
                'description' => 'Balo chống nước cao cấp, ngăn laptop riêng biệt.',
                'thumbnail' => 'https://bizweb.dktcdn.net/100/044/266/products/balo-laptop-du-lich-cao-cap-thiet-ke-da-nang-tich-hop-cong-usb-fix-laptop-15-6-inch-mark-ryden-compacto-pro-12.png?v=1754064998880',
                'is_featured' => true,
                'is_preorder' => false,
                'status' => 1,
            ]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => Product::where('slug', 'balo-laptop-bigbag-pro-15-6')->first()->id, 'color_id' => $colorBlack->id, 'size_name' => 'M'],
            ['price' => 1450000, 'stock' => 50, 'rating' => 4.8]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => Product::where('slug', 'balo-laptop-bigbag-pro-15-6')->first()->id, 'color_id' => $colorGray->id, 'size_name' => 'M'],
            ['price' => 1450000, 'stock' => 30, 'rating' => 4.7]
        );


        Product::updateOrCreate(
            ['slug' => 'solo-adventure-40l'],
            [
                'category_id' => $catTravel->id,
                'brand_id' => $brandSolo->id,
                'name' => 'Solo Adventure 40L',
                'material' => 'Polyester 600D',
                'image_url' => 'https://wetrek.vn/pic/products/balo-leo-nui-senterlan-adventure-45-5l-s1009-green_639002717478433706-w.450-q.80.jpg',
                'description' => 'Balo du lịch dung tích lớn, nhiều ngăn tiện lợi.',
                'thumbnail' => 'https://wetrek.vn/pic/products/balo-leo-nui-senterlan-adventure-45-5l-s1009-green_639002717478433706-w.200-q.80.jpg',
                'is_featured' => true,
                'is_preorder' => false,
                'status' => 1,
            ]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => Product::where('slug', 'solo-adventure-40l')->first()->id, 'color_id' => $colorBlack->id, 'size_name' => 'L'],
            ['price' => 2100000, 'stock' => 25, 'rating' => 4.9]
        );


        Product::updateOrCreate(
            ['slug' => 'kingbag-crossbody-mini'],
            [
                'category_id' => $catCrossbody->id,
                'brand_id' => $brandKing->id,
                'name' => 'KingBag Crossbody Mini',
                'material' => 'Da PU cao cấp',
                'image_url' => 'https://product.hstatic.net/200000273565/product/1__1__298a6cabc69943318235e40bfbced192_master.jpg',
                'description' => 'Túi đeo chéo thời trang, thiết kế tối giản.',
                'thumbnail' => 'https://product.hstatic.net/200000273565/product/1__1__298a6cabc69943318235e40bfbced192_master.jpg',
                'is_featured' => false,
                'is_preorder' => true,
                'status' => 1,
            ]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => Product::where('slug', 'kingbag-crossbody-mini')->first()->id, 'color_id' => $colorBrown->id, 'size_name' => 'Free'],
            ['price' => 450000, 'stock' => 100, 'rating' => 4.5]
        );


        $newProducts = [
            [
                'name' => 'Everki Atlas 17.3 inch',
                'slug' => 'everki-atlas-17-3',
                'category' => $catShock,
                'brand' => $brandEverki,
                'material' => 'Ballistic Nylon 1680D',
                'image_url' => 'https://www.everki.com/media/catalog/product/cache/ce976a4921f47273e3ea74f8ffb4648f/e/k/ekp121_01.jpg',
                'thumbnail' => 'https://www.everki.com/media/catalog/product/cache/ce976a4921f47273e3ea74f8ffb4648f/e/k/ekp121_01.jpg',
                'description' => 'Balo chống sốc cao cấp dành cho laptop 17.3 inch, nhiều ngăn phụ kiện.',
                'is_featured' => true,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorBlack, 'size' => 'L', 'price' => 2350000, 'stock' => 40, 'rating' => 4.9],
                    ['color' => $colorGray, 'size' => 'L', 'price' => 2350000, 'stock' => 25, 'rating' => 4.8],
                ]
            ],
            [
                'name' => 'Targus CitySmart 15.6 inch',
                'slug' => 'targus-citysmart-15-6',
                'category' => $catFashion,
                'brand' => $brandTargus,
                'material' => 'Polyester 600D',
                'image_url' => 'https://www.maccenter.vn/Bags/Targus-CitySmart-Professional-A.jpg',
                'thumbnail' => 'https://www.maccenter.vn/Bags/Targus-CitySmart-Professional-A.jpg',
                'description' => 'Balo thời trang, nhẹ, chống nước nhẹ, phù hợp văn phòng.',
                'is_featured' => true,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorNavy, 'size' => 'M', 'price' => 1250000, 'stock' => 60, 'rating' => 4.7],
                ]
            ],
            [
                'name' => 'Samsonite Lite 20L',
                'slug' => 'samsonite-lite-20l',
                'category' => $catTravel,
                'brand' => $brandSamsonite,
                'material' => 'Nylon 900D',
                'image_url' => 'https://cdn.deporvillage.com/cdn-cgi/image/h=960,w=768,dpr=1,f=auto,q=75,fit=contain,background=white/product-vertical/SSN-155708-1041_004.jpg',
                'thumbnail' => 'https://cdn.deporvillage.com/cdn-cgi/image/h=960,w=768,dpr=1,f=auto,q=75,fit=contain,background=white/product-vertical/SSN-155708-1041_004.jpg',
                'description' => 'Balo du lịch siêu nhẹ, chống nước, phong cách hiện đại.',
                'is_featured' => false,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorBlack, 'size' => 'M', 'price' => 1890000, 'stock' => 35, 'rating' => 4.8],
                    ['color' => $colorGray, 'size' => 'M', 'price' => 1890000, 'stock' => 20, 'rating' => 4.7],
                ]
            ],
            [
                'name' => 'Balo thời trang nữ BigBag',
                'slug' => 'bigbag-fashion-women',
                'category' => $catFashion,
                'brand' => $brandBigbag,
                'material' => 'Vải Canvas',
                'image_url' => 'https://bizweb.dktcdn.net/100/044/266/files/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4.png?v=1746292069590',
                'thumbnail' => 'https://bizweb.dktcdn.net/100/044/266/files/balo-thoi-trang-nu-tinh-fix-laptop-mong-nhe-14-1-inch-bopai-lady-pink-4.png?v=1746292069590',
                'description' => 'Balo thời trang dành cho nữ, nhiều màu sắc trẻ trung.',
                'is_featured' => true,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorRed, 'size' => 'S', 'price' => 550000, 'stock' => 80, 'rating' => 4.6],
                    ['color' => $colorWhite, 'size' => 'S', 'price' => 550000, 'stock' => 70, 'rating' => 4.5],
                ]
            ],
            [
                'name' => 'KingBag Backpack Pro',
                'slug' => 'kingbag-backpack-pro',
                'category' => $catLaptop,
                'brand' => $brandKing,
                'material' => 'Polyester 1200D',
                'image_url' => 'https://cdn2.fptshop.com.vn/unsafe/1920x0/filters:format(webp):quality(75)/balo_kingbag_zelos_kbg_138_156_inch_2_9a6a4d817c.jpg',
                'thumbnail' => 'https://cdn2.fptshop.com.vn/unsafe/1920x0/filters:format(webp):quality(75)/balo_kingbag_zelos_kbg_138_156_inch_2_9a6a4d817c.jpg',
                'description' => 'Balo laptop chuyên dụng cho dân văn phòng, thiết kế tối giản.',
                'is_featured' => false,
                'is_preorder' => true,
                'variants' => [
                    ['color' => $colorBlack, 'size' => 'M', 'price' => 990000, 'stock' => 45, 'rating' => 4.4],
                ]
            ],
            [
                'name' => 'Everki Flight 14L',
                'slug' => 'everki-flight-14l',
                'category' => $catShock,
                'brand' => $brandEverki,
                'material' => 'Ballistic Nylon 1680D',
                'image_url' => 'https://encrypted-tbn3.gstatic.com/shopping?q=tbn:ANd9GcTnqJEEcyv7_MjeOp9-qGQbIkHVQId4DrrUkv47NUWG2S9oDo4a2MpjyJbqOxdxqH6AkbkYH7_Kqt_mdQ2V6NzbMhx6yJws',
                'thumbnail' => 'https://encrypted-tbn3.gstatic.com/shopping?q=tbn:ANd9GcTnqJEEcyv7_MjeOp9-qGQbIkHVQId4DrrUkv47NUWG2S9oDo4a2MpjyJbqOxdxqH6AkbkYH7_Kqt_mdQ2V6NzbMhx6yJws',
                'description' => 'Balo chống sốc cỡ nhỏ dành cho laptop 14 inch, rất gọn nhẹ.',
                'is_featured' => false,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorOrange, 'size' => 'S', 'price' => 1850000, 'stock' => 15, 'rating' => 4.9],
                ]
            ],
            [
                'name' => 'Targus Groove X',
                'slug' => 'targus-groove-x',
                'category' => $catCrossbody,
                'brand' => $brandTargus,
                'material' => 'Polyester 300D',
                'image_url' => 'https://anphat.com.vn/media/product/34030_3.jpg',
                'thumbnail' => 'https://anphat.com.vn/media/product/34030_3.jpg',
                'description' => 'Túi đeo chéo thời trang, chống nước, đựng vừa iPad.',
                'is_featured' => true,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorGreen, 'size' => 'Free', 'price' => 350000, 'stock' => 120, 'rating' => 4.3],
                    ['color' => $colorBlack, 'size' => 'Free', 'price' => 350000, 'stock' => 110, 'rating' => 4.2],
                ]
            ],
            [
                'name' => 'Balo du lịch lớn Samsonite 50L',
                'slug' => 'samsonite-travel-50l',
                'category' => $catTravel,
                'brand' => $brandSamsonite,
                'material' => 'Nylon 600D',
                'image_url' => 'https://cdn.hstatic.net/products/200000321545/xanhla_31x24x44cm_1_6e936579337d4369a507ace92ee90955_master.jpg',
                'thumbnail' => 'https://cdn.hstatic.net/products/200000321545/xanhla_31x24x44cm_1_6e936579337d4369a507ace92ee90955_master.jpg',
                'description' => 'Balo du lịch cỡ lớn 50L, phù hợp cho chuyến đi dài ngày.',
                'is_featured' => false,
                'is_preorder' => true,
                'variants' => [
                    ['color' => $colorNavy, 'size' => 'XL', 'price' => 2850000, 'stock' => 20, 'rating' => 4.8],
                    ['color' => $colorBrown, 'size' => 'XL', 'price' => 2850000, 'stock' => 10, 'rating' => 4.7],
                ]
            ],
            [
                'name' => 'Solo Minimalist Backpack',
                'slug' => 'solo-minimalist-backpack',
                'category' => $catLaptop,
                'brand' => $brandSolo,
                'material' => 'Polyester 900D',
                'image_url' => 'https://www.sixmoondesigns.com/cdn/shop/products/OV06170.jpg?v=1682026829&width=1600',
                'thumbnail' => 'https://www.sixmoondesigns.com/cdn/shop/products/OV06170.jpg?v=1682026829&width=1600',
                'description' => 'Balo tối giản, phù hợp với môi trường công sở.',
                'is_featured' => false,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorGray, 'size' => 'M', 'price' => 1190000, 'stock' => 55, 'rating' => 4.5],
                ]
            ],
            [
                'name' => 'BigBag Eco Canvas',
                'slug' => 'bigbag-eco-canvas',
                'category' => $catCrossbody,
                'brand' => $brandBigbag,
                'material' => 'Canvas tái chế',
                'image_url' => 'https://cdn.awsli.com.br/2500x2500/2626/2626263/produto/254251738/20231226_155703-uu383ushc9.jpg',
                'thumbnail' => 'https://cdn.awsli.com.br/2500x2500/2626/2626263/produto/254251738/20231226_155703-uu383ushc9.jpg',
                'description' => 'Balo thân thiện môi trường, chất liệu canvas tái chế.',
                'is_featured' => true,
                'is_preorder' => false,
                'variants' => [
                    ['color' => $colorGreen, 'size' => 'M', 'price' => 650000, 'stock' => 90, 'rating' => 4.4],
                    ['color' => $colorBrown, 'size' => 'M', 'price' => 650000, 'stock' => 85, 'rating' => 4.3],
                ]
            ],
        ];


        foreach ($newProducts as $np) {
            $product = Product::updateOrCreate(
                ['slug' => $np['slug']],
                [
                    'category_id' => $np['category']->id,
                    'brand_id' => $np['brand']->id,
                    'name' => $np['name'],
                    'material' => $np['material'],
                    'image_url' => $np['image_url'],
                    'thumbnail' => $np['thumbnail'],
                    'description' => $np['description'],
                    'is_featured' => $np['is_featured'],
                    'is_preorder' => $np['is_preorder'],
                    'status' => 1,
                ]
            );
            foreach ($np['variants'] as $variant) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color_id' => $variant['color']->id,
                        'size_name' => $variant['size'],
                    ],
                    [
                        'price' => $variant['price'],
                        'stock' => $variant['stock'],
                        'rating' => $variant['rating'],
                    ]
                );
            }
        }
    }
}
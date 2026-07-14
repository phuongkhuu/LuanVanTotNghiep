<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private $priceColumn = 'price';

    public function index()
    {
        $this->detectPriceColumn();

        // ==================== BANNER ====================
        $banners = Banner::where('status', Banner::STATUS_ACTIVE)
            ->with('campaign')
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'campaign' => $banner->campaign?->name,
                ];
            });

        // ==================== HOT SALE ====================
        $hotSales = $this->getHotSaleProducts();
        
        // ==================== TRENDING ====================
        $trending = $this->getTrendingProducts();
        
        // ==================== NEW PRODUCTS ====================
        $newProducts = $this->getNewProducts();

        // ==================== NEWS ====================
        $newsList = [
            [
                'id' => 1,
                'title' => 'BigBag ra mắt bộ sưu tập Xuân Hè 2024',
                'excerpt' => 'Những thiết kế mới nhất với chất liệu thân thiện môi trường, phong cách thời trang công sở hiện đại.',
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop',
                'category' => 'Sự kiện',
                'date' => '15/03/2024'
            ],
            [
                'id' => 2,
                'title' => 'Ưu đãi đặc biệt dịp 30/4 - Giảm đến 40%',
                'excerpt' => 'Nhân dịp lễ lớn, BigBag dành tặng ưu đãi cực sốc cho tất cả sản phẩm balo và túi xách.',
                'image' => 'https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?w=800&h=500&fit=crop',
                'category' => 'Khuyến mãi',
                'date' => '10/04/2024'
            ],
            [
                'id' => 3,
                'title' => 'Bí quyết chọn balo phù hợp với vóc dáng',
                'excerpt' => 'Khám phá những bí quyết chọn balo giúp bạn tôn lên vóc dáng và phong cách riêng.',
                'image' => 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&h=500&fit=crop',
                'category' => 'Mẹo hay',
                'date' => '05/04/2024'
            ]
        ];

        return Inertia::render('Web/Welcome', [
            'banners' => $banners,
            'hotSales' => $hotSales,
            'trending' => $trending,
            'newProducts' => $newProducts,
            'newsList' => $newsList,
        ]);
    }

    private function detectPriceColumn()
    {
        $columns = Schema::getColumnListing('products');
        $possiblePriceColumns = ['price', 'product_price', 'unit_price', 'cost', 'sale_price', 'price_regular'];
        
        foreach ($possiblePriceColumns as $col) {
            if (in_array($col, $columns)) {
                $this->priceColumn = $col;
                return $col;
            }
        }
        
        $this->priceColumn = 'price';
    }

    private function getHotSaleProducts()
    {
        if ($this->columnExists('is_hot_sale')) {
            $hotSales = Product::where('is_hot_sale', true)->limit(4)->get();
            if ($hotSales->isNotEmpty()) {
                return $hotSales->map(fn($product) => $this->formatProductData($product, 'hot_sale'));
            }
        }

        if ($this->columnExists('discount')) {
            $hotSales = Product::where('discount', '>', 0)
                ->orderBy('discount', 'desc')
                ->limit(4)
                ->get();
            if ($hotSales->isNotEmpty()) {
                return $hotSales->map(fn($product) => $this->formatProductData($product, 'hot_sale'));
            }
        }

        $hotSales = Product::limit(4)->get();
        return $hotSales->map(fn($product) => $this->formatProductData($product, 'hot_sale'));
    }

    private function getTrendingProducts()
    {
        if ($this->columnExists('is_trending')) {
            $trending = Product::where('is_trending', true)->limit(4)->get();
            if ($trending->isNotEmpty()) {
                return $trending->map(fn($product) => $this->formatProductData($product, 'trending'));
            }
        }

        if ($this->columnExists('sold')) {
            $trending = Product::orderBy('sold', 'desc')->limit(4)->get();
            if ($trending->isNotEmpty()) {
                return $trending->map(fn($product) => $this->formatProductData($product, 'trending'));
            }
        }

        $trending = Product::orderBy('created_at', 'desc')->limit(4)->get();
        return $trending->map(fn($product) => $this->formatProductData($product, 'trending'));
    }

    private function getNewProducts()
    {
        $newProducts = Product::orderBy('created_at', 'desc')->limit(4)->get();
        return $newProducts->map(fn($product) => $this->formatProductData($product, 'new'));
    }

    private function formatProductData($product, $type = 'default')
    {
        $price = $this->getProductPrice($product);
        $image = $this->getProductImage($product);

        $data = [
            'id' => $product->id,
            'name' => $product->name ?? 'Sản phẩm',
            'image' => $image,
            'price' => $price,
        ];

        if ($type === 'hot_sale') {
            $salePrice = $product->sale_price ?? $price * 0.8;
            $data['salePrice'] = (float) $salePrice;
            $data['originalPrice'] = (float) $price;
            $data['discount'] = (int) ($product->discount ?? $this->calculateDiscount($price, $salePrice));
            $data['rating'] = (float) ($product->rating ?? rand(4, 5));
            $data['reviews'] = (int) ($product::with('reviews')->first()->reviews->count() ?? rand(10, 100));
            $data['slug'] = $product->slug ?? 'product-' . $product->id;
        }

        if ($type === 'trending') {
            $data['sold'] = (int) ($product->sold ?? rand(50, 500));
        }

        return $data;
    }

    private function getProductPrice($product)
    {
        if (!$product->relationLoaded('variants')) {
            $product->load('variants');
        }
        $minPrice = $product->variants->min('price') ?? 0;
        return $minPrice;
    }

    private function getProductImage($product)
    {
        if (empty($product->image_url)) {
            return '/images/default-product.jpg';
        }

        $image = $product->image_url;

        if ($this->isJson($image)) {
            $images = json_decode($image, true);
            if (is_array($images) && !empty($images)) {
                return $images[0];
            }
            return '/images/default-product.jpg';
        }

        return $image;
    }

    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function calculateDiscount($originalPrice, $salePrice)
    {
        if ($originalPrice > 0 && $salePrice > 0 && $salePrice < $originalPrice) {
            $discount = round((($originalPrice - $salePrice) / $originalPrice) * 100);
            return min(max($discount, 0), 50);
        }
        return rand(10, 30);
    }

    private function columnExists($column)
    {
        return Schema::hasColumn('products', $column);
    }
}
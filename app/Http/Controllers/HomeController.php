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
                    'image' => $this->normalizeImagePath($banner->image ?? '/images/default-banner.jpg'),
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
            $data['reviews'] = (int) ($product->reviews ?? rand(10, 100));
        }

        if ($type === 'trending') {
            $data['sold'] = (int) ($product->sold ?? rand(50, 500));
        }

        return $data;
    }

    private function getProductPrice($product)
    {
        if (isset($product->{$this->priceColumn}) && $product->{$this->priceColumn} > 0) {
            return (float) $product->{$this->priceColumn};
        }
        
        if (isset($product->sale_price) && $product->sale_price > 0) {
            return (float) $product->sale_price;
        }
        
        $possibleColumns = ['product_price', 'unit_price', 'cost', 'price_regular'];
        foreach ($possibleColumns as $col) {
            if (isset($product->$col) && $product->$col > 0) {
                return (float) $product->$col;
            }
        }
        
        return 0;
    }

    private function getProductImage($product)
    {
        if (empty($product->image)) {
            return '/images/default-product.jpg';
        }

        $image = $product->image;

        if ($this->isJson($image)) {
            $images = json_decode($image, true);
            if (is_array($images) && !empty($images)) {
                return $this->normalizeImagePath($images[0]);
            }
            return '/images/default-product.jpg';
        }

        return $this->normalizeImagePath($image);
    }

    private function normalizeImagePath($path)
    {
        if (empty($path)) {
            return '/images/default-product.jpg';
        }

        // Nếu là URL đầy đủ
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Nếu đã có /storage/
        if (strpos($path, '/storage/') === 0) {
            return $path;
        }

        // Nếu có storage/ nhưng thiếu /
        if (strpos($path, 'storage/') === 0) {
            return '/' . $path;
        }

        // Nếu đã có /images/
        if (strpos($path, '/images/') === 0) {
            return $path;
        }

        // Nếu có images/ nhưng thiếu /
        if (strpos($path, 'images/') === 0) {
            return '/' . $path;
        }

        // Mặc định: thêm /storage/
        return '/storage/' . ltrim($path, '/');
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
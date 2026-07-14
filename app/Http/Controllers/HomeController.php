<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Campaign;
use App\Models\CampaignConfig;
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

    /**
     * Tính toán giá sale cho sản phẩm - Lấy campaign có discount cao nhất và kiểm tra thời gian
     */
    private function calculateSalePrice($product)
    {
        $originalPrice = $this->getProductPrice($product);
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $discountType = null;
        $campaignId = null;
        $now = now();

        // Lấy tất cả variants của sản phẩm
        $variantIds = $product->variants->pluck('id')->toArray();

        if (empty($variantIds)) {
            return [
                'original_price' => $originalPrice,
                'sale_price' => $originalPrice,
                'discount_percent' => 0,
                'discount_type' => null,
                'campaign_id' => null,
                'is_on_sale' => false,
            ];
        }

        // 1. Kiểm tra campaign - Lấy campaign có discount cao nhất
        if (!$product->is_preorder) {
            // Lấy TẤT CẢ campaigns áp dụng cho sản phẩm này
            $campaigns = Campaign::where('status', 'active')
                ->where('type', '!=', 'voucher')
                ->where('type', '!=', 'preorder')
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    })->orWhere(function($q) {
                        $q->whereNull('start_time')
                          ->whereNull('end_time');
                    });
                })
                ->whereHas('productVariants', function($query) use ($variantIds) {
                    $query->whereIn('product_variant_id', $variantIds);
                })
                ->with('configs')
                ->get();
            
            // Duyệt qua tất cả campaigns để tìm discount cao nhất
            foreach ($campaigns as $campaign) {
                $config = $campaign->configs()->first();
                $currentDiscount = $config ? (float) $config->discount_percent : 0;
                
                // Lấy campaign có discount cao nhất
                if ($currentDiscount > $discountPercent) {
                    $discountPercent = $currentDiscount;
                    $campaignId = $campaign->id;
                    $discountType = 'campaign';
                }
            }
            
            // Nếu có discount > 0, tính sale price
            if ($discountPercent > 0) {
                $salePrice = $originalPrice * (1 - $discountPercent / 100);
                $salePrice = round($salePrice);
            }
        }

        // 2. Kiểm tra pre-order
        if ($product->is_preorder) {
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $product->id)
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    })->orWhere(function($q) {
                        $q->whereNull('start_time')
                          ->whereNull('end_time');
                    });
                })
                ->first();
            
            if ($preorder) {
                $currentBuyers = $preorder->current_buyers ?? 0;
                $tiers = $preorder->tiers ?? [];
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $preorderDiscount = $tier['discount'] ?? 0;
                        // So sánh với discount từ campaign thường, lấy cái cao hơn
                        if ($preorderDiscount > $discountPercent) {
                            $discountPercent = $preorderDiscount;
                            $discountType = 'preorder';
                            $campaignId = $preorder->id;
                        }
                        break;
                    }
                }
                
                if ($discountPercent > 0) {
                    $salePrice = $originalPrice * (1 - $discountPercent / 100);
                    $salePrice = round($salePrice);
                }
            }
        }

        $result = [
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'discount_type' => $discountType,
            'campaign_id' => $campaignId,
            'is_on_sale' => $discountPercent > 0,
        ];

        return $result;
    }

    private function getProductsWithActiveCampaign()
    {
        $now = now();
        
        // Lấy tất cả variant ids từ campaign đang active và trong thời gian hiệu lực
        $variantIds = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now);
                })->orWhere(function($q) {
                    $q->whereNull('start_time')
                      ->whereNull('end_time');
                });
            })
            ->with('productVariants')
            ->get()
            ->pluck('productVariants')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();

        if (empty($variantIds)) {
            return collect();
        }

        // Lấy sản phẩm có variants trong campaign
        return Product::with(['variants', 'variants.color'])
            ->whereHas('variants', function($query) use ($variantIds) {
                $query->whereIn('id', $variantIds);
            })
            ->limit(8)
            ->get();
    }

    private function getHotSaleProducts()
    {
        // Lấy sản phẩm có campaign active
        $campaignProducts = $this->getProductsWithActiveCampaign();

        if ($campaignProducts->isNotEmpty()) {
            return $campaignProducts->map(function ($product) {
                $saleInfo = $this->calculateSalePrice($product);
                return $this->formatProductData($product, 'hot_sale', $saleInfo);
            })->slice(0, 4)->values();
        }

        // Fallback: lấy sản phẩm có is_hot_sale
        if ($this->columnExists('is_hot_sale')) {
            $hotSales = Product::where('is_hot_sale', true)->limit(4)->get();
            if ($hotSales->isNotEmpty()) {
                return $hotSales->map(function ($product) {
                    $saleInfo = $this->calculateSalePrice($product);
                    return $this->formatProductData($product, 'hot_sale', $saleInfo);
                });
            }
        }

        // Fallback cuối cùng: lấy sản phẩm mới nhất
        $hotSales = Product::limit(4)->get();
        return $hotSales->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'hot_sale', $saleInfo);
        });
    }

    private function getTrendingProducts()
    {
        // Lấy sản phẩm có campaign active
        $campaignProducts = $this->getProductsWithActiveCampaign();

        if ($campaignProducts->isNotEmpty()) {
            return $campaignProducts->map(function ($product) {
                $saleInfo = $this->calculateSalePrice($product);
                return $this->formatProductData($product, 'trending', $saleInfo);
            })->slice(0, 4)->values();
        }

        // Fallback
        if ($this->columnExists('is_trending')) {
            $trending = Product::where('is_trending', true)->limit(4)->get();
            if ($trending->isNotEmpty()) {
                return $trending->map(function ($product) {
                    $saleInfo = $this->calculateSalePrice($product);
                    return $this->formatProductData($product, 'trending', $saleInfo);
                });
            }
        }

        $trending = Product::orderBy('created_at', 'desc')->limit(4)->get();
        return $trending->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'trending', $saleInfo);
        });
    }

    private function getNewProducts()
    {
        // Lấy sản phẩm mới nhất kèm variants
        $newProducts = Product::with(['variants', 'variants.color'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Format từng sản phẩm
        $formattedProducts = $newProducts->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'new', $saleInfo);
        });

        // Lọc ra 4 sản phẩm, ưu tiên sản phẩm có sale
        $priorityProducts = $formattedProducts->filter(function ($product) {
            return $product['is_on_sale'];
        });

        $nonSaleProducts = $formattedProducts->filter(function ($product) {
            return !$product['is_on_sale'];
        });

        // Kết hợp: lấy sale trước, sau đó lấy non-sale để đủ 4
        $result = $priorityProducts->concat($nonSaleProducts)->slice(0, 4)->values();

        return $result;
    }

    private function formatProductData($product, $type = 'default', $saleInfo = null)
    {
        if ($saleInfo === null) {
            $saleInfo = $this->calculateSalePrice($product);
        }

        $price = $saleInfo['original_price'];
        $salePrice = $saleInfo['sale_price'];
        $discountPercent = $saleInfo['discount_percent'];
        $isOnSale = $saleInfo['is_on_sale'];

        $image = $this->getProductImage($product);

        $data = [
            'id' => $product->id,
            'name' => $product->name ?? 'Sản phẩm',
            'image' => $image,
            'price' => $price,
            'slug' => $product->slug ?? 'product-' . $product->id,
            'is_on_sale' => $isOnSale,
            'sale_price' => $isOnSale ? $salePrice : null,
            'original_price' => $isOnSale ? $price : null,
            'discount_percent' => $isOnSale ? $discountPercent : 0,
            'discount_type' => $saleInfo['discount_type'],
            'campaign_id' => $saleInfo['campaign_id'],
        ];

        if ($type === 'hot_sale') {
            $data['rating'] = (float) ($product->rating ?? rand(4, 5));
            $data['reviews'] = (int) (rand(10, 100));
            if (!$isOnSale) {
                $fakeDiscount = rand(10, 30);
                $data['discount_percent'] = $fakeDiscount;
                $data['sale_price'] = $price * (1 - $fakeDiscount / 100);
                $data['original_price'] = $price;
                $data['is_on_sale'] = true;
            }
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
        return (float) $minPrice;
    }

    private function getProductImage($product)
    {
        // Kiểm tra image_url
        if (!empty($product->image_url)) {
            $image = $product->image_url;

            if (is_array($image) && !empty($image)) {
                return $image[0];
            }

            if (is_string($image) && $this->isJson($image)) {
                $images = json_decode($image, true);
                if (is_array($images) && !empty($images)) {
                    return $images[0];
                }
            }

            if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
        }

        // Kiểm tra thumbnail
        if (!empty($product->thumbnail)) {
            return $product->thumbnail;
        }

        // Fallback
        return '/images/default-product.jpg';
    }

    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function columnExists($column)
    {
        return Schema::hasColumn('products', $column);
    }
}
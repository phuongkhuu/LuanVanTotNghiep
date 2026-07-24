<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Campaign;
use App\Models\News;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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

        // ==================== SALE CAMPAIGN (cho countdown) ====================
        $saleCampaign = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where('end_time', '>', now())
            ->whereHas('productVariants', function ($q) {
                $q->whereHas('product', function ($q2) {
                    $q2->where('status', 1);
                });
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        // ==================== HOT SALE ====================
        $hotSales = $this->getHotSaleProducts();

        // ==================== TRENDING ====================
        $trending = $this->getTrendingProducts();

        // ==================== NEW PRODUCTS ====================
        $newProducts = $this->getNewProducts();

        // ==================== NEWS & PROMOTIONS ====================
        $newsList = $this->getNewsAndPromotions();

        return Inertia::render('Web/Welcome', [
            'banners' => $banners,
            'hotSales' => $hotSales,
            'trending' => $trending,
            'newProducts' => $newProducts,
            'newsList' => $newsList,
            'saleCampaign' => $saleCampaign ? [
                'id' => $saleCampaign->id,
                'end_time' => $saleCampaign->end_time,
                'name' => $saleCampaign->name,
            ] : null,
        ]);
    }

    // ==================== PHẦN TÍNH TOÁN SALE ====================

    /**
     * Tính toán giá sale cho sản phẩm
     * Ưu tiên dữ liệu từ variant đã được set sale (bởi PromotionController)
     * Nếu chưa có, fallback tính từ campaign (để đảm bảo hiển thị)
     */
    private function calculateSalePrice($product)
    {
        $originalPrice = $this->getProductPrice($product);
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $discountType = null;
        $campaignId = null;
        $isOnSale = false;

        $variants = $product->variants;

        // 1. Ưu tiên kiểm tra variant đã được set sale
        foreach ($variants as $variant) {
            if ($variant->is_on_sale && $variant->sale_price && $variant->sale_price > 0) {
                if (!$isOnSale || $variant->sale_price < $salePrice) {
                    $salePrice = $variant->sale_price;
                    $discountPercent = $variant->discount_percent ?? 0;
                    if ($discountPercent == 0 && $originalPrice > 0) {
                        $discountPercent = round((1 - $salePrice / $originalPrice) * 100);
                    }
                    $discountType = $variant->sale_type;
                    $campaignId = $variant->sale_campaign_id;
                    $isOnSale = true;
                }
            }
        }

        if ($isOnSale) {
            return [
                'original_price' => $originalPrice,
                'sale_price' => $salePrice,
                'discount_percent' => $discountPercent,
                'discount_type' => $discountType,
                'campaign_id' => $campaignId,
                'is_on_sale' => true,
            ];
        }

        // 2. Fallback: tính từ campaign
        $variantIds = $variants->pluck('id')->toArray();
        if (empty($variantIds)) {
            return $this->getDefaultSaleInfo($originalPrice);
        }

        $now = now();

        if (!$product->is_preorder) {
            $campaigns = Campaign::where('status', 'active')
                ->where('type', '!=', 'voucher')
                ->where('type', '!=', 'preorder')
                ->where(function ($query) use ($now) {
                    $query->where(function ($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
                    })->orWhere(function ($q) {
                        $q->whereNull('start_time')
                            ->whereNull('end_time');
                    });
                })
                ->whereHas('productVariants', function ($query) use ($variantIds) {
                    $query->whereIn('product_variant_id', $variantIds);
                })
                ->with('configs')
                ->get();

            foreach ($campaigns as $campaign) {
                $config = $campaign->configs()->first();
                $currentDiscount = $config ? (float) $config->discount_percent : 0;
                if ($currentDiscount > $discountPercent) {
                    $discountPercent = $currentDiscount;
                    $campaignId = $campaign->id;
                    $discountType = 'campaign';
                }
            }
        }

        if ($product->is_preorder) {
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $product->id)
                ->where(function ($query) use ($now) {
                    $query->where(function ($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
                    })->orWhere(function ($q) {
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
                        $preDiscount = $tier['discount'] ?? 0;
                        if ($preDiscount > $discountPercent) {
                            $discountPercent = $preDiscount;
                            $discountType = 'preorder';
                            $campaignId = $preorder->id;
                        }
                        break;
                    }
                }
            }
        }

        if ($discountPercent > 0) {
            $salePrice = round($originalPrice * (1 - $discountPercent / 100));
            $isOnSale = true;
        }

        return [
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'discount_type' => $discountType,
            'campaign_id' => $campaignId,
            'is_on_sale' => $isOnSale,
        ];
    }

    private function getDefaultSaleInfo($price)
    {
        return [
            'original_price' => $price,
            'sale_price' => $price,
            'discount_percent' => 0,
            'discount_type' => null,
            'campaign_id' => null,
            'is_on_sale' => false,
        ];
    }

    // ==================== LẤY SẢN PHẨM HOT SALE (CHỈ LẤY SẢN PHẨM ĐANG GIẢM GIÁ) ====================

    private function getHotSaleProducts()
    {
        // CHỈ lấy sản phẩm có variant đang sale (is_on_sale = true)
        $productIds = ProductVariant::where('is_on_sale', true)
            ->where('sale_price', '>', 0)
            ->pluck('product_id')
            ->unique()
            ->toArray();

        if (empty($productIds)) {
            return collect();
        }

        $hotProducts = Product::with(['variants', 'variants.color'])
            ->whereIn('id', $productIds)
            ->where('status', 1)
            ->limit(4)
            ->get();

        return $hotProducts->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'hot_sale', $saleInfo);
        })->values();
    }

    // ==================== LẤY SẢN PHẨM TRENDING ====================

    private function getTrendingProducts()
    {
        // Ưu tiên dựa trên lượt xem (nếu có cột views) hoặc số lượng bán trong 7 ngày
        $sevenDaysAgo = now()->subDays(7);

        // Nếu có cột views, lấy sản phẩm có views cao nhất
        if (Schema::hasColumn('products', 'views')) {
            $trending = Product::with(['variants', 'variants.color'])
                ->where('status', 1)
                ->orderBy('views', 'desc')
                ->limit(4)
                ->get();

            if ($trending->isNotEmpty()) {
                return $trending->map(function ($product) {
                    $saleInfo = $this->calculateSalePrice($product);
                    return $this->formatProductData($product, 'trending', $saleInfo);
                });
            }
        }

        // Fallback: sản phẩm có số lượng bán nhiều trong 7 ngày
        $topTrending = OrderDetail::select('product_variant_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($query) use ($sevenDaysAgo) {
                $query->where('order_status', 3)
                    ->where('created_at', '>=', $sevenDaysAgo);
            })
            ->groupBy('product_variant_id')
            ->orderBy('total_sold', 'desc')
            ->limit(8)
            ->pluck('product_variant_id')
            ->toArray();

        $productIds = ProductVariant::whereIn('id', $topTrending)
            ->pluck('product_id')
            ->unique()
            ->toArray();

        $trending = collect();
        if (!empty($productIds)) {
            $trending = Product::with(['variants', 'variants.color'])
                ->whereIn('id', $productIds)
                ->where('status', 1)
                ->limit(4)
                ->get();
        }

        // Nếu chưa đủ, bổ sung sản phẩm có campaign active
        if ($trending->count() < 4) {
            $campaignProducts = $this->getProductsWithActiveCampaign();
            $existingIds = $trending->pluck('id')->toArray();
            $extra = $campaignProducts->filter(fn($p) => !in_array($p->id, $existingIds))
                ->take(4 - $trending->count());
            $trending = $trending->concat($extra);
        }

        // Nếu vẫn chưa đủ, lấy sản phẩm mới nhất
        if ($trending->count() < 4) {
            $fallback = Product::with(['variants', 'variants.color'])
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->limit(4 - $trending->count())
                ->get();
            $trending = $trending->concat($fallback);
        }

        return $trending->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'trending', $saleInfo);
        })->values();
    }

    // ==================== CÁC PHƯƠNG THỨC HỖ TRỢ ====================

    private function getProductsWithActiveCampaign()
    {
        $now = now();

        $variantIds = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                })->orWhere(function ($q) {
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

        return Product::with(['variants', 'variants.color'])
            ->whereHas('variants', function ($query) use ($variantIds) {
                $query->whereIn('id', $variantIds);
            })
            ->where('status', 1)
            ->limit(8)
            ->get();
    }

    private function getNewProducts()
    {
        $newProducts = Product::with(['variants', 'variants.color'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $formatted = $newProducts->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'new', $saleInfo);
        });

        // Ưu tiên sản phẩm có sale
        $priority = $formatted->filter(fn($p) => $p['is_on_sale']);
        $normal = $formatted->filter(fn($p) => !$p['is_on_sale']);
        return $priority->concat($normal)->slice(0, 4)->values();
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
            // Lấy số lượng bán thực tế
            $sold = OrderDetail::whereHas('order', function ($q) {
                $q->where('order_status', 3);
            })
                ->whereHas('productVariant', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->sum('quantity');
            $data['rating'] = (float) ($product->rating ?? 0);
            $data['reviews'] = (int) ($product->reviews_count ?? 0);
            $data['sold'] = (int) $sold;
        }

        if ($type === 'trending') {
            $sold = OrderDetail::whereHas('order', function ($q) {
                $q->where('order_status', 3);
            })
                ->whereHas('productVariant', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->sum('quantity');
            $data['sold'] = (int) $sold;
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

        if (!empty($product->thumbnail)) {
            return $product->thumbnail;
        }

        return '/images/default-product.jpg';
    }

    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function detectPriceColumn()
    {
        $columns = Schema::getColumnListing('products');
        $possible = ['price', 'product_price', 'unit_price', 'cost', 'sale_price', 'price_regular'];
        foreach ($possible as $col) {
            if (in_array($col, $columns)) {
                $this->priceColumn = $col;
                return;
            }
        }
        $this->priceColumn = 'price';
    }

    // ==================== NEWS & PROMOTIONS ====================
    // Giữ nguyên logic như cũ, không thay đổi

    private function getNewsAndPromotions()
    {
        try {
            $now = now();

            $news = News::with(['campaign', 'banner'])
                ->where('status', 1)
                ->whereHas('campaign', function ($query) use ($now) {
                    $query->where('status', 'active')
                        ->where(function ($q) use ($now) {
                            $q->where(function ($sub) use ($now) {
                                $sub->where('start_time', '<=', $now)
                                    ->where('end_time', '>=', $now);
                            })->orWhere(function ($sub) {
                                $sub->whereNull('start_time')
                                    ->whereNull('end_time');
                            });
                        });
                })
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            if ($news->isNotEmpty()) {
                return $news->map(function ($item) {
                    $campaign = $item->campaign;
                    $category = 'Tin tức';

                    if ($campaign) {
                        $campaignType = $campaign->type ?? '';
                        $typeLabels = [
                            'seasonal' => 'Theo mùa',
                            'flash_sale' => 'Flash Sale',
                            'anniversary' => 'Kỷ niệm',
                            'holiday' => 'Ngày lễ',
                            'product_launch' => 'Ra mắt sản phẩm',
                            'campaign' => 'Chiến dịch',
                            'other' => 'Khuyến mãi',
                        ];
                        $category = $typeLabels[$campaignType] ?? 'Khuyến mãi';
                    }

                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'excerpt' => $this->getExcerpt($item->content, 120),
                        'image' => $item->thumbnail ?? $item->banner?->image ?? $this->getDefaultNewsImage(),
                        'category' => $category,
                        'date' => $item->created_at ? $item->created_at->format('d/m/Y') : date('d/m/Y'),
                        'slug' => $item->slug,
                        'campaign_id' => $item->campaign_id,
                        'banner_id' => $item->banner_id,
                    ];
                });
            }

            return $this->getCampaignsAsNews();

        } catch (\Exception $e) {
            Log::error('Lỗi lấy news & promotions: ' . $e->getMessage());
            return $this->getFallbackNews();
        }
    }

    private function getCampaignsAsNews()
    {
        try {
            $now = now();

            $campaigns = Campaign::where('status', 'active')
                ->whereNotIn('type', ['voucher', 'preorder'])
                ->where(function ($query) use ($now) {
                    $query->where(function ($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
                    })->orWhere(function ($q) {
                        $q->whereNull('start_time')
                            ->whereNull('end_time');
                    });
                })
                ->with('banners')
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            if ($campaigns->isNotEmpty()) {
                return $campaigns->map(function ($campaign) {
                    $banner = $campaign->banners()->where('status', Banner::STATUS_ACTIVE)->first();

                    $typeLabels = [
                        'seasonal' => 'Theo mùa',
                        'flash_sale' => 'Flash Sale',
                        'anniversary' => 'Kỷ niệm',
                        'holiday' => 'Ngày lễ',
                        'product_launch' => 'Ra mắt sản phẩm',
                        'campaign' => 'Chiến dịch',
                        'other' => 'Khuyến mãi',
                    ];

                    return [
                        'id' => $campaign->id,
                        'title' => $campaign->name ?? 'Chiến dịch khuyến mãi',
                        'excerpt' => $campaign->description ?? 'Ưu đãi đặc biệt dành cho bạn',
                        'image' => $banner?->image ?? $campaign->banner_url ?? $this->getDefaultNewsImage(),
                        'category' => $typeLabels[$campaign->type] ?? 'Khuyến mãi',
                        'date' => $campaign->start_time ? $campaign->start_time->format('d/m/Y') : date('d/m/Y'),
                        'slug' => 'promotion-' . $campaign->id,
                        'campaign_id' => $campaign->id,
                        'banner_id' => $banner?->id,
                    ];
                });
            }

            return $this->getFallbackNews();

        } catch (\Exception $e) {
            Log::error('Lỗi lấy campaigns làm news: ' . $e->getMessage());
            return $this->getFallbackNews();
        }
    }

    private function getFallbackNews()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'BigBag ra mắt bộ sưu tập Xuân Hè 2024',
                'excerpt' => 'Những thiết kế mới nhất với chất liệu thân thiện môi trường, phong cách thời trang công sở hiện đại.',
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop',
                'category' => 'Sự kiện',
                'date' => date('d/m/Y'),
            ],
            [
                'id' => 2,
                'title' => 'Ưu đãi đặc biệt dịp 30/4 - Giảm đến 40%',
                'excerpt' => 'Nhân dịp lễ lớn, BigBag dành tặng ưu đãi cực sốc cho tất cả sản phẩm balo và túi xách.',
                'image' => 'https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?w=800&h=500&fit=crop',
                'category' => 'Khuyến mãi',
                'date' => date('d/m/Y'),
            ],
            [
                'id' => 3,
                'title' => 'Bí quyết chọn balo phù hợp với vóc dáng',
                'excerpt' => 'Khám phá những bí quyết chọn balo giúp bạn tôn lên vóc dáng và phong cách riêng.',
                'image' => 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&h=500&fit=crop',
                'category' => 'Mẹo hay',
                'date' => date('d/m/Y'),
            ]
        ]);
    }

    private function getExcerpt($content, $length = 120)
    {
        if (empty($content)) {
            return '';
        }

        $text = strip_tags($content);
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }

        return $text;
    }

    private function getDefaultNewsImage()
    {
        return 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop';
    }
}
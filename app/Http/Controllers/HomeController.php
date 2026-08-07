<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Campaign;
use App\Models\News;
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

        // ==================== HOT SALE ====================
        $hotSales = $this->getHotSaleProducts();

        // ==================== SALE CAMPAIGN (cho countdown) ====================
        $saleCampaign = null;
        if ($hotSales->isNotEmpty()) {
            $hotProductIds = $hotSales->pluck('id')->toArray();
            Log::info('Hot sale product IDs:', $hotProductIds);
            
            $campaign = Campaign::where('status', 'active')
                ->where('type', '!=', 'voucher')
                ->where('type', '!=', 'preorder')
                ->where('end_time', '>', now())
                ->whereHas('productVariants.product', function ($q) use ($hotProductIds) {
                    $q->whereIn('products.id', $hotProductIds);
                })
                ->orderBy('priority', 'desc')
                ->orderBy('end_time', 'asc')
                ->first();

            if ($campaign) {
                $saleCampaign = $campaign;
                Log::info('Sale campaign found from hot sales:', [
                    'id' => $saleCampaign->id,
                    'name' => $saleCampaign->name,
                    'end_time' => $saleCampaign->end_time,
                ]);
            } else {
                Log::info('No matching campaign found for hot sales products.');
            }
        } else {
            Log::info('No hot sales products found, skipping sale campaign.');
        }

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
                'end_time' => $saleCampaign->end_time ? $saleCampaign->end_time->toISOString() : null,
                'name' => $saleCampaign->name,
            ] : null,
        ]);
    }

    // ==================== PHẦN TÍNH TOÁN SALE ====================

    private function calculateSalePrice($product)
    {
        $originalPrice = $this->getProductPrice($product);
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $discountType = null;
        $campaignId = null;
        $isOnSale = false;

        $variants = $product->variants;

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

    // ==================== LẤY SỐ LƯỢNG ĐÃ BÁN ====================

    /**
     * Lấy tổng số lượng đã bán của các sản phẩm (chỉ tính đơn hàng hoàn thành)
     * Hoàn thành: retail = status 3, wholesale/preorder = status 4
     * @param array $productIds
     * @return array [product_id => total_sold]
     */
    private function getSoldForProducts(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        $soldData = OrderDetail::whereHas('order', function ($q) {
            // Lấy tất cả đơn hàng có trạng thái hoàn thành
            // retail: 3, wholesale/preorder: 4
            $q->whereIn('order_status', [3, 4]);
        })
        ->whereHas('productVariant', function ($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })
        ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
        ->select('product_variants.product_id', DB::raw('SUM(quantity) as total_sold'))
        ->groupBy('product_variants.product_id')
        ->pluck('total_sold', 'product_variants.product_id')
        ->toArray();

        return $soldData;
    }

    // ==================== LẤY SẢN PHẨM HOT SALE ====================

    private function getHotSaleProducts()
    {
        $now = now();

        // Lấy tất cả variant đang có campaign active (giảm giá)
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

        // Nếu không có variant nào trong campaign, fallback lấy variant có is_on_sale = true
        if (empty($variantIds)) {
            $variantIds = ProductVariant::where('is_on_sale', true)
                ->where('sale_price', '>', 0)
                ->pluck('id')
                ->toArray();
        }

        if (empty($variantIds)) {
            return collect();
        }

        $productIds = ProductVariant::whereIn('id', $variantIds)
            ->pluck('product_id')
            ->unique()
            ->toArray();

        // Lấy sản phẩm kèm rating và reviews
        $hotProducts = Product::with(['variants', 'variants.color'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereIn('id', $productIds)
            ->where('status', 1)
            ->limit(8)
            ->get();

        // Tính sold cho tất cả sản phẩm
        $soldMap = $this->getSoldForProducts($hotProducts->pluck('id')->toArray());

        // Lọc và sắp xếp theo mức giảm giá cao nhất
        $formatted = $hotProducts->map(function ($product) use ($soldMap) {
            $saleInfo = $this->calculateSalePrice($product);
            if (!$saleInfo['is_on_sale']) return null;
            $data = $this->formatProductData($product, 'hot_sale', $saleInfo);
            $data['discount_percent'] = $saleInfo['discount_percent'];
            $data['sold'] = (int) ($soldMap[$product->id] ?? 0);
            return $data;
        })->filter()
        ->sortByDesc('discount_percent')
        ->take(4)
        ->values();

        return $formatted;
    }

    // ==================== LẤY SẢN PHẨM TRENDING ====================

    private function getTrendingProducts()
    {
        $sevenDaysAgo = now()->subDays(7);

        // Nếu có cột views, ưu tiên dùng views
        if (Schema::hasColumn('products', 'views')) {
            $trending = Product::with(['variants', 'variants.color'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('status', 1)
                ->orderBy('views', 'desc')
                ->limit(4)
                ->get();

            if ($trending->isNotEmpty()) {
                $soldMap = $this->getSoldForProducts($trending->pluck('id')->toArray());
                return $trending->map(function ($product) use ($soldMap) {
                    $saleInfo = $this->calculateSalePrice($product);
                    $data = $this->formatProductData($product, 'trending', $saleInfo);
                    $data['sold'] = (int) ($soldMap[$product->id] ?? 0);
                    return $data;
                });
            }
        }

        // Nếu không có views, tính theo số lượng bán trong 7 ngày gần nhất
        $topTrending = OrderDetail::select('product_variant_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($query) use ($sevenDaysAgo) {
                $query->whereIn('order_status', [3, 4]) // hoàn thành
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
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->whereIn('id', $productIds)
                ->where('status', 1)
                ->limit(4)
                ->get();
        }

        // Bổ sung sản phẩm từ campaign nếu chưa đủ 4
        if ($trending->count() < 4) {
            $campaignProducts = $this->getProductsWithActiveCampaign();
            $existingIds = $trending->pluck('id')->toArray();
            $extra = $campaignProducts->filter(fn($p) => !in_array($p->id, $existingIds))
                ->take(4 - $trending->count());
            $trending = $trending->concat($extra);
        }

        // Fallback: lấy sản phẩm mới nhất
        if ($trending->count() < 4) {
            $fallback = Product::with(['variants', 'variants.color'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->limit(4 - $trending->count())
                ->get();
            $trending = $trending->concat($fallback);
        }

        // Loại bỏ trùng lặp
        $trending = $trending->unique('id');

        // Tính sold cho toàn bộ sản phẩm
        $soldMap = $this->getSoldForProducts($trending->pluck('id')->toArray());

        return $trending->map(function ($product) use ($soldMap) {
            $saleInfo = $this->calculateSalePrice($product);
            $data = $this->formatProductData($product, 'trending', $saleInfo);
            $data['sold'] = (int) ($soldMap[$product->id] ?? 0);
            return $data;
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
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
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
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $formatted = $newProducts->map(function ($product) {
            $saleInfo = $this->calculateSalePrice($product);
            return $this->formatProductData($product, 'new', $saleInfo);
        });

        // Ưu tiên sản phẩm đang giảm giá lên trước
        $priority = $formatted->filter(fn($p) => $p['is_on_sale']);
        $normal = $formatted->filter(fn($p) => !$p['is_on_sale']);
        return $priority->concat($normal)->slice(0, 4)->values();
    }

    /**
     * Định dạng dữ liệu sản phẩm trả về cho frontend
     * (không tính sold ở đây nữa, đã được tính trước)
     */
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
            'rating' => (float) ($product->reviews_avg_rating ?? 0),
            'reviews' => (int) ($product->reviews_count ?? 0),
        ];

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
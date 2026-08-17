<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Campaign;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::with([
            'category',
            'brand',
            'variants.color',
            'reviews.user'
        ])
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        // ============ LẤY THÔNG TIN PRE-ORDER ============
        $isPreorder = false;
        $isPreorderActive = false;
        $preorderInfo = null;
        $preorder = null;
        $preorderDiscount = 0;

        if ($product->is_preorder) {
            $now = now();

            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $product->id)
                ->where(function($query) use ($now) {
                    $query->where('start_time', '<=', $now)
                        ->orWhereNull('start_time');
                })
                ->where(function($query) use ($now) {
                    $query->where('end_time', '>=', $now)
                        ->orWhereNull('end_time');
                })
                ->first();

            if ($preorder) {
                $isPreorder = true;
                $isPreorderActive = true;

                $totalOrders = Order::where('campaign_id', $preorder->id)
                    ->where('order_code', 'preorder')
                    ->where('order_status', '!=', 4)
                    ->count();

                $tiers = $preorder->tiers ?? [];

                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });

                $currentTier = null;
                $currentTierIndex = -1;
                foreach ($tiers as $index => $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($totalOrders >= $from && $totalOrders <= $to) {
                        $currentTier = $tier;
                        $currentTierIndex = $index;
                        break;
                    }
                }

                if ($currentTier) {
                    $preorderDiscount = $currentTier['discount'] ?? 0;
                } elseif (!empty($tiers)) {
                    $firstTier = $tiers[0];
                    $preorderDiscount = $firstTier['discount'] ?? 0;
                }

                $nextTier = null;
                $nextCount = 0;
                if ($currentTier && isset($tiers[$currentTierIndex + 1])) {
                    $nextTier = $tiers[$currentTierIndex + 1];
                    $nextCount = ($nextTier['from'] ?? 0) - $totalOrders;
                }

                $maxBuyers = !empty($tiers) ? $tiers[count($tiers) - 1]['to'] ?? 100 : 100;

                $preorderInfo = [
                    'campaign_id' => $preorder->id,
                    'total_orders' => $totalOrders,
                    'tiers' => $tiers,
                    'current_discount' => $preorderDiscount,
                    'next_tier' => $nextTier,
                    'next_count' => $nextCount,
                    'max_buyers' => $maxBuyers,
                    'is_in_tier' => $currentTier !== null,
                    'start_date' => $preorder->start_time ? $preorder->start_time->format('d/m/Y') : null,
                    'end_date' => $preorder->end_time ? $preorder->end_time->format('d/m/Y') : null,
                ];
            } else {
                $isPreorder = true;
                $isPreorderActive = false;
            }
        }

        // ============ TÍNH GIÁ HIỂN THỊ ============
        $variants = $product->variants;

        $minPriceAll = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPriceAll;

        $displayPrice = $minPriceAll;
        $originalPrice = 0;
        $hasSale = false;
        $salePercent = 0;
        $discountPercent = 0;

        if ($product->is_preorder && $isPreorderActive && $preorderDiscount > 0) {
            $salePrice = round($minPriceAll * (1 - $preorderDiscount / 100));
            $displayPrice = $salePrice;
            $originalPrice = $minPriceAll;
            $hasSale = true;
            $salePercent = $preorderDiscount;
            $discountPercent = $preorderDiscount;
        }

        if (!$product->is_preorder && !$hasSale) {
            $variantIds = $product->variants->pluck('id')->toArray();
            $now = now();

            if (!empty($variantIds)) {
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
                    ->first();

                if ($campaigns) {
                    $config = $campaigns->configs()->first();
                    $discountPercent = $config ? (float) $config->discount_percent : 0;

                    if ($discountPercent > 0) {
                        $salePrice = round($minPriceAll * (1 - $discountPercent / 100));
                        $displayPrice = $salePrice;
                        $originalPrice = $minPriceAll;
                        $hasSale = true;
                        $salePercent = $discountPercent;
                    }
                }
            }
        }

        if (!$hasSale) {
            $displayPrice = $minPriceAll;
            $originalPrice = 0;
            $salePercent = 0;
        }

        $discount = null;
        if (!$hasSale && $maxPrice > $minPriceAll) {
            $discount = round((1 - $minPriceAll / $maxPrice) * 100) . '%';
        }

        // ============ LẤY DANH SÁCH SIZE VÀ MÀU ============
        // Lấy tất cả size có trong sản phẩm
        $allSizes = $product->variants->pluck('size_name')->unique()->filter()->values();

        // Lấy màu sắc
        $colors = [];
        $colorMap = [];
        
        foreach ($product->variants as $variant) {
            if ($variant->color) {
                $colorId = $variant->color_id;
                if (!isset($colorMap[$colorId])) {
                    $colorMap[$colorId] = [
                        'value' => $colorId,
                        'code' => $variant->color->code ?? '#000000',
                        'label' => $variant->color->name
                    ];
                }
            }
        }
        $colors = array_values($colorMap);

        // ============ XÂY DỰNG MAP SIZE THEO MÀU ============
        $sizeByColor = [];
        foreach ($product->variants as $variant) {
            $colorId = $variant->color_id;
            $sizeName = $variant->size_name;
            if ($colorId && $sizeName) {
                if (!isset($sizeByColor[$colorId])) {
                    $sizeByColor[$colorId] = [];
                }
                if (!in_array($sizeName, $sizeByColor[$colorId])) {
                    $sizeByColor[$colorId][] = $sizeName;
                }
            }
        }

        $images = $product->image_url ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        if (empty($images) && $product->thumbnail) {
            $images = [$product->thumbnail];
        }

        // ============ CHUẨN BỊ DỮ LIỆU VARIANTS ============
        $variantsData = $product->variants->map(function($variant) use ($product, $preorderDiscount, $isPreorderActive) {
            $stock = (int) $variant->stock;
            
            $variantPrice = (int) $variant->price;
            $variantSalePrice = null;
            $variantIsOnSale = false;

            if ($product->is_preorder && $isPreorderActive && $preorderDiscount > 0) {
                $variantSalePrice = round($variantPrice * (1 - $preorderDiscount / 100));
                $variantIsOnSale = true;
            }

            if (!$product->is_preorder && !$variantIsOnSale) {
                $now = now();
                $campaign = Campaign::where('status', 'active')
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
                    ->whereHas('productVariants', function($query) use ($variant) {
                        $query->where('product_variant_id', $variant->id);
                    })
                    ->with('configs')
                    ->first();

                if ($campaign) {
                    $config = $campaign->configs()->first();
                    $discount = $config ? (float) $config->discount_percent : 0;
                    if ($discount > 0) {
                        $variantSalePrice = round($variantPrice * (1 - $discount / 100));
                        $variantIsOnSale = true;
                    }
                }
            }

            return [
                'id' => $variant->id,
                'color_id' => $variant->color_id,
                'size_name' => $variant->size_name,
                'price' => $variantPrice,
                'sale_price' => $variantIsOnSale ? $variantSalePrice : null,
                'is_on_sale' => $variantIsOnSale,
                'stock' => $stock,
            ];
        });

        // ============ DỮ LIỆU PRODUCT ============
        $productData = [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,

            'price' => number_format($displayPrice) . '₫',
            'oldPrice' => $hasSale && $originalPrice ? number_format($originalPrice) . '₫' : ($maxPrice > $minPriceAll ? number_format($maxPrice) . '₫' : null),
            'discount' => $hasSale ? $salePercent . '%' : $discount,

            'displayPrice' => (int) $displayPrice,
            'originalPrice' => (int) $originalPrice,
            'hasSale' => $hasSale,
            'salePercent' => $salePercent,

            'reviewCount' => $product->reviews->count(),
            'thumbnails' => $images,
            'allSizes' => $allSizes, // Tất cả size
            'colors' => $colors,
            'sizeByColor' => $sizeByColor, // Map size theo màu
            'features' => [
                ['icon' => 'verified', 'text' => 'Bảo hành 12 tháng'],
                ['icon' => 'local_shipping', 'text' => 'Miễn phí vận chuyển'],
                ['icon' => 'history', 'text' => 'Đổi trả 30 ngày'],
            ],
            'description' => $product->description,
            'material' => $product->material,
            'is_preorder' => (bool) $product->is_preorder,
            'is_preorder_active' => $isPreorderActive,
            'preorderInfo' => $preorderInfo,
            'variants' => $variantsData->toArray(),
        ];

        // Related products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get()
            ->map(function ($item) {
                $price = $item->variants->min('price') ?? 0;
                return [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'brand' => $item->brand?->name ?? '',
                    'price' => number_format($price) . '₫',
                    'image' => $item->thumbnail ?? 'https://picsum.photos/200/250',
                ];
            });

        $reviews = $product->reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'author' => $review->user?->name ?? 'Khách hàng',
                'rating' => $review->rating,
                'date' => $review->created_at->format('d/m/Y'),
                'content' => $review->comment,
            ];
        });

        return Inertia::render('Web/ProductDetail', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'totalReviews' => $reviews->count(),
        ]);
    }
}
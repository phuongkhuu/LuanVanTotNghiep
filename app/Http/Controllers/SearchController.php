<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categoryName = 'Tìm kiếm';

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);

        // Tìm kiếm theo từ khóa
        if (!empty($q)) {
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($q) {
                        $brandQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('category', function ($catQuery) use ($q) {
                        $catQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        // Áp dụng bộ lọc (tương tự CategoryController)
        $query = $this->applyFilters($query, $request);

        // Phân trang
        $products = $query->paginate(12)->withQueryString();

        // Transform dữ liệu
        $products->getCollection()->transform(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc (dựa trên tất cả sản phẩm khớp với từ khóa, có filter)
        $allProductsQuery = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        if (!empty($q)) {
            $allProductsQuery->where(function($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($q) {
                        $brandQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('category', function ($catQuery) use ($q) {
                        $catQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }
        $allProducts = $allProductsQuery->get();
        $filterData = $this->getFilterData($allProducts);

        return Inertia::render('Web/Category', [
            'search' => $q,
            'products' => $products,
            'categoryName' => $categoryName,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
            'slug' => 'tim-kiem',
        ]);
    }

    // === Các helper giống trong CategoryController ===
    private function applyFilters($query, Request $request)
    {
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    private function getFilterData($products)
    {
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        $materials = $products->pluck('material')->unique()->filter()->values();

        $colorIds = collect();
        foreach ($products as $product) {
            if ($product->variants) {
                $variantColorIds = $product->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColorIds);
            }
        }
        $colorIds = $colorIds->unique()->filter()->values();
        
        $colors = collect();
        if ($colorIds->isNotEmpty()) {
            $colors = Color::whereIn('id', $colorIds)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        }

        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $prices = [];
        foreach ($products as $product) {
            if ($product->variants) {
                $minPrice = $product->variants->min('price') ?? 0;
                $maxPrice = $product->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors,
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    private function calculateSalePrice($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $salePrice = $minPrice;
        $discountPercent = 0;
        $isOnSale = false;
        $now = now();

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
                
                $tiers = $preorder->tiers ?? [];
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $discountPercent = $tier['discount'] ?? 0;
                        break;
                    }
                }
                
                if ($discountPercent == 0 && !empty($tiers)) {
                    $discountPercent = $tiers[0]['discount'] ?? 0;
                }
                
                if ($discountPercent > 0) {
                    $salePrice = round($minPrice * (1 - $discountPercent / 100));
                    $isOnSale = true;
                }
            }
        }

        if (!$product->is_preorder) {
            $variantIds = $product->variants->pluck('id')->toArray();
            
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
                    ->get();

                foreach ($campaigns as $campaign) {
                    $config = $campaign->configs()->first();
                    $currentDiscount = $config ? (float) $config->discount_percent : 0;
                    if ($currentDiscount > $discountPercent) {
                        $discountPercent = $currentDiscount;
                    }
                }

                if ($discountPercent > 0) {
                    $salePrice = round($minPrice * (1 - $discountPercent / 100));
                    $isOnSale = true;
                }
            }
        }

        return [
            'original_price' => $minPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $isOnSale,
        ];
    }

    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        
        $saleInfo = $this->calculateSalePrice($product);
        
        $displayPrice = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $minPrice;
        $originalPrice = $saleInfo['is_on_sale'] ? $minPrice : ($maxPrice > $minPrice ? $maxPrice : null);
        $discount = $saleInfo['is_on_sale'] ? $saleInfo['discount_percent'] . '%' : null;

        if ($discount) {
            $badge = "-$discount";
            $badgeClass = 'bg-primary text-white';
        } elseif ($product->is_preorder) {
            $badge = 'Pre-order';
            $badgeClass = 'bg-amber-600 text-white';
        } elseif ($product->created_at->diffInDays(now()) <= 7) {
            $badge = 'New';
            $badgeClass = 'bg-green-500 text-white';
        } else {
            $badge = null;
            $badgeClass = '';
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($displayPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $badge,
            'badgeClass' => $badgeClass,
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'is_on_sale' => $saleInfo['is_on_sale'],
            'discount_percent' => $saleInfo['discount_percent'],
            'sale_price' => $saleInfo['is_on_sale'] ? number_format($saleInfo['sale_price']) . 'đ' : null,
        ];
    }
}
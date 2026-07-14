<?php
// app/Http/Controllers/CategoryController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

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

    /**
     * Tính giá sale cho sản phẩm
     */
    private function calculateSalePrice($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $salePrice = $minPrice;
        $discountPercent = 0;
        $isOnSale = false;
        $now = now();

        $variantIds = $product->variants->pluck('id')->toArray();

        if (!empty($variantIds)) {
            // Kiểm tra campaign
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

            // Kiểm tra pre-order
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
                            if ($preorderDiscount > $discountPercent) {
                                $discountPercent = $preorderDiscount;
                            }
                            break;
                        }
                    }
                }
            }

            if ($discountPercent > 0) {
                $salePrice = $minPrice * (1 - $discountPercent / 100);
                $salePrice = round($salePrice);
                $isOnSale = true;
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
        
        // Tính sale price
        $saleInfo = $this->calculateSalePrice($product);
        
        $displayPrice = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $minPrice;
        $originalPrice = $saleInfo['is_on_sale'] ? $minPrice : ($maxPrice > $minPrice ? $maxPrice : null);
        $discount = $saleInfo['is_on_sale'] ? $saleInfo['discount_percent'] . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($displayPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            // Thêm thông tin sale
            'is_on_sale' => $saleInfo['is_on_sale'],
            'discount_percent' => $saleInfo['discount_percent'],
            'sale_price' => $saleInfo['is_on_sale'] ? number_format($saleInfo['sale_price']) . 'đ' : null,
        ];
    }
}
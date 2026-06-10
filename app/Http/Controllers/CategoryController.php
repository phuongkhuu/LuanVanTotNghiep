<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show($slug)
    {
        // 1. Slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($slug);
        }

        // 2. Tìm danh mục chính xác
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($slug, $category);
        }

        // 3. Xử lý slug có dấu gạch ngang (ví dụ: cap-tui, balo-laptop)
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
            return $this->showProductsByMultipleCategories($slug, $matchingCategories);
        }

        // 4. Coi slug là thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($slug, $brand);
        }

        // 5. Không tìm thấy gì
        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    /**
     * Hiển thị tất cả sản phẩm
     */
    private function showAllProducts($slug)
    {
        $categoryName = 'Tất cả sản phẩm';
        $products = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::whereIn('id', $products->pluck('brand_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Hiển thị sản phẩm theo một danh mục
     */
    private function showProductsByCategory($slug, $category)
    {
        $categoryName = $category->name;
        $products = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::whereIn('id', $products->pluck('brand_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Hiển thị sản phẩm từ nhiều danh mục (kết quả tìm kiếm từ khóa)
     */
    private function showProductsByMultipleCategories($slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $products = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::whereIn('id', $products->pluck('brand_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Hiển thị sản phẩm theo thương hiệu
     */
    private function showProductsByBrand($slug, $brand)
    {
        $categoryName = $brand->name;
        $products = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        // Chỉ hiển thị thương hiệu này trong bộ lọc (có thể thay đổi nếu muốn hiển thị tất cả)
        $brands = Brand::where('id', $brand->id)->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Map sản phẩm thành định dạng frontend
     */
    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . '₫',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . '₫' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $colorIds = $colorIds->merge($productModel->variants->pluck('color_id'));
            }
        }
        return $colorIds->unique()->filter()->values();
    }
}
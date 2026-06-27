<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'variants.color'])
            ->where('id', $id)
            ->where('status', 1)
            ->firstOrFail();

        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        $sizes = $product->variants->pluck('size_name')->unique()->filter()->values();

        $colors = $product->variants->map(function ($variant) {
            if ($variant->color) {
                return [
                    'value' => $variant->color->code ?? '#000000',
                    'label' => $variant->color->name
                ];
            }
            return null;
        })->filter()->unique('value')->values();

        // ---- LẤY MẢNG ẢNH ----
        $images = $product->image_url ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        // Nếu không có ảnh, fallback thumbnail
        if (empty($images) && $product->thumbnail) {
            $images = [$product->thumbnail];
        }

        $features = [
            ['icon' => 'verified', 'text' => 'Bảo hành 12 tháng'],
            ['icon' => 'local_shipping', 'text' => 'Miễn phí vận chuyển'],
            ['icon' => 'history', 'text' => 'Đổi trả 30 ngày'],
        ];

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get()
            ->map(function ($item) {
                $price = $item->variants->min('price') ?? 0;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand?->name ?? '',
                    'price' => number_format($price) . '₫',
                    'image' => $item->thumbnail ?? 'https://picsum.photos/200/250',
                ];
            });

        $reviews = [
            [
                'id' => 1,
                'author' => 'Nguyễn Văn A',
                'rating' => 5,
                'date' => now()->subDays(5)->format('d/m/Y'),
                'content' => 'Sản phẩm chất lượng, đúng mô tả.'
            ],
            [
                'id' => 2,
                'author' => 'Trần Thị B',
                'rating' => 4,
                'date' => now()->subDays(10)->format('d/m/Y'),
                'content' => 'Rất hài lòng, sẽ ủng hộ dài dài.'
            ],
        ];
        $totalReviews = 128; // placeholder

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => number_format($minPrice) . '₫',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . '₫' : null,
            'discount' => $discount,
            'reviewCount' => $totalReviews,
            'thumbnails' => $images,        // mảng ảnh
            'sizes' => $sizes,
            'colors' => $colors,
            'features' => $features,
            'description' => $product->description,
            'material' => $product->material,
            // Thêm nếu cần: 'image_url' => $images (để tương thích)
        ];

        return Inertia::render('Web/ProductDetail', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'totalReviews' => $totalReviews,
        ]);
    }
}
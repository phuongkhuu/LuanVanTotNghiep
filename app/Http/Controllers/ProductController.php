<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
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

        $images = $product->image_url ?? [];
        if (!is_array($images)) {
            $images = [];
        }
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

        $totalReviews = $reviews->count();
        $reviewCount = $totalReviews; 

        $productData = [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'price' => number_format($minPrice) . '₫',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . '₫' : null,
            'discount' => $discount,
            'reviewCount' => $reviewCount,
            'thumbnails' => $images,
            'sizes' => $sizes,
            'colors' => $colors,
            'features' => $features,
            'description' => $product->description,
            'material' => $product->material,
            'is_preorder' => (bool) $product->is_preorder,
            'variants' => $product->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_name' => $variant->size_name,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                ];
            })->toArray(),
        ];

        return Inertia::render('Web/ProductDetail', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,       
            'totalReviews' => $totalReviews,
        ]);
    }
}
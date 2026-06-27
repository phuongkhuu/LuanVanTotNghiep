<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\News;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $hotSales = Product::where('is_featured', 1)
            ->with(['variants' => function($q) {
                $q->select('product_id', 'price');
            }])
            ->take(4)
            ->get()
            ->map(function ($product) {
                $minPrice = $product->variants->min('price') ?? 0;
                $maxPrice = $product->variants->max('price') ?? $minPrice;
                $discount = $maxPrice > $minPrice ? round((1 - $minPrice / $maxPrice) * 100) : 0;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->thumbnail ?? 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&h=600&fit=crop',
                    'discount' => $discount,
                    'salePrice' => $minPrice,
                    'originalPrice' => $maxPrice,
                    'rating' => 5,
                    'reviews' => 0,
                ];
            });

        $trending = Product::where('is_preorder', 0)
            ->with(['variants' => function($q) {
                $q->select('product_id', 'price');
            }])
            ->orderBy('id', 'desc')
            ->take(4)
            ->get()
            ->map(function ($product) {
                $minPrice = $product->variants->min('price') ?? 0;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->thumbnail ?? 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&h=600&fit=crop',
                    'price' => $minPrice,
                    'sold' => 0,
                ];
            });

        $newProducts = Product::where('is_preorder', 0)
            ->with(['variants' => function($q) {
                $q->select('product_id', 'price');
            }])
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get()
            ->map(function ($product) {
                $minPrice = $product->variants->min('price') ?? 0;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->thumbnail ?? 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&h=600&fit=crop',
                    'price' => $minPrice,
                ];
            });

        // Lấy tin tức (giữ nguyên là Collection)
        $news = News::orderBy('created_at', 'desc')->take(3)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'excerpt' => $item->excerpt,
                'image' => $item->image ?? 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop',
                'category' => $item->category ?? 'Tin tức',
                'date' => $item->created_at->format('d/m/Y'),
            ];
        });

        // Nếu không có tin tức, dùng dữ liệu mẫu (dưới dạng Collection)
        if ($news->isEmpty()) {
            $news = collect([
                [
                    'id' => 1,
                    'title' => 'BigBag ra mắt bộ sưu tập Xuân Hè 2024',
                    'excerpt' => 'Những thiết kế mới nhất với chất liệu thân thiện môi trường, phong cách thời trang công sở hiện đại.',
                    'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop',
                    'category' => 'Sự kiện',
                    'date' => '15/03/2024'
                ],
            ]);
        }

        // Chuyển thành array để truyền vào Inertia
        return Inertia::render('Web/Welcome', [
            'hotSales' => $hotSales,
            'trending' => $trending,
            'newProducts' => $newProducts,
            'newsList' => $news->toArray(),
        ]);
    }
}
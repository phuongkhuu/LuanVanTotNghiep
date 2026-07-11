<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\News;
use App\Models\Banner;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy banner active, sắp xếp theo thứ tự
        $banners = Banner::where('status', 1)
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

        // Nếu không có banner, dùng mặc định
        if ($banners->isEmpty()) {
            $banners = collect([
                [
                    'id' => 1,
                    'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDxx0m6cgeB_wFfg7s6Gg9fUlG74LJAjQX52e76-kLKbboHcvdGuP8wLvolaZ2nn44uSU4mSzGcMnWRrxegCgrBQPS_CJCrqTw_lR9qipVD13hl9T_DV9Vwt4PmieoYHWvSuOgDjr4TLs2YpCS6eO_P1Ya4-_gUurI8xgCqtWZq3EvAe9WrB0_PXR8pDs-UdKo5u7vHbg-s3eYwYc1YpaZsyCDVrp1oAxlY5NkvxU8DCvx9sj5PwWBzawIL86tZy9He4cl9TZdngHc',
                    'link' => null,
                    'campaign' => null,
                ],
                [
                    'id' => 2,
                    'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCp5eQ5SZCwA43e9ZQV6q5AsixqVrngZDfmTBxJnnZZnN9FJ-UksaoW1_6ST0Oc6LoiJEgpvMf4K1zxMWMDQMiSsoVTBNGkDP_gHl8zHBONErOgONG9qdZ1Uj2M143jhRomrMwOr7m_k66Z1qw8Dg6V-3CBkzDQGEdnu4uUQFh56yuIQox-XTGWy1stgcNRm_9bBcHtgvXHSzjDoLZxarh8vh22_7wpoMLjWSTigP2X-laqEhuIKyvDhR7HHBaSrePhkDvbOjOKw9c',
                    'link' => null,
                    'campaign' => null,
                ],
            ]);
        }

        // Lấy sản phẩm hot sale
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

        // Lấy sản phẩm trending
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

        // Lấy sản phẩm mới
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

        // Lấy tin tức
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

        return Inertia::render('Web/Welcome', [
            'banners' => $banners,
            'hotSales' => $hotSales,
            'trending' => $trending,
            'newProducts' => $newProducts,
            'newsList' => $news->toArray(),
        ]);
    }
}
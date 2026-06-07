<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index($type = 'normal')
    {
        // Cho phép 2 giá trị: normal (mặc định) và preorder
        $validTypes = ['normal', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'normal';

        $allProducts = Product::with(['category', 'brand', 'variants'])
            ->latest()
            ->get()
            ->map(function ($product) {
                $totalStock = $product->variants->sum('stock');
                $minPrice = $product->variants->min('price') ?? 0;

                // Xác định loại sản phẩm theo is_preorder
                $productType = $product->is_preorder ? 'preorder' : 'normal';

                // Giá sỉ tạm thời bằng giá lẻ (có thể điều chỉnh sau)
                $wholesalePrice = $minPrice;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'Chưa phân loại',
                    'price' => (int) $minPrice,
                    'wholesalePrice' => (int) $wholesalePrice,
                    'stock' => $totalStock,
                    'type' => $productType,   // 'normal' hoặc 'preorder'
                    'image' => $product->thumbnail ?? 'https://picsum.photos/40/40',
                    'status' => $product->status ? 'active' : 'inactive',
                ];
            });

        return Inertia::render('Admin/Products', [
            'type' => $type,
            'initialProducts' => $allProducts,
        ]);
    }
}
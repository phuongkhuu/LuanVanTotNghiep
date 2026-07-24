<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Discount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class WholesaleController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tham số từ query string
        $variantId = $request->query('variant_id');
        $productId = $request->query('product_id');
        $defaultQuantity = (int) $request->query('quantity', 1);
        $defaultColor = $request->query('color', '');
        $defaultSize = $request->query('size', '');

        $selectedVariant = null;
        $selectedProduct = null;

        // Ưu tiên lấy theo variant_id
        if ($variantId) {
            $selectedVariant = ProductVariant::with(['product', 'color', 'product.brand', 'product.category'])
                ->find($variantId);
            if ($selectedVariant) {
                $selectedProduct = $selectedVariant->product;
            }
        } elseif ($productId) {
            $selectedProduct = Product::with(['variants', 'variants.color', 'brand', 'category'])
                ->find($productId);
            if ($selectedProduct && $selectedProduct->variants->isNotEmpty()) {
                $selectedVariant = $selectedProduct->variants->first();
            }
        }

        // Fallback: nếu không có sản phẩm, lấy sản phẩm mới nhất
        if (!$selectedProduct) {
            $selectedProduct = Product::with(['variants', 'variants.color', 'brand', 'category'])
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($selectedProduct && $selectedProduct->variants->isNotEmpty()) {
                $selectedVariant = $selectedProduct->variants->first();
            }
        }

        // Nếu sản phẩm là pre-order, chuyển hướng hoặc hiển thị thông báo
        if ($selectedProduct && $selectedProduct->is_preorder) {
            return redirect()->route('home')->with('error', 'Sản phẩm Pre-order không áp dụng mua sỉ.');
        }

        // ==== LẤY DANH SÁCH DISCOUNT ĐANG ACTIVE ====
        $discounts = Discount::where('is_active', true)
            ->orderBy('min_quantity', 'asc')
            ->get()
            ->map(fn($d) => [
                'min_quantity' => (int) $d->min_quantity,
                'discount_percent' => (float) $d->discount_percent,
            ])
            ->toArray();

        // Chuẩn bị dữ liệu sản phẩm
        $productData = null;
        if ($selectedProduct) {
            $variants = $selectedProduct->variants;
            $minPrice = $variants->min('price') ?? 0;
            $maxPrice = $variants->max('price') ?? $minPrice;

            // Xác định giá sale từ variant đã chọn hoặc variant đầu tiên
            $targetVariant = $selectedVariant ?? $variants->first();
            $originalPrice = $targetVariant ? $targetVariant->price : $minPrice;
            $salePrice = $targetVariant && $targetVariant->is_on_sale && $targetVariant->sale_price
                ? $targetVariant->sale_price
                : $originalPrice;

            $discountPercent = 0;
            if ($originalPrice > 0 && $salePrice < $originalPrice) {
                $discountPercent = round((1 - $salePrice / $originalPrice) * 100);
            }

            // Chi tiết variants
            $variantsData = $variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color_name' => $variant->color ? $variant->color->name : '',
                    'color_id' => $variant->color_id,
                    'size_name' => $variant->size_name ?? '',
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'is_on_sale' => $variant->is_on_sale,
                    'stock' => $variant->stock,
                ];
            })->toArray();

            $colors = $variants
                ->pluck('color')
                ->filter()
                ->unique('id')
                ->values()
                ->map(fn($color) => [
                    'id' => $color->id,
                    'name' => $color->name,
                    'code' => $color->code,
                ])
                ->toArray();

            $sizes = $variants
                ->pluck('size_name')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $productData = [
                'id' => $selectedProduct->id,
                'name' => $selectedProduct->name,
                'description' => $selectedProduct->description,
                'image' => $this->getProductImage($selectedProduct),
                'base_price' => $minPrice,
                'max_price' => $maxPrice,
                'sale_price' => $salePrice,
                'original_price' => $originalPrice,
                'discount_percent' => $discountPercent,
                'stock' => $targetVariant ? $targetVariant->stock : 0,
                'variant_id' => $targetVariant ? $targetVariant->id : null,
                'variants' => $variantsData,
                'colors' => $colors,
                'sizes' => $sizes,
                'brand' => $selectedProduct->brand ? $selectedProduct->brand->name : null,
                'category' => $selectedProduct->category ? $selectedProduct->category->name : null,
                'is_preorder' => $selectedProduct->is_preorder ?? false,
                'is_on_sale' => ($salePrice < $originalPrice),
            ];
        }

        // Lấy sản phẩm gợi ý (không phải pre-order)
        $suggestedProducts = Product::with(['variants', 'brand'])
            ->where('status', 1)
            ->where('is_preorder', 0)
            ->where('id', '!=', $selectedProduct?->id)
            ->limit(4)
            ->get()
            ->map(function ($product) {
                $minPrice = $product->variants->min('price') ?? 0;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $this->getProductImage($product),
                    'price' => $minPrice,
                    'brand' => $product->brand ? $product->brand->name : null,
                    'slug' => $product->slug,
                ];
            });

        return Inertia::render('Web/Wholesale', [
            'selectedProduct' => $productData,
            'suggestedProducts' => $suggestedProducts,
            'defaultQuantity' => $defaultQuantity,
            'defaultColor' => $defaultColor,
            'defaultSize' => $defaultSize,
            'discounts' => $discounts,
        ]);
    }

    /**
     * Xử lý đặt hàng sỉ (chỉ thanh toán qua PayOS)
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
        ]);

        $variant = ProductVariant::with('product')->find($validated['variant_id']);
        if (!$variant) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }

        // Kiểm tra tồn kho
        if ($variant->stock < $validated['quantity']) {
            return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
        }

        // Tạo đơn hàng thông qua PaymentController
        $paymentController = app(\App\Http\Controllers\PaymentController::class);

        $orderRequest = new \Illuminate\Http\Request([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'shipping_address' => $validated['shipping_address'],
            'note' => $validated['note'] ?? null,
            'payment_method' => 'payos',
            'items' => [
                [
                    'id' => $validated['variant_id'],
                    'quantity' => $validated['quantity'],
                    'price' => $variant->is_on_sale && $variant->sale_price ? $variant->sale_price : $variant->price,
                ]
            ],
            'total_amount' => ($variant->is_on_sale && $variant->sale_price ? $variant->sale_price : $variant->price) * $validated['quantity'],
            'order_type' => 'wholesale',
            'promo_code' => null,
            'discount_amount' => 0,
        ]);

        try {
            $response = $paymentController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                // Trả về order_id để frontend chuyển hướng đến PayOS
                return response()->json([
                    'success' => true,
                    'order_id' => $responseData->order->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $responseData->message ?? 'Có lỗi xảy ra khi tạo đơn hàng',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Wholesale order error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy ảnh đại diện của sản phẩm
     */
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

    /**
     * Kiểm tra chuỗi JSON
     */
    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Discount;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestDetail;
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

        // Tạo đơn hàng thông qua PaymentController (gửi request dạng API)
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

        // Đánh dấu request là API call
        $orderRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

        try {
            $response = $paymentController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                // Trả về redirect_url cho frontend
                return response()->json([
                    'success' => true,
                    'order_id' => $responseData->order_id ?? null,
                    'redirect_url' => $responseData->redirect_url ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $responseData->message ?? 'Có lỗi xảy ra khi tạo đơn hàng',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Wholesale order error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xử lý đặt hàng sỉ + lưu yêu cầu báo giá (B2B) cùng lúc
     */
    public function placeOrderWithQuote(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'company'        => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'city'           => 'nullable|string|max:100',
            'district'       => 'nullable|string|max:100',
            'ward'           => 'nullable|string|max:100',
            'address'        => 'required|string|max:500',
            'note'           => 'nullable|string|max:500',
            'requirements'   => 'nullable|string|max:1000',
            'variant_id'     => 'required|exists:product_variants,id',
            'quantity'       => 'required|integer|min:1',
            'color'          => 'nullable|string|max:50',
            'size'           => 'nullable|string|max:50',
        ]);

        $variant = ProductVariant::with('product')->find($validated['variant_id']);
        if (!$variant) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }

        if ($variant->stock < $validated['quantity']) {
            return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
        }

        // 2. Lưu yêu cầu báo giá
        $unitPrice = $variant->is_on_sale && $variant->sale_price ? $variant->sale_price : $variant->price;
        $total = $unitPrice * $validated['quantity'];

        $quoteRequest = QuoteRequest::create([
            'user_id'       => auth()->id() ?? null,
            'company_name'  => $validated['company'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'],
            'total_quantity'=> $validated['quantity'],
            'total'         => $total,
            'requirement'   => $validated['requirements'] ?? null,
            'logo_file'     => null,
            'status'        => 'pending',
        ]);

        QuoteRequestDetail::create([
            'quote_request_id'    => $quoteRequest->id,
            'product_variant_id'  => $variant->id,
            'quantity'            => $validated['quantity'],
        ]);

        // 3. Tạo đơn hàng sỉ (wholesale) – gọi PaymentController
        $paymentController = app(\App\Http\Controllers\PaymentController::class);

        $orderRequest = new \Illuminate\Http\Request([
            'customer_name'    => $validated['company'],
            'customer_phone'   => $validated['phone'],
            'customer_email'   => $validated['email'],
            'receiver_name'    => $validated['company'],
            'receiver_phone'   => $validated['phone'],
            'shipping_address' => $validated['address'],
            'note'             => $validated['note'] ?? null,
            'payment_method'   => 'payos',
            'items' => [
                [
                    'id'       => $validated['variant_id'],
                    'quantity' => $validated['quantity'],
                    'price'    => $unitPrice,
                ]
            ],
            'total_amount'     => $total,
            'order_type'       => 'wholesale',
            'promo_code'       => null,
            'discount_amount'  => 0,
        ]);

        // Quan trọng: Gán session từ request hiện tại để PaymentController có thể xóa session
        $orderRequest->setLaravelSession($request->session());
        // Không cần setUserResolver, chỉ cần session là đủ

        $orderRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

        try {
            $response = $paymentController->store($orderRequest);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $response->getData();
                if ($responseData->success) {
                    return response()->json([
                        'success'      => true,
                        'order_id'     => $responseData->order_id ?? null,
                        'redirect_url' => $responseData->redirect_url ?? null,
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => $responseData->message ?? 'Có lỗi xảy ra khi tạo đơn hàng',
                ], 400);
            }

            // Fallback nếu vẫn là RedirectResponse
            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $response->getTargetUrl()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể xác định phản hồi từ PaymentController'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Place order with quote error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lưu yêu cầu báo giá (B2B) từ form bên phải trang mua sỉ
     */
    public function submitRequest(Request $request)
    {
        $validated = $request->validate([
            'company'      => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|max:20',
            'city'         => 'nullable|string|max:100',
            'district'     => 'nullable|string|max:100',
            'ward'         => 'nullable|string|max:100',
            'address'      => 'required|string|max:500',
            'note'         => 'nullable|string|max:500',
            'requirements' => 'nullable|string|max:1000',
            'product_id'   => 'nullable|exists:products,id',
            'variant_id'   => 'nullable|exists:product_variants,id',
            'quantity'     => 'required|integer|min:1',
            'color'        => 'nullable|string|max:50',
            'size'         => 'nullable|string|max:50',
        ]);

        // Lấy variant và product
        $variant = null;
        $product = null;

        if (!empty($validated['variant_id'])) {
            $variant = ProductVariant::with('product')->find($validated['variant_id']);
            if ($variant) {
                $product = $variant->product;
            }
        } elseif (!empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                $variant = $product->variants->first(); // lấy variant đầu tiên nếu không chọn cụ thể
            }
        }

        if (!$product || !$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm hoặc biến thể. Vui lòng chọn lại.'
            ], 404);
        }

        // Tính giá (ưu tiên sale_price nếu có)
        $unitPrice = $variant->is_on_sale && $variant->sale_price
            ? $variant->sale_price
            : $variant->price;

        $total = $unitPrice * $validated['quantity'];

        // Tạo quote request
        $quoteRequest = QuoteRequest::create([
            'user_id'       => auth()->id() ?? null,
            'company_name'  => $validated['company'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'],
            'total_quantity'=> $validated['quantity'],
            'total'         => $total,
            'requirement'   => $validated['requirements'] ?? null,
            'logo_file'     => null,
            'status'        => 'pending',
        ]);

        // Tạo chi tiết báo giá
        QuoteRequestDetail::create([
            'quote_request_id'    => $quoteRequest->id,
            'product_variant_id'  => $variant->id,
            'quantity'            => $validated['quantity'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu báo giá đã được gửi thành công! Chúng tôi sẽ liên hệ trong 30 phút.',
            'quote_id' => $quoteRequest->id,
        ]);
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
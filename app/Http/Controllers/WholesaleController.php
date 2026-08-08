<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Discount;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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

        $orderRequest->setLaravelSession($request->session());
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
     * Tạo QuoteRequest, Order và Payment (bank_transfer, amount=0)
     */
    public function submitRequest(Request $request)
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi yêu cầu mua sỉ.'
            ], 401);
        }

        // 2. Validate dữ liệu
        $validated = $request->validate([
            'company'       => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'required|string|max:20',
            'tax_code'      => 'nullable|string|max:50',
            'delivery_date' => 'nullable|date|after:today',
            'city'          => 'nullable|string|max:100',
            'district'      => 'nullable|string|max:100',
            'ward'          => 'nullable|string|max:100',
            'address'       => 'required|string|max:500',
            'note'          => 'nullable|string|max:500',
            'requirements'  => 'nullable|string|max:1000',
            'variant_id'    => 'required|exists:product_variants,id',
            'quantity'      => 'required|integer|min:50',
            'color'         => 'nullable|string|max:50',
            'size'          => 'nullable|string|max:50',
        ]);

        // 3. Lấy variant và product
        $variant = ProductVariant::with('product')->find($validated['variant_id']);
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }

        // 4. Kiểm tra trạng thái hoạt động của doanh nghiệp qua mã số thuế
        if (!empty($validated['tax_code'])) {
            $taxCode = preg_replace('/[^0-9]/', '', $validated['tax_code']);
            $cacheKey = 'tax_info_' . $taxCode;
            
            // Kiểm tra cache
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                if (isset($cachedData['status']) && !$this->checkBusinessStatus($cachedData['status'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Doanh nghiệp đã ngưng hoạt động, không thể đặt hàng. Vui lòng sử dụng mã số thuế của công ty đang hoạt động.'
                    ], 400);
                }
            } else {
                // Tra cứu API để kiểm tra trạng thái
                try {
                    $response = Http::timeout(10)->get("https://api.vietqr.io/v2/business/{$taxCode}");
                    if ($response->successful()) {
                        $data = $response->json();
                        if ($data && isset($data['code']) && $data['code'] === '00') {
                            $business = $data['data'] ?? [];
                            $status = $business['status'] ?? $business['businessStatus'] ?? 'Đang hoạt động';
                            
                            if (!$this->checkBusinessStatus($status)) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Doanh nghiệp đã ngưng hoạt động, không thể đặt hàng. Vui lòng sử dụng mã số thuế của công ty đang hoạt động.'
                                ], 400);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Nếu API lỗi, bỏ qua kiểm tra (vẫn cho phép đặt hàng)
                    Log::warning('Không thể kiểm tra trạng thái doanh nghiệp: ' . $e->getMessage());
                }
            }
        }

        // Tính giá
        $unitPrice = $variant->is_on_sale && $variant->sale_price
            ? $variant->sale_price
            : $variant->price;
        $total = $unitPrice * $validated['quantity'];

        // 5. Gom các thông tin bổ sung vào một mảng và encode JSON cho QuoteRequest
        $extraData = [
            'address'       => $validated['address'],
            'city'          => $validated['city'],
            'district'      => $validated['district'],
            'ward'          => $validated['ward'],
            'tax_code'      => $validated['tax_code'],
            'delivery_date' => $validated['delivery_date'],
            'requirements'  => $validated['requirements'],
            'note'          => $validated['note'],
        ];
        $extraData = array_filter($extraData, function ($value) {
            return !is_null($value) && $value !== '';
        });
        $requirementJson = json_encode($extraData, JSON_UNESCAPED_UNICODE);

        // 6. Xây dựng nội dung ghi chú cho Order
        $orderNote = '';
        if (!empty($validated['note'])) {
            $orderNote .= $validated['note'] . "\n\n";
        }
        $orderNote .= "--- THÔNG TIN BỔ SUNG ---\n";
        if (!empty($validated['email'])) {
            $orderNote .= "Email: {$validated['email']}\n";
        }
        if (!empty($validated['tax_code'])) {
            $orderNote .= "Mã số thuế: {$validated['tax_code']}\n";
        }
        if (!empty($validated['delivery_date'])) {
            $orderNote .= "Ngày cần nhận: {$validated['delivery_date']}\n";
        }
        if (!empty($validated['address'])) {
            $addressParts = [
                $validated['address'],
                $validated['ward'],
                $validated['district'],
                $validated['city']
            ];
            $fullAddress = implode(', ', array_filter($addressParts));
            $orderNote .= "Địa chỉ giao hàng: {$fullAddress}\n";
        }
        if (!empty($validated['requirements'])) {
            $orderNote .= "Yêu cầu đặc biệt: {$validated['requirements']}\n";
        }
        $orderNote .= "-------------------------";

        // 7. Lưu yêu cầu và đơn hàng vào DB
        try {
            DB::beginTransaction();

            // --- Tạo yêu cầu báo giá ---
            $quoteRequest = QuoteRequest::create([
                'user_id'        => Auth::id(),
                'company_name'   => $validated['company'],
                'email'          => $validated['email'],
                'phone'          => $validated['phone'],
                'total_quantity' => $validated['quantity'],
                'total'          => $total,
                'requirement'    => $requirementJson,
                'logo_file'      => null,
                'status'         => 'pending',
            ]);

            QuoteRequestDetail::create([
                'quote_request_id'   => $quoteRequest->id,
                'product_variant_id' => $variant->id,
                'quantity'           => $validated['quantity'],
            ]);

            // --- Tạo đơn hàng bán sỉ (wholesale) với trạng thái chờ xác nhận ---
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_code'       => 'wholesale',
                'customer_name'    => $validated['company'],
                'customer_phone'   => $validated['phone'],
                'customer_email'   => $validated['email'],
                'receiver_name'    => $validated['company'],
                'receiver_phone'   => $validated['phone'],
                'shipping_address' => $validated['address'],
                'note'             => $orderNote,
                'total_amount'     => $total,
                'discount_amount'  => 0,
                'final_amount'     => $total,
                'deposit_amount'   => 0,
                'remaining_amount' => $total,
                'payment_status'   => 'pending',
                'order_status'     => 0, // 0: Chờ xác nhận
                'shipping_fee'     => 0,
            ]);

            // Sinh mã đơn hàng
            $order->order_number = 'S' . now()->format('dmy') . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();


            // --- Tạo chi tiết đơn hàng ---
            OrderDetail::create([
                'order_id'           => $order->id,
                'product_variant_id' => $variant->id,
                'quantity'           => $validated['quantity'],
                'unit_price'         => $unitPrice,
                'subtotal'           => $total,
            ]);

            // --- Tạo bản ghi thanh toán ---
            Payment::create([
                'order_id'          => $order->id,
                'transaction_code'  => 'PAY-WS-' . $order->id . '-' . time(),
                'payment_method'    => 'bank_transfer',
                'amount'            => 0,
                'payment_date'      => now(),
                'status'            => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gửi yêu cầu thành công! Chúng tôi sẽ liên hệ trong 30 phút.',
                'quote_id' => $quoteRequest->id,
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit quote request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * Tra cứu thông tin công ty qua mã số thuế - Sử dụng API VietQR
     * 
     * @param string $taxCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookupTaxCode($taxCode)
    {
        // Validate mã số thuế
        if (empty($taxCode) || strlen($taxCode) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Mã số thuế không hợp lệ. Vui lòng nhập đúng 10-14 chữ số.'
            ], 400);
        }

        // Loại bỏ ký tự đặc biệt, chỉ giữ số
        $taxCode = preg_replace('/[^0-9]/', '', $taxCode);
        
        // Kiểm tra cache
        $cacheKey = 'tax_info_' . $taxCode;
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            
            // Kiểm tra trạng thái hoạt động từ cache
            if (isset($cachedData['status']) && !$this->checkBusinessStatus($cachedData['status'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động. Vui lòng kiểm tra lại.',
                    'data' => $cachedData,
                    'status' => $cachedData['status']
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'data' => $cachedData,
                'message' => 'Tra cứu thành công (từ cache)'
            ]);
        }

        try {
            // ==========================================
            // GỌI API VIETQR ĐỂ TRA CỨU
            // ==========================================
            $response = Http::timeout(10)->get("https://api.vietqr.io/v2/business/{$taxCode}");
            
            // Log để debug
            Log::info('VietQR API response', [
                'tax_code' => $taxCode,
                'status' => $response->status(),
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Kiểm tra response code từ VietQR
                if ($data && isset($data['code']) && $data['code'] === '00') {
                    $business = $data['data'] ?? [];
                    
                    // Lấy thông tin từ API
                    $companyName = $business['companyName'] ?? $business['name'] ?? '';
                    $email = $business['email'] ?? '';
                    $phone = $business['phone'] ?? $business['tel'] ?? '';
                    $status = $business['status'] ?? $business['businessStatus'] ?? 'Đang hoạt động';
                    
                    // Nếu có thông tin, kiểm tra trạng thái hoạt động
                    if (!empty($companyName)) {
                        // Kiểm tra trạng thái hoạt động kinh doanh
                        $isActive = $this->checkBusinessStatus($status);
                        
                        $result = [
                            'company_name' => $companyName,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => $status,
                            'is_active' => $isActive
                        ];
                        
                        if (!$isActive) {
                            // Lưu cache 24 giờ với trạng thái không hoạt động
                            Cache::put($cacheKey, $result, 86400);
                            
                            return response()->json([
                                'success' => false,
                                'message' => 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động. Vui lòng kiểm tra lại.',
                                'data' => $result,
                                'status' => $status
                            ], 400);
                        }
                        
                        // Doanh nghiệp đang hoạt động
                        // Lưu cache 24 giờ
                        Cache::put($cacheKey, $result, 86400);
                        
                        return response()->json([
                            'success' => true,
                            'data' => $result,
                            'message' => 'Tra cứu thành công'
                        ]);
                    }
                }
            }

            // ==========================================
            // FALLBACK: DỮ LIỆU MOCK KHI API KHÔNG HOẠT ĐỘNG
            // ==========================================
            $mockData = $this->getMockCompanyData($taxCode);
            if ($mockData) {
                // Kiểm tra trạng thái từ mock data
                $status = $mockData['status'] ?? 'Đang hoạt động';
                $isActive = $this->checkBusinessStatus($status);
                $mockData['is_active'] = $isActive;
                
                if (!$isActive) {
                    // Lưu cache 1 giờ cho mock data không hoạt động
                    Cache::put($cacheKey, $mockData, 3600);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động. Vui lòng kiểm tra lại.',
                        'data' => $mockData,
                        'status' => $status
                    ], 400);
                }
                
                // Lưu cache 1 giờ cho mock data
                Cache::put($cacheKey, $mockData, 3600);
                
                return response()->json([
                    'success' => true,
                    'data' => $mockData,
                    'message' => 'Tra cứu thành công (dữ liệu mẫu)'
                ]);
            }

            // Không tìm thấy thông tin
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin công ty với mã số thuế này. Vui lòng kiểm tra lại hoặc nhập thủ công.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Tax lookup error: ' . $e->getMessage(), [
                'tax_code' => $taxCode,
                'trace' => $e->getTraceAsString()
            ]);

            // Khi API lỗi, thử dùng dữ liệu mock
            $mockData = $this->getMockCompanyData($taxCode);
            if ($mockData) {
                // Kiểm tra trạng thái từ mock data
                $status = $mockData['status'] ?? 'Đang hoạt động';
                $isActive = $this->checkBusinessStatus($status);
                $mockData['is_active'] = $isActive;
                
                if (!$isActive) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động. Vui lòng kiểm tra lại.',
                        'data' => $mockData,
                        'status' => $status
                    ], 400);
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $mockData,
                    'message' => 'Tra cứu thành công (dữ liệu mẫu)'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tra cứu mã số thuế. Vui lòng thử lại sau hoặc nhập thủ công.'
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái hoạt động kinh doanh
     * 
     * @param string $status
     * @return bool
     */
    private function checkBusinessStatus($status)
    {
        // Danh sách các trạng thái "đang hoạt động"
        $activeStatuses = [
            'Đang hoạt động',
            'Đang hoạt động (đã đăng ký)',
            'Hoạt động',
            'Active',
            'Đang hoạt động (chưa đăng ký)',
            'active',
            'ACTIVE',
            'Đang hoạt động'
        ];
        
        // Danh sách các trạng thái "ngưng hoạt động"
        $inactiveStatuses = [
            'Ngưng hoạt động',
            'Đã giải thể',
            'Đã ngừng hoạt động',
            'Tạm ngừng hoạt động',
            'Inactive',
            'inactive',
            'INACTIVE',
            'Ngừng hoạt động',
            'Không hoạt động',
            'Đã đóng mã số thuế',
            'Đã chấm dứt hoạt động',
            'chấm dứt hiệu lực',
            'NNT ngừng hoạt động'
        ];
        
        // Kiểm tra nếu status nằm trong danh sách không hoạt động
        foreach ($inactiveStatuses as $inactive) {
            if (stripos($status, $inactive) !== false) {
                return false;
            }
        }
        
        // Kiểm tra nếu status nằm trong danh sách hoạt động
        foreach ($activeStatuses as $active) {
            if (stripos($status, $active) !== false) {
                return true;
            }
        }
        
        // Mặc định: nếu status không rõ ràng, coi là đang hoạt động
        // (ưu tiên cho phép giao dịch nếu không xác định được)
        return true;
    }

    /**
     * Dữ liệu mẫu cho demo - Sử dụng khi API không hoạt động
     * 
     * @param string $taxCode
     * @return array|null
     */
    private function getMockCompanyData($taxCode)
    {
        // Xóa ký tự đặc biệt, chỉ giữ số
        $cleanTaxCode = preg_replace('/[^0-9]/', '', $taxCode);
        
        // Dữ liệu mẫu với trạng thái hoạt động
        $mockDatabase = [
            '0312345678' => [
                'company_name' => 'CÔNG TY TNHH BIGBAG VIỆT NAM',
                'email' => 'contact@bigbag.vn',
                'phone' => '02812345678',
                'status' => 'Đang hoạt động',
                'is_active' => true
            ],
            '0312345679' => [
                'company_name' => 'CÔNG TY CỔ PHẦN THƯƠNG MẠI ABC',
                'email' => 'info@abc.com.vn',
                'phone' => '02898765432',
                'status' => 'Đang hoạt động',
                'is_active' => true
            ],
            '0101234567' => [
                'company_name' => 'CÔNG TY TNHH MTV XYZ HÀ NỘI',
                'email' => 'xyz@hanoi.vn',
                'phone' => '02412345678',
                'status' => 'Đang hoạt động',
                'is_active' => true
            ],
            '0301234567' => [
                'company_name' => 'CÔNG TY CỔ PHẦN ĐẦU TƯ PHÁT TRIỂN MẠNH PHÁT',
                'email' => 'manhphat@company.com',
                'phone' => '02511234567',
                'status' => 'Ngưng hoạt động',
                'is_active' => false
            ],
            '0312345680' => [
                'company_name' => 'CÔNG TY TNHH SẢN XUẤT THƯƠNG MẠI DỊCH VỤ BÌNH MINH',
                'email' => 'binhminh@company.com',
                'phone' => '02837123456',
                'status' => 'Đã giải thể',
                'is_active' => false
            ],
        ];

        // Kiểm tra chính xác
        if (isset($mockDatabase[$cleanTaxCode])) {
            return $mockDatabase[$cleanTaxCode];
        }

        // Kiểm tra gần đúng
        foreach ($mockDatabase as $key => $value) {
            if (substr($key, 0, 9) === substr($cleanTaxCode, 0, 9)) {
                return $value;
            }
        }

        return null;
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
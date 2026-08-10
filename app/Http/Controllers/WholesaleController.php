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
use Illuminate\Support\Facades\Mail;
use App\Mail\QuoteRequestSubmitted;
use App\Mail\NewQuoteRequestAdmin;

class WholesaleController extends Controller
{
    /**
     * Hiển thị trang mua sỉ
     */
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

        // Nếu sản phẩm là pre-order, chuyển hướng
        if ($selectedProduct && $selectedProduct->is_preorder) {
            return redirect()->route('home')->with('error', 'Sản phẩm Pre-order không áp dụng mua sỉ.');
        }

        // Lấy danh sách discount đang active
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

            $targetVariant = $selectedVariant ?? $variants->first();
            $originalPrice = $targetVariant ? $targetVariant->price : $minPrice;
            $salePrice = $targetVariant && $targetVariant->is_on_sale && $targetVariant->sale_price
                ? $targetVariant->sale_price
                : $originalPrice;

            $discountPercent = 0;
            if ($originalPrice > 0 && $salePrice < $originalPrice) {
                $discountPercent = round((1 - $salePrice / $originalPrice) * 100);
            }

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

        // Sản phẩm gợi ý
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
     * Lấy discount phù hợp nhất với số lượng (ưu tiên min_quantity lớn nhất)
     */
    private function getApplicableDiscount($quantity)
    {
        return Discount::where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('min_quantity', 'desc')
            ->first();
    }

    /**
     * Xử lý đặt hàng sỉ (chỉ thanh toán qua PayOS)
     * Áp dụng discount nếu có
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

        if ($variant->stock < $validated['quantity']) {
            return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
        }

        $unitPrice = $variant->is_on_sale && $variant->sale_price ? $variant->sale_price : $variant->price;
        $total = $unitPrice * $validated['quantity'];

        $discount = $this->getApplicableDiscount($validated['quantity']);
        $discountPercent = $discount ? $discount->discount_percent : 0;
        $discountAmount = $total * $discountPercent / 100;
        $finalAmount = $total - $discountAmount;

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
                    'price' => $unitPrice,
                ]
            ],
            'total_amount' => $total,
            'discount_id' => $discount ? $discount->id : null,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'order_type' => 'wholesale',
            'promo_code' => null,
        ]);

        $orderRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

        try {
            $response = $paymentController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
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
     * Áp dụng discount và gửi email
     */
    public function placeOrderWithQuote(Request $request)
    {
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

        $unitPrice = $variant->is_on_sale && $variant->sale_price ? $variant->sale_price : $variant->price;
        $total = $unitPrice * $validated['quantity'];

        $discount = $this->getApplicableDiscount($validated['quantity']);
        $discountPercent = $discount ? $discount->discount_percent : 0;
        $discountAmount = $total * $discountPercent / 100;
        $finalAmount = $total - $discountAmount;

        // Lưu yêu cầu báo giá
        $quoteRequest = QuoteRequest::create([
            'user_id'       => auth()->id() ?? null,
            'company_name'  => $validated['company'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'],
            'total_quantity'=> $validated['quantity'],
            'total'         => $finalAmount,
            'requirement'   => $validated['requirements'] ?? null,
            'logo_file'     => null,
            'status'        => 'pending',
        ]);

        QuoteRequestDetail::create([
            'quote_request_id'    => $quoteRequest->id,
            'product_variant_id'  => $variant->id,
            'quantity'            => $validated['quantity'],
        ]);

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
            'discount_id'      => $discount ? $discount->id : null,
            'discount_amount'  => $discountAmount,
            'final_amount'     => $finalAmount,
            'order_type'       => 'wholesale',
            'promo_code'       => null,
        ]);

        $orderRequest->setLaravelSession($request->session());
        $orderRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

        try {
            $response = $paymentController->store($orderRequest);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $response->getData();
                if ($responseData->success) {
                    $orderId = $responseData->order_id ?? null;
                    if ($orderId) {
                        $order = Order::with('details.productVariant.product')->find($orderId);
                        if ($order) {
                            $this->sendQuoteEmails($quoteRequest, $order);
                        }
                    }
                    return response()->json([
                        'success'      => true,
                        'order_id'     => $orderId,
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
     * Áp dụng discount và gửi email
     */
    public function submitRequest(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi yêu cầu mua sỉ.'
            ], 401);
        }

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

        $variant = ProductVariant::with('product')->find($validated['variant_id']);
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }

        // Kiểm tra trạng thái doanh nghiệp
        if (!empty($validated['tax_code'])) {
            $taxCode = preg_replace('/[^0-9]/', '', $validated['tax_code']);
            $cacheKey = 'tax_info_' . $taxCode;
            
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                if (isset($cachedData['status']) && !$this->checkBusinessStatus($cachedData['status'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Doanh nghiệp đã ngưng hoạt động, không thể đặt hàng. Vui lòng sử dụng mã số thuế của công ty đang hoạt động.'
                    ], 400);
                }
            } else {
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
                                    'message' => 'Doanh nghiệp đã ngưng hoạt động, không thể đặt hàng.'
                                ], 400);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Không thể kiểm tra trạng thái doanh nghiệp: ' . $e->getMessage());
                }
            }
        }

        // Tính toán discount
        $unitPrice = $variant->is_on_sale && $variant->sale_price ? $variant->sale_price : $variant->price;
        $total = $unitPrice * $validated['quantity'];

        $discount = $this->getApplicableDiscount($validated['quantity']);
        $discountPercent = $discount ? $discount->discount_percent : 0;
        $discountAmount = $total * $discountPercent / 100;
        $finalAmount = $total - $discountAmount;

        // Gom thông tin bổ sung
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
        $extraData = array_filter($extraData, fn($v) => !is_null($v) && $v !== '');
        $requirementJson = json_encode($extraData, JSON_UNESCAPED_UNICODE);

        // Xây dựng ghi chú cho Order
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

        try {
            DB::beginTransaction();

            // Tạo yêu cầu báo giá
            $quoteRequest = QuoteRequest::create([
                'user_id'        => Auth::id(),
                'company_name'   => $validated['company'],
                'email'          => $validated['email'],
                'phone'          => $validated['phone'],
                'total_quantity' => $validated['quantity'],
                'total'          => $finalAmount,
                'requirement'    => $requirementJson,
                'logo_file'      => null,
                'status'         => 'pending',
            ]);

            QuoteRequestDetail::create([
                'quote_request_id'   => $quoteRequest->id,
                'product_variant_id' => $variant->id,
                'quantity'           => $validated['quantity'],
            ]);

            // Tạo đơn hàng
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
                'discount_id'      => $discount ? $discount->id : null,
                'discount_amount'  => $discountAmount,
                'final_amount'     => $finalAmount,
                'deposit_amount'   => 0,
                'remaining_amount' => $finalAmount,
                'payment_status'   => 'pending',
                'order_status'     => 0,
                'shipping_fee'     => 0,
            ]);

            $order->order_number = 'S' . now()->format('dmY') . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            OrderDetail::create([
                'order_id'           => $order->id,
                'product_variant_id' => $variant->id,
                'quantity'           => $validated['quantity'],
                'unit_price'         => $unitPrice,
                'subtotal'           => $total,
            ]);

            Payment::create([
                'order_id'          => $order->id,
                'transaction_code'  => 'PAY-WS-' . $order->id . '-' . time(),
                'payment_method'    => 'bank_transfer',
                'amount'            => 0,
                'payment_date'      => now(),
                'status'            => 'pending',
            ]);

            DB::commit();

            // Gửi email sau khi commit (đã load relationship)
            $this->sendQuoteEmails($quoteRequest, $order);

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
     * Gửi email xác nhận cho khách hàng và thông báo cho admin
     */
    private function sendQuoteEmails($quoteRequest, $order)
    {
        try {
            // Load quan hệ cần thiết để tránh lỗi - dùng details (không phải orderDetails)
            $order->load('details.productVariant.product');

            // Gửi cho khách hàng
            Mail::to($quoteRequest->email)->send(new QuoteRequestSubmitted($quoteRequest, $order));
            
            // Gửi cho admin (lấy từ config, mặc định admin@bigbag.vn)
            $adminEmail = config('mail.admin_email', 'admin@bigbag.vn');
            Mail::to($adminEmail)->send(new NewQuoteRequestAdmin($quoteRequest, $order));
        } catch (\Exception $e) {
            Log::error('Không thể gửi email yêu cầu báo giá: ' . $e->getMessage());
            // Không throw lỗi để không ảnh hưởng đến luồng chính
        }
    }

    /**
     * Tra cứu thông tin công ty qua mã số thuế - Sử dụng API VietQR
     */
    public function lookupTaxCode($taxCode)
    {
        if (empty($taxCode) || strlen($taxCode) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Mã số thuế không hợp lệ. Vui lòng nhập đúng 10-14 chữ số.'
            ], 400);
        }

        $taxCode = preg_replace('/[^0-9]/', '', $taxCode);
        $cacheKey = 'tax_info_' . $taxCode;
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
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
            $response = Http::timeout(10)->get("https://api.vietqr.io/v2/business/{$taxCode}");
            Log::info('VietQR API response', ['tax_code' => $taxCode, 'status' => $response->status()]);
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data && isset($data['code']) && $data['code'] === '00') {
                    $business = $data['data'] ?? [];
                    $companyName = $business['companyName'] ?? $business['name'] ?? '';
                    $email = $business['email'] ?? '';
                    $phone = $business['phone'] ?? $business['tel'] ?? '';
                    $status = $business['status'] ?? $business['businessStatus'] ?? 'Đang hoạt động';
                    
                    if (!empty($companyName)) {
                        $isActive = $this->checkBusinessStatus($status);
                        $result = [
                            'company_name' => $companyName,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => $status,
                            'is_active' => $isActive
                        ];
                        if (!$isActive) {
                            Cache::put($cacheKey, $result, 86400);
                            return response()->json([
                                'success' => false,
                                'message' => 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động. Vui lòng kiểm tra lại.',
                                'data' => $result,
                                'status' => $status
                            ], 400);
                        }
                        Cache::put($cacheKey, $result, 86400);
                        return response()->json([
                            'success' => true,
                            'data' => $result,
                            'message' => 'Tra cứu thành công'
                        ]);
                    }
                }
            }

            // Fallback mock
            $mockData = $this->getMockCompanyData($taxCode);
            if ($mockData) {
                $status = $mockData['status'] ?? 'Đang hoạt động';
                $isActive = $this->checkBusinessStatus($status);
                $mockData['is_active'] = $isActive;
                if (!$isActive) {
                    Cache::put($cacheKey, $mockData, 3600);
                    return response()->json([
                        'success' => false,
                        'message' => 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động. Vui lòng kiểm tra lại.',
                        'data' => $mockData,
                        'status' => $status
                    ], 400);
                }
                Cache::put($cacheKey, $mockData, 3600);
                return response()->json([
                    'success' => true,
                    'data' => $mockData,
                    'message' => 'Tra cứu thành công (dữ liệu mẫu)'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin công ty với mã số thuế này. Vui lòng kiểm tra lại hoặc nhập thủ công.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Tax lookup error: ' . $e->getMessage(), ['tax_code' => $taxCode, 'trace' => $e->getTraceAsString()]);
            $mockData = $this->getMockCompanyData($taxCode);
            if ($mockData) {
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
     */
    private function checkBusinessStatus($status)
    {
        $activeStatuses = [
            'Đang hoạt động', 'Đang hoạt động (đã đăng ký)', 'Hoạt động',
            'Active', 'Đang hoạt động (chưa đăng ký)', 'active', 'ACTIVE'
        ];
        $inactiveStatuses = [
            'Ngưng hoạt động', 'Đã giải thể', 'Đã ngừng hoạt động',
            'Tạm ngừng hoạt động', 'Inactive', 'inactive', 'INACTIVE',
            'Ngừng hoạt động', 'Không hoạt động', 'Đã đóng mã số thuế',
            'Đã chấm dứt hoạt động', 'chấm dứt hiệu lực', 'NNT ngừng hoạt động'
        ];
        foreach ($inactiveStatuses as $inactive) {
            if (stripos($status, $inactive) !== false) return false;
        }
        foreach ($activeStatuses as $active) {
            if (stripos($status, $active) !== false) return true;
        }
        return true; // mặc định cho phép
    }

    /**
     * Dữ liệu mẫu cho demo
     */
    private function getMockCompanyData($taxCode)
    {
        $cleanTaxCode = preg_replace('/[^0-9]/', '', $taxCode);
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
        if (isset($mockDatabase[$cleanTaxCode])) {
            return $mockDatabase[$cleanTaxCode];
        }
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
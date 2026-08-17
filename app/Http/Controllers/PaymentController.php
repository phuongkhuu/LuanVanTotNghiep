<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\LogoPrintRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Campaign;
use App\Mail\OrderConfirmationMail;
use App\Mail\CustomOrderConfirmation;
use App\Mail\NewCustomOrderAdmin;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Tính giá sale cho variant – ưu tiên sale_price có sẵn trong variant
     */
    private function calculateSalePrice($variant)
    {
        // 1. Nếu variant đã có sale_price và is_on_sale = true, dùng luôn
        if (!empty($variant->sale_price) && $variant->is_on_sale) {
            return [
                'original_price' => (float) $variant->price,
                'sale_price'      => (float) $variant->sale_price,
                'discount_percent'=> (float) ($variant->discount_percent ?? 0),
                'is_on_sale'      => true,
            ];
        }

        // 2. Nếu không, tính từ campaign
        $originalPrice = (float) $variant->price;
        $salePrice = $originalPrice;
        $discountPercent = 0.0;
        $now = now();

        // Kiểm tra campaign (retail)
        $campaigns = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now);
                })->orWhere(function($q) {
                    $q->whereNull('start_time')
                      ->whereNull('end_time');
                });
            })
            ->whereHas('productVariants', function($query) use ($variant) {
                $query->where('product_variant_id', $variant->id);
            })
            ->with('configs')
            ->get();

        foreach ($campaigns as $campaign) {
            $config = $campaign->configs()->first();
            $currentDiscount = $config ? (float) $config->discount_percent : 0.0;
            if ($currentDiscount > $discountPercent) {
                $discountPercent = $currentDiscount;
            }
        }

        // Kiểm tra pre-order
        if ($variant->product && ($variant->product->is_preorder ?? false)) {
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $variant->product_id)
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    })->orWhere(function($q) {
                        $q->whereNull('start_time')
                          ->whereNull('end_time');
                    });
                })
                ->first();

            if ($preorder) {
                $currentBuyers = (int) ($preorder->current_buyers ?? 0);
                $tiers = $preorder->tiers ?? [];
                
                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });
                
                $preorderDiscount = 0.0;
                foreach ($tiers as $tier) {
                    $from = (int) ($tier['from'] ?? 0);
                    $to = (int) ($tier['to'] ?? PHP_INT_MAX);
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $preorderDiscount = (float) ($tier['discount'] ?? 0);
                        break;
                    }
                }
                
                if ($preorderDiscount == 0.0 && !empty($tiers)) {
                    $preorderDiscount = (float) ($tiers[0]['discount'] ?? 0);
                }
                
                if ($preorderDiscount > $discountPercent) {
                    $discountPercent = $preorderDiscount;
                }
            }
        }

        if ($discountPercent > 0) {
            $salePrice = $originalPrice * (1 - $discountPercent / 100);
            $salePrice = round($salePrice);
        }

        return [
            'original_price' => $originalPrice,
            'sale_price'      => $salePrice,
            'discount_percent'=> $discountPercent,
            'is_on_sale'      => $discountPercent > 0,
        ];
    }

    /**
     * Tính phí in logo
     */
    private function calculateLogoPrice($basePrice, $position, $size)
    {
        $positionFactor = [
            'front' => 0.10,
            'back'  => 0.12,
            'side'  => 0.08,
        ];
        $sizeFactor = [
            'small'  => 0.08,
            'medium' => 0.12,
            'large'  => 0.18,
        ];
        $factor = ($positionFactor[$position] ?? 0.10) + ($sizeFactor[$size] ?? 0.10);
        return round($basePrice * $factor);
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function index(Request $request)
    {
        $voucherCode = $request->session()->get('voucher_code', null);
        $voucherDiscount = $request->session()->get('voucher_discount', 0);

        $cartItems = [];
        if ($request->has('cart')) {
            $cartJson = $request->query('cart', '{}');
            $cartItems = json_decode($cartJson, true) ?: [];
        }

        if (empty($cartItems)) {
            $cartItems = Session::get('cart', []);
        }

        Log::info('Checkout - Cart from request:', ['cart' => $cartItems]);
        Log::info('Checkout - Voucher from session:', [
            'code' => $voucherCode,
            'discount' => $voucherDiscount
        ]);

        $products = [];
        $subtotal = 0;
        $orderType = $request->query('order_type', 'retail'); // Lấy từ query, mặc định retail
        $isPreOrder = false;

        // Xử lý sản phẩm thường (retail)
        if (!empty($cartItems)) {
            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if (!$variant) {
                    Log::warning("Variant not found: {$variantId}");
                    continue;
                }

                if ($variant->product && ($variant->product->is_preorder ?? false)) {
                    continue;
                }

                // --- Lấy giá sau giảm giá (ưu tiên sale_price có sẵn) ---
                $saleInfo = $this->calculateSalePrice($variant);
                $basePrice = $saleInfo['sale_price']; // Đây là giá đã giảm (hoặc giá gốc nếu không giảm)

                // Nếu không có giảm giá, basePrice sẽ bằng original_price
                if (!$saleInfo['is_on_sale']) {
                    $basePrice = $saleInfo['original_price'];
                }

                // --- Tính phí in logo nếu có ---
                $meta = $item['meta'] ?? null;
                $additionalPrice = 0;
                if (!empty($meta['logo'])) {
                    $logo = $meta['logo'];
                    $additionalPrice = $this->calculateLogoPrice($basePrice, $logo['position'], $logo['size']);
                }

                $finalPrice = $basePrice + $additionalPrice;
                $quantity = (int) ($item['quantity'] ?? 1);
                $total = $finalPrice * $quantity;
                $subtotal += $total;

                // ---- LOG CHI TIẾT ĐỂ GỠ RỐI ----
                Log::info('Product price detail', [
                    'variant_id'        => $variant->id,
                    'variant_price'     => (float) $variant->price,
                    'variant_sale_price'=> $variant->sale_price ?? null,
                    'sale_info'         => $saleInfo,
                    'basePrice'         => $basePrice,
                    'additionalPrice'   => $additionalPrice,
                    'finalPrice'        => $finalPrice,
                    'quantity'          => $quantity,
                    'total'             => $total,
                    'subtotal'          => $subtotal,
                    'meta'              => $meta,
                ]);

                // Cảnh báo nếu giá quá cao (trên 100 triệu)
                if ($basePrice > 100000000) {
                    Log::warning('⚠️ Product price is unusually high!', [
                        'variant_id'   => $variant->id,
                        'basePrice'    => $basePrice,
                        'product_name' => $variant->product->name ?? 'Unknown',
                    ]);
                }

                if ($additionalPrice > 20000000) {
                    Log::warning('⚠️ Logo print fee is unusually high!', [
                        'variant_id'      => $variant->id,
                        'basePrice'       => $basePrice,
                        'additionalPrice' => $additionalPrice,
                    ]);
                }

                // --- Lấy ảnh sản phẩm ---
                $images = $variant->product->image_url ?? [];
                if (!is_array($images)) {
                    $images = [];
                }
                if (empty($images) && $variant->product->thumbnail) {
                    $images = [$variant->product->thumbnail];
                }

                // --- Tạo dữ liệu sản phẩm cho frontend ---
                $productData = [
                    'id'              => $variant->id,
                    'name'            => $variant->product->name,
                    'variant_name'    => $variant->name ?? '',
                    'price'           => $finalPrice,       // Đơn giá sau cùng
                    'quantity'        => $quantity,
                    'total'           => $total,            // Thành tiền cho sản phẩm này
                    'image'           => $images[0] ?? '/images/default-product.jpg',
                    'color'           => $variant->color->name ?? 'Đen',
                    'size'            => $variant->size_name ?? 'M',
                    'is_pre_order'    => false,
                    'is_on_sale'      => $saleInfo['is_on_sale'],
                    'original_price'  => $saleInfo['original_price'],
                    'discount_percent'=> $saleInfo['discount_percent'],
                ];

                if ($meta !== null) {
                    $productData['meta'] = $meta;
                }

                $products[] = $productData;
            }
        }

        // Xử lý pre-order (nếu giỏ hàng rỗng và có session pre-order)
        if (empty($products)) {
            $preOrderVariantId = Session::get('pre_order_variant_id');
            if ($preOrderVariantId) {
                $variant = ProductVariant::with('product', 'color')->find($preOrderVariantId);
                if ($variant && ($variant->product->is_preorder ?? false)) {
                    $quantity = (int) (Session::get('pre_order_quantity', 1));
                    
                    $saleInfo = $this->calculateSalePrice($variant);
                    $price = $saleInfo['sale_price'];
                    if (!$saleInfo['is_on_sale']) {
                        $price = $saleInfo['original_price'];
                    }
                    
                    $total = $price * $quantity;
                    $subtotal = $total;

                    $images = $variant->product->image_url ?? [];
                    if (!is_array($images)) {
                        $images = [];
                    }
                    if (empty($images) && $variant->product->thumbnail) {
                        $images = [$variant->product->thumbnail];
                    }

                    $products[] = [
                        'id'              => $variant->id,
                        'name'            => $variant->product->name,
                        'variant_name'    => $variant->name ?? '',
                        'price'           => $price,
                        'quantity'        => $quantity,
                        'total'           => $total,
                        'image'           => $images[0] ?? '/images/default-product.jpg',
                        'color'           => $variant->color->name ?? 'Đen',
                        'size'            => $variant->size_name ?? 'M',
                        'is_pre_order'    => true,
                        'is_on_sale'      => $saleInfo['is_on_sale'],
                        'original_price'  => $saleInfo['original_price'],
                        'discount_percent'=> $saleInfo['discount_percent'],
                    ];

                    $orderType = 'preorder';
                    $isPreOrder = true;
                    Log::info('Checkout - Pre-order mode:', [
                        'original_price'   => $saleInfo['original_price'],
                        'sale_price'       => $price,
                        'discount_percent' => $saleInfo['discount_percent'],
                    ]);
                }
            }
        }

        if (empty($products)) {
            Log::warning('Checkout - No products found');
            return redirect()->route('cart')->with('error', 'Giỏ hàng trống');
        }

        // Nếu có voucher, tính lại discount
        if ($voucherCode && $voucherDiscount > 0) {
            $voucher = Campaign::where('code', $voucherCode)
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();
            
            if ($voucher) {
                $discountValue = (float) $voucher->discount_value;
                $discountType = $voucher->discount_type;
                
                if ($discountType === 'percent') {
                    $voucherDiscount = ($subtotal * $discountValue) / 100;
                } elseif ($discountType === 'fixed') {
                    $voucherDiscount = min($discountValue, $subtotal);
                }
                $voucherDiscount = round($voucherDiscount);
                
                session(['voucher_discount' => $voucherDiscount]);
                session()->save();
            } else {
                session()->forget(['voucher_code', 'voucher_discount']);
                session()->save();
                $voucherCode = null;
                $voucherDiscount = 0;
            }
        }

        $discount = $voucherDiscount ?? 0;
        $shippingFee = 0;
        $finalTotal = max(0, $subtotal + $shippingFee - $discount);

        Log::info('Checkout - Final calculation', [
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'final_total' => $finalTotal,
        ]);

        $user = Auth::user();
        $userData = $user ? [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ] : null;

        return Inertia::render('Web/Checkout', [
            'user'            => $userData,
            'products'        => $products,
            'subtotal'        => $subtotal,
            'shipping_fee'    => $shippingFee,
            'discount'        => $discount,
            'final_total'     => $finalTotal,
            'order_type'      => $orderType,
            'is_customize'    => $orderType === 'customize', // Thêm flag customize
            'is_pre_order'    => $isPreOrder,
            'voucher_code'    => $voucherCode,
            'voucher_discount'=> $voucherDiscount,
        ]);
    }

    /**
     * Lưu đơn hàng - Gọi Admin Order Controller
     */
    public function store(Request $request)
    {
        Log::info('PaymentController@store called', $request->all());

        $validated = $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'required|string|max:20',
            'customer_email'  => 'required|email|max:255',
            'receiver_name'   => 'required|string|max:255',
            'receiver_phone'  => 'required|string|max:20',
            'shipping_address'=> 'required|string|max:500',
            'note'            => 'nullable|string|max:500',
            'payment_method'  => 'required|in:cod,ewallet,bank_transfer,vnpay,momo,payos',
            'items'           => 'required|array|min:1',
            'items.*.id'      => 'required|exists:product_variants,id',
            'items.*.quantity'=> 'required|integer|min:1',
            'items.*.price'   => 'required|numeric|min:0',
            'items.*.meta'    => 'nullable|array',
            'total_amount'    => 'required|numeric|min:0',
            'order_type'      => 'required|in:retail,preorder,wholesale,customize', // Thêm customize
            'promo_code'      => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        Log::info('Email khách hàng từ form: ' . $validated['customer_email']);

        $orderType = $validated['order_type'];

        Log::info('Order data:', [
            'promo_code' => $validated['promo_code'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'total_amount' => $validated['total_amount'],
            'order_type' => $orderType,
            'customer_email' => $validated['customer_email'],
        ]);

        // ===== TÍNH TOÁN TIỀN CỌC CHO ĐƠN SỈ =====
        $depositAmount = 0;
        $remainingAmount = 0;
        $paymentStatus = 'pending';

        if ($orderType === 'wholesale') {
            $depositAmount = round($validated['total_amount'] * 0.5);
            $remainingAmount = $validated['total_amount'] - $depositAmount;
            $paymentStatus = 'pending';
        } else {
            $depositAmount = $validated['total_amount'];
            $remainingAmount = 0;
            $paymentStatus = 'pending';
        }

        // Tạo request mới cho OrderController
        $orderRequest = new \Illuminate\Http\Request([
            'customer_name'    => $validated['customer_name'],
            'customer_phone'   => $validated['customer_phone'],
            'customer_email'   => $validated['customer_email'],
            'receiver_name'    => $validated['receiver_name'],
            'receiver_phone'   => $validated['receiver_phone'],
            'shipping_address' => $validated['shipping_address'],
            'note'             => $validated['note'] ?? null,
            'payment_method'   => $validated['payment_method'],
            'items'            => $validated['items'],
            'total_amount'     => $validated['total_amount'],
            'order_type'       => $orderType,
            'promo_code'       => $validated['promo_code'] ?? null,
            'discount_amount'  => $validated['discount_amount'] ?? 0,
            'deposit_amount'   => $depositAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status'   => $paymentStatus,
        ]);

        $orderRequest->setLaravelSession($request->session());

        try {
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            Log::info('OrderController response:', (array) $responseData);

            if ($responseData->success) {
                // Xóa session giỏ hàng / pre-order / voucher
                if ($orderType === 'retail' || $orderType === 'customize') {
                    $request->session()->forget('cart');
                } else {
                    $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                }
                $request->session()->forget(['voucher_code', 'voucher_discount']);

                // Lưu order ID vào session để trang success
                session(['last_order_id' => $responseData->order->id]);
                if (isset($responseData->order_display_code) && !empty($responseData->order_display_code)) {
                    session(['last_order_display_code' => $responseData->order_display_code]);
                } else {
                    $displayCode = $this->generateOrderDisplayCode($responseData->order);
                    session(['last_order_display_code' => $displayCode]);
                }

                // ===== XỬ LÝ LƯU THÔNG TIN IN LOGO =====
                $orderData = $responseData->order;
                $logoRequests = collect();
                if ($orderData && isset($orderData->details)) {
                    foreach ($orderData->details as $detail) {
                        $matchedItem = collect($validated['items'])->firstWhere('id', $detail->product_variant_id);
                        if ($matchedItem && isset($matchedItem['meta']['logo'])) {
                            $logoMeta = $matchedItem['meta']['logo'];
                            $logoRequest = LogoPrintRequest::create([
                                'order_detail_id' => $detail->id,
                                'logo_image' => $logoMeta['file'] ?? null,
                                'print_position' => $logoMeta['position'] ?? '',
                                'print_size' => $logoMeta['size'] ?? '',
                                'note' => ($logoMeta['note'] ?? '') . "\n\n---\n" .
                                           "Khách hàng: " . ($logoMeta['fullName'] ?? '') . "\n" .
                                           "Email: " . ($logoMeta['email'] ?? '') . "\n" .
                                           "SĐT: " . ($logoMeta['phone'] ?? ''),
                                'status' => 'pending',
                            ]);
                            $logoRequests->push($logoRequest);
                            Log::info('LogoPrintRequest created for order detail: ' . $detail->id);
                        }
                    }
                }

                // ===== GỬI EMAIL XÁC NHẬN ĐƠN HÀNG =====
                Log::info('=== BẮT ĐẦU GỬI EMAIL SAU KHI TẠO ĐƠN HÀNG ===');
                Log::info('Email sẽ gửi đến: ' . $validated['customer_email']);
                
                // Nếu là customize, chỉ gửi email tùy chỉnh (không gửi OrderConfirmationMail thông thường)
                if ($orderType === 'customize') {
                    // Lấy order với relationships đầy đủ
                    $orderWithRelations = Order::with([
                        'details.productVariant.product',
                        'details.productVariant.color'
                    ])->find($orderData->id);
                    
                    if ($orderWithRelations && $logoRequests->isNotEmpty()) {
                        // Gửi email cho khách hàng (CustomOrderConfirmation)
                        Mail::to($validated['customer_email'])->send(new CustomOrderConfirmation($orderWithRelations, $logoRequests));
                        
                        // Gửi email cho admin
                        $adminEmail = config('mail.admin_email', 'thanhphuongkhuu@gmail.com');
                        Mail::to($adminEmail)->send(new NewCustomOrderAdmin($orderWithRelations, $logoRequests));
                        
                        Log::info('Custom order emails sent for order ID: ' . $orderData->id);
                    } else {
                        Log::warning('Custom order: no logo requests found, but sending confirmation anyway.');
                        // Vẫn gửi email cơ bản nếu không có logo
                        $this->sendOrderConfirmationEmail($responseData->order, $validated['customer_email'], $logoRequests);
                    }
                } else {
                    // Đơn hàng thông thường: gửi email xác nhận
                    $this->sendOrderConfirmationEmail($responseData->order, $validated['customer_email'], $logoRequests);
                    
                    // Nếu có yêu cầu in logo, gửi thêm email tùy chỉnh
                    if ($logoRequests->isNotEmpty()) {
                        $orderWithRelations = Order::with([
                            'details.productVariant.product',
                            'details.productVariant.color'
                        ])->find($orderData->id);
                        
                        if ($orderWithRelations) {
                            Mail::to($validated['customer_email'])->send(new CustomOrderConfirmation($orderWithRelations, $logoRequests));
                            $adminEmail = config('mail.admin_email', 'thanhphuongkhuu@gmail.com');
                            Mail::to($adminEmail)->send(new NewCustomOrderAdmin($orderWithRelations, $logoRequests));
                            Log::info('Custom order emails sent for order ID: ' . $orderData->id);
                        }
                    }
                }
                
                Log::info('=== HOÀN TẤT QUÁ TRÌNH GỬI EMAIL ===');

                // Xác định redirect URL
                $orderId = $responseData->order->id;
                
                // Nếu là customize, luôn redirect thẳng đến success (không qua PayOS)
                if ($orderType === 'customize') {
                    $redirectUrl = route('checkout.success');
                } else {
                    $redirectUrl = $validated['payment_method'] === 'payos'
                        ? route('payment.create', ['order_id' => $orderId])
                        : route('checkout.success');
                }

                $isApiRequest = $request->expectsJson() && !$request->header('X-Inertia');

                if ($isApiRequest) {
                    return response()->json([
                        'success'      => true,
                        'order_id'     => $orderId,
                        'redirect_url' => $redirectUrl,
                    ]);
                }

                return redirect()->to($redirectUrl);
            }

            // Lỗi từ OrderController
            $errorMessage = $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.';
            $isApiRequest = $request->expectsJson() && !$request->header('X-Inertia');

            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 400);
            }

            return back()->withErrors(['error' => $errorMessage]);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
            $isApiRequest = $request->expectsJson() && !$request->header('X-Inertia');

            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }

            return back()->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Gửi email xác nhận đơn hàng
     */
    private function sendOrderConfirmationEmail($orderData, $customerEmail, $logoRequests = [])
    {
        try {
            Log::info('=== BẮT ĐẦU GỬI EMAIL XÁC NHẬN ĐƠN HÀNG ===');
            Log::info('Email khách hàng: ' . $customerEmail);
            
            // Kiểm tra email có hợp lệ không
            if (empty($customerEmail) || $customerEmail === 'N/A') {
                Log::warning('Email khách hàng không hợp lệ: ' . $customerEmail);
                return;
            }

            // Lấy order từ database với relationships
            $order = Order::with([
                'details.productVariant.product',
                'details.productVariant.color',
                'payment',
                'user'
            ])->find($orderData->id);

            if (!$order) {
                Log::error('Không tìm thấy order với ID: ' . $orderData->id);
                return;
            }

            Log::info('Order ID: ' . $order->id);
            Log::info('Số lượng order details: ' . $order->details->count());

            // Lấy chi tiết đơn hàng (bao gồm id để match với logo)
            $orderDetails = [];
            foreach ($order->details as $detail) {
                $variant = $detail->productVariant;
                $product = $variant ? $variant->product : null;
                
                $orderDetails[] = [
                    'id'          => $detail->id,
                    'name'        => $product ? $product->name : 'Sản phẩm không xác định',
                    'quantity'    => (int) $detail->quantity,
                    'unit_price'  => (int) $detail->unit_price,
                    'subtotal'    => (int) $detail->subtotal,
                    'color'       => $variant && $variant->color ? $variant->color->name : '',
                    'size'        => $variant ? $variant->size_name : '',
                ];
            }

            Log::info('Số lượng sản phẩm đã xử lý: ' . count($orderDetails));

            // Nếu chưa có logoRequests, thử lấy từ database
            if (empty($logoRequests) && $order->details->isNotEmpty()) {
                $logoRequests = LogoPrintRequest::whereIn('order_detail_id', $order->details->pluck('id'))->get();
                Log::info('Logo requests loaded from DB: ' . $logoRequests->count());
            }

            $displayCode = $this->generateOrderDisplayCode($order);
            Log::info('Mã đơn hàng: ' . $displayCode);

            $emailToSend = $order->customer_email ?? $order->user?->email ?? $customerEmail;
            Log::info('Email sẽ gửi đến: ' . $emailToSend);

            // Gửi email với logo requests
            Mail::to($emailToSend)->send(new OrderConfirmationMail($order, $orderDetails, $displayCode, $logoRequests));
            
            Log::info('✅ Email xác nhận đã được gửi thành công đến: ' . $emailToSend);
            
        } catch (\Exception $e) {
            Log::error('❌ LỖI GỬI EMAIL: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            // Không throw exception để không làm gián đoạn quá trình đặt hàng
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị
     */
    private function generateOrderDisplayCode($order)
    {
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail'    => 'L',
            'wholesale' => 'S',
            'preorder'  => 'P',
            'customize' => 'C', // Thêm prefix cho đơn customize
            default     => 'DH'
        };

        $date = now()->format('dmY');
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Áp dụng voucher từ checkout
     */
    public function applyVoucher(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0'
            ]);

            $code = strtoupper($request->code);
            $subtotal = $request->subtotal;

            $voucher = Campaign::where('code', $code)
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ'
                ]);
            }

            if ($voucher->expiry && $voucher->expiry < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết hạn'
                ]);
            }

            if ($voucher->limit && $voucher->used >= $voucher->limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết'
                ]);
            }

            if ($voucher->min_order > 0 && $subtotal < $voucher->min_order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->min_order) . 'đ để áp dụng mã'
                ]);
            }

            $discountAmount = 0;
            $discountType = $voucher->discount_type;
            $discountValue = $voucher->discount_value;

            if ($discountType === 'percent') {
                $discountAmount = ($subtotal * $discountValue) / 100;
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($discountValue, $subtotal);
            }

            $discountAmount = round($discountAmount);

            session([
                'voucher_code' => $voucher->code,
                'voucher_discount' => $discountAmount,
            ]);
            session()->save();

            return response()->json([
                'success' => true,
                'code' => $voucher->code,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'message' => 'Áp dụng mã giảm giá thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Apply voucher error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa voucher khỏi session
     */
    public function removeVoucher(Request $request)
    {
        try {
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            $request->session()->save();

            Log::info('Voucher removed from session');

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mã giảm giá',
                'clear_local' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Remove voucher error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success()
    {
        $orderId = session('last_order_id');
        $displayCode = session('last_order_display_code');

        if (!$orderId) {
            return Inertia::render('Web/CheckoutSuccess', [
                'error' => 'Không tìm thấy thông tin đơn hàng',
                'order' => null,
                'order_display_code' => null,
            ]);
        }

        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment',
            'user'
        ])->find($orderId);

        if (!$order) {
            return Inertia::render('Web/CheckoutSuccess', [
                'error' => 'Không tìm thấy đơn hàng',
                'order' => null,
                'order_display_code' => null,
            ]);
        }

        if (empty($displayCode)) {
            $displayCode = $this->generateOrderDisplayCode($order);
        }

        $customerEmail = $order->customer_email;
        if (empty($customerEmail) || $customerEmail === 'N/A') {
            $customerEmail = $order->user?->email ?? 'N/A';
        }

        $orderDetails = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            
            $image = '/images/default-product.jpg';
            if ($product) {
                $imageUrls = $product->image_url;
                if (is_array($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls[0];
                } elseif (is_string($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls;
                } elseif ($product->thumbnail) {
                    $image = $product->thumbnail;
                }
            }
            
            return [
                'id'          => $detail->id,
                'name'        => $product ? $product->name : 'Sản phẩm không xác định',
                'image'       => $image,
                'quantity'    => (int) $detail->quantity,
                'unit_price'  => (int) $detail->unit_price,
                'subtotal'    => (int) $detail->subtotal,
                'color'       => $variant && $variant->color ? $variant->color->name : '',
                'size'        => $variant ? $variant->size_name : '',
            ];
        });

        $payment = $order->payment;
        $paymentMethod = $payment ? $payment->payment_method : 'cod';

        $paymentStatus = $order->payment_status ?? 'pending';

        if ($order->order_code === 'wholesale') {
            if ($payment && ($payment->status === 'success' || $payment->status === 'paid')) {
                $paymentStatus = 'deposit_paid';
                $order->payment_status = 'deposit_paid';
                $order->save();
            } else {
                $paymentStatus = 'pending';
            }
        } else {
            if ($payment && ($payment->status === 'success' || $payment->status === 'paid')) {
                $paymentStatus = 'paid';
                $order->payment_status = 'paid';
                $order->save();
            } else {
                $paymentStatus = 'pending';
            }
        }

        if ($payment && in_array($payment->status, ['pending', null])) {
            if ($paymentStatus === 'paid' || $paymentStatus === 'deposit_paid') {
                $payment->status = 'success';
                $payment->save();
            }
        }

        $orderData = [
            'id'                => $order->id,
            'customer_name'     => $order->customer_name,
            'customer_phone'    => $order->customer_phone,
            'customer_email'    => $customerEmail,
            'receiver_name'     => $order->receiver_name,
            'receiver_phone'    => $order->receiver_phone,
            'shipping_address'  => $order->shipping_address,
            'note'              => $order->note,
            'total_amount'      => (int) $order->total_amount,
            'shipping_fee'      => (int) $order->shipping_fee,
            'discount_amount'   => (int) $order->discount_amount,
            'final_amount'      => (int) $order->final_amount,
            'deposit_amount'    => (int) ($order->deposit_amount ?? 0),
            'remaining_amount'  => (int) ($order->remaining_amount ?? 0),
            'status'            => $order->getStatusText(),
            'order_code'        => $order->order_code ?? 'retail',
            'payment_method'    => $paymentMethod,
            'payment_status'    => $paymentStatus,
            'transaction_code'  => $payment ? $payment->transaction_code : null,
            'details'           => $orderDetails,
            'created_at'        => $order->created_at,
            'display_code'      => $displayCode,
            'order_display_code'=> $displayCode,
        ];

        session()->forget(['last_order_id', 'last_order_display_code']);

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode,
        ]);
    }
}
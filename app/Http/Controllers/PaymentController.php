<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Campaign;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Tính giá sale cho variant
     */
    private function calculateSalePrice($variant)
    {
        $originalPrice = $variant->price;
        $salePrice = $originalPrice;
        $discountPercent = 0;
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
            $currentDiscount = $config ? (float) $config->discount_percent : 0;
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
                $currentBuyers = $preorder->current_buyers ?? 0;
                $tiers = $preorder->tiers ?? [];
                
                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });
                
                $preorderDiscount = 0;
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $preorderDiscount = $tier['discount'] ?? 0;
                        break;
                    }
                }
                
                if ($preorderDiscount == 0 && !empty($tiers)) {
                    $preorderDiscount = $tiers[0]['discount'] ?? 0;
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
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $discountPercent > 0,
        ];
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
        $orderType = 'retail';
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

                $saleInfo = $this->calculateSalePrice($variant);
                $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;
                $quantity = $item['quantity'] ?? 1;
                $total = $price * $quantity;
                $subtotal += $total;

                $images = $variant->product->image_url ?? [];
                if (!is_array($images)) {
                    $images = [];
                }
                if (empty($images) && $variant->product->thumbnail) {
                    $images = [$variant->product->thumbnail];
                }

                $products[] = [
                    'id' => $variant->id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->name ?? '',
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $total,
                    'image' => $images[0] ?? '/images/default-product.jpg',
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'original_price' => $variant->price,
                    'discount_percent' => $saleInfo['discount_percent'],
                ];
            }
            $orderType = 'retail';
            $isPreOrder = false;
        }

        // Xử lý pre-order
        if (empty($products)) {
            $preOrderVariantId = Session::get('pre_order_variant_id');
            if ($preOrderVariantId) {
                $variant = ProductVariant::with('product', 'color')->find($preOrderVariantId);
                if ($variant && ($variant->product->is_preorder ?? false)) {
                    $quantity = Session::get('pre_order_quantity', 1);
                    
                    $saleInfo = $this->calculateSalePrice($variant);
                    $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;
                    
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
                        'id' => $variant->id,
                        'name' => $variant->product->name,
                        'variant_name' => $variant->name ?? '',
                        'price' => $price,
                        'quantity' => $quantity,
                        'total' => $total,
                        'image' => $images[0] ?? '/images/default-product.jpg',
                        'color' => $variant->color->name ?? 'Đen',
                        'size' => $variant->size_name ?? 'M',
                        'is_pre_order' => true,
                        'is_on_sale' => $saleInfo['is_on_sale'],
                        'original_price' => $variant->price,
                        'discount_percent' => $saleInfo['discount_percent'],
                    ];

                    $orderType = 'preorder';
                    $isPreOrder = true;
                    Log::info('Checkout - Pre-order mode with sale price:', [
                        'original_price' => $variant->price,
                        'sale_price' => $price,
                        'discount_percent' => $saleInfo['discount_percent'],
                        'is_on_sale' => $saleInfo['is_on_sale'],
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
                $discountValue = $voucher->discount_value;
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

        $user = Auth::user();
        $userData = $user ? [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ] : null;

        return Inertia::render('Web/Checkout', [
            'user' => $userData,
            'products' => $products,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'order_type' => $orderType,
            'is_pre_order' => $isPreOrder,
            'voucher_code' => $voucherCode,
            'voucher_discount' => $voucherDiscount,
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
            'total_amount'    => 'required|numeric|min:0',
            'order_type'      => 'required|in:retail,preorder,wholesale',
            'promo_code'      => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $orderType = $validated['order_type'];

        Log::info('Order data:', [
            'promo_code' => $validated['promo_code'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'total_amount' => $validated['total_amount'],
            'order_type' => $orderType,
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

        // CHỈ GÁN SESSION - KHÔNG CẦN setUserResolver
        $orderRequest->setLaravelSession($request->session());

        try {
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                // Xóa session giỏ hàng / pre-order / voucher
                if ($orderType === 'retail') {
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

                // Xác định redirect URL
                $orderId = $responseData->order->id;
                $redirectUrl = null;

                if ($validated['payment_method'] === 'payos') {
                    $redirectUrl = route('payment.create', ['order_id' => $orderId]);
                } else {
                    $redirectUrl = route('checkout.success');
                }

                // Nếu request là AJAX / JSON, trả về JSON
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success'      => true,
                        'order_id'     => $orderId,
                        'redirect_url' => $redirectUrl,
                    ]);
                }

                // Ngược lại redirect thông thường
                return redirect()->to($redirectUrl);
            }

            // Nếu không thành công
            $errorMessage = $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 400);
            }
            return back()->withErrors(['error' => $errorMessage]);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage());
            $errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }
            return back()->withErrors(['error' => $errorMessage]);
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
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
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
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
        }

        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment',
            'user'
        ])->find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
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
                'id' => $detail->id,
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'image' => $image,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'color' => $variant && $variant->color ? $variant->color->name : '',
                'size' => $variant ? $variant->size_name : '',
            ];
        });

        $payment = $order->payment;
        $paymentMethod = $payment ? $payment->payment_method : 'cod';

        // ===== LẤY TRẠNG THÁI THANH TOÁN TỪ BẢNG ORDERS =====
        $paymentStatus = $order->payment_status ?? 'pending';

        // ===== XỬ LÝ ĐƠN SỈ =====
        if ($order->order_code === 'wholesale') {
            // Nếu đã có payment và status = success => cọc đã được thanh toán
            if ($payment && ($payment->status === 'success' || $payment->status === 'paid')) {
                $paymentStatus = 'deposit_paid';
                $order->payment_status = 'deposit_paid';
                $order->save();
            } else {
                // Chưa thanh toán cọc, giữ pending
                $paymentStatus = 'pending';
            }
        } else {
            // Đơn thường / pre-order
            if ($payment && ($payment->status === 'success' || $payment->status === 'paid')) {
                $paymentStatus = 'paid';
                $order->payment_status = 'paid';
                $order->save();
            } else {
                $paymentStatus = 'pending';
            }
        }

        // ===== CẬP NHẬT PAYMENT STATUS (nếu chưa được cập nhật) =====
        if ($payment && in_array($payment->status, ['pending', null])) {
            if ($paymentStatus === 'paid' || $paymentStatus === 'deposit_paid') {
                $payment->status = 'success';
                $payment->save();
            }
        }

        $orderData = [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $customerEmail,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'note' => $order->note,
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) $order->discount_amount,
            'final_amount' => (int) $order->final_amount,
            'deposit_amount' => (int) ($order->deposit_amount ?? 0),
            'remaining_amount' => (int) ($order->remaining_amount ?? 0),
            'status' => $order->getStatusText(),
            'order_code' => $order->order_code ?? 'retail',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'transaction_code' => $payment ? $payment->transaction_code : null,
            'details' => $orderDetails,
            'created_at' => $order->created_at,
            'display_code' => $displayCode,
            'order_display_code' => $displayCode,
        ];

        session()->forget(['last_order_id', 'last_order_display_code']);

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode,
        ]);
    }
}
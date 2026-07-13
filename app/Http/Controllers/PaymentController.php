<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function index(Request $request)
    {
        $isPreOrder = $request->session()->get('pre_order_checkout', false);
        $preOrderVariantId = $request->session()->get('pre_order_variant_id', null);
        
        $products = [];
        $subtotal = 0;
        $orderType = 'retail';

        if ($isPreOrder && $preOrderVariantId) {
            Log::info('Processing pre-order checkout for variant: ' . $preOrderVariantId);
            
            $variant = ProductVariant::with('product', 'color')->find($preOrderVariantId);
            if ($variant && ($variant->product->is_preorder ?? false)) {
                $quantity = $request->session()->get('pre_order_quantity', 1);
                $price = $variant->price;
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
                    'id'          => $variant->id,
                    'name'        => $variant->product->name,
                    'variant_name'=> $variant->name ?? '',
                    'price'       => $price,
                    'quantity'    => $quantity,
                    'total'       => $total,
                    'image'       => $images[0] ?? '/images/default-product.jpg',
                    'color'       => $variant->color->name ?? 'Đen',
                    'size'        => $variant->size_name ?? 'M',
                    'is_pre_order' => true,
                ];
                
                $orderType = 'preorder';
            } else {
                Log::warning('Pre-order variant not found or invalid: ' . $preOrderVariantId);
                $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                return redirect()->route('cart')->with('error', 'Sản phẩm Pre-order không hợp lệ');
            }
        } else {
            Log::info('Processing retail checkout from cart');
            $cartItems = Session::get('cart', []);
            
            $filteredCart = [];
            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::with('product')->find($variantId);
                if ($variant && !($variant->product->is_preorder ?? false)) {
                    $filteredCart[$variantId] = $item;
                }
            }
            
            foreach ($filteredCart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if ($variant) {
                    $price = $item['price'] ?? $variant->price;
                    $quantity = $item['quantity'];
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
                        'id'          => $variant->id,
                        'name'        => $variant->product->name,
                        'variant_name'=> $variant->name ?? '',
                        'price'       => $price,
                        'quantity'    => $quantity,
                        'total'       => $total,
                        'image'       => $images[0] ?? '/images/default-product.jpg',
                        'color'       => $variant->color->name ?? 'Đen',
                        'size'        => $variant->size_name ?? 'M',
                        'is_pre_order' => false,
                    ];
                }
            }
            
            if (empty($products)) {
                return redirect()->route('cart')->with('error', 'Giỏ hàng trống');
            }
            
            $orderType = 'retail';
        }

        $shippingFee = 0;
        $discount = 0;
        $finalTotal = $subtotal + $shippingFee - $discount;

        $user = Auth::user();
        $userData = $user ? [
            'name'  => $user->name,
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
        ]);
    }

    /**
     * Xử lý tạo đơn hàng
     */
    public function store(Request $request)
    {
        Log::info('PaymentController@store called', $request->all());
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'required|in:retail,preorder',
        ]);

        $orderType = $validated['order_type'];
        Log::info('Creating order with type: ' . $orderType);

        $orderRequest = new Request([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'shipping_address' => $validated['shipping_address'],
            'note' => $validated['note'] ?? null,
            'payment_method' => $validated['payment_method'],
            'items' => $validated['items'],
            'total_amount' => $validated['total_amount'],
            'order_type' => $orderType,
        ]);

        try {
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                if ($orderType === 'retail') {
                    $request->session()->forget('cart');
                    Log::info('Cart cleared after retail order');
                } else {
                    $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                    Log::info('Pre-order session cleared');
                }
                
                // Lưu order_id vào session
                session(['last_order_id' => $responseData->order->id]);
                
                // Lấy order_display_code từ response, nếu không có thì tự tạo
                if (isset($responseData->order_display_code) && !empty($responseData->order_display_code)) {
                    session(['last_order_display_code' => $responseData->order_display_code]);
                    Log::info('Display code from response: ' . $responseData->order_display_code);
                } else {
                    // Tạo display code từ order
                    $displayCode = $this->generateOrderDisplayCode($responseData->order);
                    session(['last_order_display_code' => $displayCode]);
                    Log::info('Generated display code: ' . $displayCode);
                }

                return redirect()->route('checkout.success');
            }

            return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success()
    {
        $orderId = session('last_order_id');
        $displayCode = session('last_order_display_code');

        Log::info('Checkout success - order_id: ' . $orderId . ', display_code: ' . $displayCode);

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

        // Nếu không có displayCode trong session, tạo mới
        if (empty($displayCode)) {
            $displayCode = $this->generateOrderDisplayCode($order);
            Log::info('Generated new display code: ' . $displayCode);
        }

        // Lấy email từ order, nếu không có thì lấy từ user
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
        $paymentStatus = 'pending';
        
        if ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        } elseif (in_array($paymentMethod, ['bank_transfer', 'ewallet', 'vnpay', 'momo'])) {
            $paymentStatus = 'paid';
        }

        if ($payment && $payment->status !== $paymentStatus) {
            $payment->status = $paymentStatus;
            $payment->save();
        }

        // Tạo dữ liệu order để gửi lên view
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

        Log::info('Order data sent to view:', [
            'customer_email' => $orderData['customer_email'],
            'order_display_code' => $displayCode,
            'display_code' => $displayCode,
            'created_at' => $orderData['created_at'],
            'details_count' => count($orderDetails),
        ]);

        // Xóa session sau khi đã lấy dữ liệu
        session()->forget(['last_order_id', 'last_order_display_code']);

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode,
        ]);
    }

    /**
     * Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
     * Format: [Loại đơn hàng][Ngày tạo dmY][STT 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     */
    private function generateOrderDisplayCode($order)
    {
        // Xác định prefix dựa trên loại đơn hàng
        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số (VD: 00019)
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }
}
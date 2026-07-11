<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        // Inject Admin\OrderController
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
                
                $products[] = [
                    'id'          => $variant->id,
                    'name'        => $variant->product->name,
                    'variant_name'=> $variant->name ?? '',
                    'price'       => $price,
                    'quantity'    => $quantity,
                    'total'       => $total,
                    'image'       => $variant->product->image_url[0] ?? '/images/default-product.jpg',
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
                    $products[] = [
                        'id'          => $variant->id,
                        'name'        => $variant->product->name,
                        'variant_name'=> $variant->name ?? '',
                        'price'       => $price,
                        'quantity'    => $quantity,
                        'total'       => $total,
                        'image'       => $variant->product->image_url[0] ?? '/images/default-product.jpg',
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

        // Tạo request mới để gửi đến Admin\OrderController
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
            // Gọi Admin\OrderController để tạo đơn hàng
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
                
                session(['last_order' => $responseData->order]);
                session(['last_order_display_code' => $responseData->order_display_code ?? '']);

                return redirect()->route('checkout.success')->with('success', 'Đặt hàng thành công!');
            }

            return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function success()
    {
        $order = session('last_order');
        $displayCode = session('last_order_display_code');

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $order,
            'order_display_code' => $displayCode,
        ]);
    }
}
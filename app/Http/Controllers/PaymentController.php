<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct(OrderController $orderController)
    {
        $this->orderController = $orderController;
    }

    public function index()
    {
        // Lấy giỏ hàng từ session
        $cartItems = Session::get('cart', []);
        $products = [];
        $subtotal = 0;

        foreach ($cartItems as $variantId => $item) {
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
                    'image'       => $variant->product->image ?? '/images/default-product.jpg',
                    'color'       => $variant->color->name ?? 'Đen',
                    'size'        => $variant->size_name ?? 'M',
                ];
            }
        }

        // Nếu giỏ hàng trống, chuyển về trang giỏ hàng
        if (empty($products)) {
            return redirect()->route('cart')->with('error', 'Giỏ hàng trống');
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
        ]);
    }

    public function store(Request $request)
    {
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
        ]);

        $cartItems = Session::get('cart', []);

        if (empty($cartItems)) {
            return back()->withErrors(['error' => 'Giỏ hàng trống.']);
        }

        // Tạo request để gửi đến OrderController
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
            'order_type' => 'retail',
        ]);

        try {
            // Gọi OrderController để tạo đơn hàng
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                // Lưu thông tin đơn hàng vào session
                session(['last_order' => $responseData->order]);
                session(['last_order_display_code' => $responseData->order_display_code ?? '']);

                return redirect()->route('checkout.success')->with('success', 'Đặt hàng thành công!');
            }

            return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

        } catch (\Exception $e) {
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
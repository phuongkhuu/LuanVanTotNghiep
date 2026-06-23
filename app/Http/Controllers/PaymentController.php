<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {

        $cartItems = session('cart', []);

        $products = [];
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = ProductVariant::with('product')->find($item['product_variant_id']);
            if ($variant) {
                $price = $variant->price;
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
                    'image'       => $variant->product->image,
                    'color'       => $variant->color ?? 'Đen', // nếu có
                ];
            }
        }

        $shippingFee = 0; // miễn phí
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
            'payment_method' => 'required|in:cod,ewallet',
            'items' => 'required|array',
            'total_amount' => 'required|numeric',
        ]);

        $user = Auth::user();
        $cartItems = session('cart', []);

        if (empty($cartItems)) {
            return back()->withErrors(['error' => 'Giỏ hàng trống.']);
        }

        DB::beginTransaction();
        try {

            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'order_code' => 'retail',
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'],
                'shipping_fee' => 0,
                'total_amount' => $validated['total_amount'],
                'discount_amount' => 0,
                'final_amount' => $validated['total_amount'],
                'order_status' => 0, // pending
            ]);


            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if ($variant) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);
                }
            }


            session()->forget('cart');
            DB::commit();

            return redirect()->route('checkout.success')->with('success', 'Đặt hàng thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function success()
    {
        return Inertia::render('Web/CheckoutSuccess');
    }
}
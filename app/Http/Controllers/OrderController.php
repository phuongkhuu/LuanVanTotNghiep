<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    /**
     * Tạo đơn hàng mới từ giỏ hàng
     */
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
            'order_type' => 'nullable|in:retail,wholesale,preorder',
        ]);

        $user = Auth::user();
        $orderType = $validated['order_type'] ?? 'retail';
        $totalAmount = $validated['total_amount'];
        $shippingFee = 0;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        try {
            DB::beginTransaction();

            // 1. Kiểm tra tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity']) {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
            }

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderType,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0, // pending
            ]);

            // 3. Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Cập nhật tồn kho
                $variant->decrement('stock', $item['quantity']);
            }

            // 4. Tạo bản ghi thanh toán
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_code' => $this->generateTransactionCode(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            // 5. Xóa giỏ hàng
            Session::forget('cart');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $this->generateOrderDisplayCode($orderType),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị cho khách
     */
    public function generateOrderDisplayCode($orderType)
    {
        $prefix = match($orderType) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        $date = now()->format('Ymd');
        $count = Order::whereDate('created_at', now()->toDateString())->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }

    /**
     * Xem chi tiết đơn hàng (cho người dùng)
     */
    public function show($id)
    {
        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment'
        ]);

        if (Auth::check()) {
            $order->where('user_id', Auth::id());
        }

        $order = $order->findOrFail($id);

        return response()->json([
            'order' => $order,
            'order_display_code' => $this->generateOrderDisplayCode($order->order_code),
            'status_text' => $order->status_text,
            'status_label' => $order->status_label,
        ]);
    }

    /**
     * Lịch sử đơn hàng của người dùng
     */
    public function history()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['details', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $orders->each(function ($order) {
            $order->display_code = $this->generateOrderDisplayCode($order->order_code);
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
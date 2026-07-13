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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0,
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

            // Tạo mã đơn hàng hiển thị
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
                'created_at' => $order->created_at->format('dmY H:i:s'),
                'current_time' => now()->format('dmY H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $displayCode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị cho khách
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600016 (L + 13072026 + 00016)
     * 
     * @param Order $order
     * @return string
     */
    public function generateOrderDisplayCode($order)
    {
        // Lấy order object hoặc order_id
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

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('dmY');
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
            'order_display_code' => $this->generateOrderDisplayCode($order),
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
            $order->display_code = $this->generateOrderDisplayCode($order);
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\ProductVariant;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng theo loại
     */
    public function index($type = 'retail')
    {
        $validTypes = ['retail', 'wholesale', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'retail';

        $orders = Order::with(['details.productVariant.product', 'payment'])
            ->where('order_code', $type)
            ->latest()
            ->get()
            ->map(function ($order) {
                $products = $order->details->map(function ($detail) {
                    $variant = $detail->productVariant;
                    $product = $variant ? $variant->product : null;
                    return [
                        'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                        'quantity' => $detail->quantity,
                        'price'    => (int) $detail->unit_price,
                        'subtotal' => (int) $detail->subtotal,
                        'image'    => $product ? ($product->image_url[0] ?? '/images/default-product.jpg') : '/images/default-product.jpg',
                    ];
                });

                $subtotal = $products->sum('subtotal');
                $shipping = (int) ($order->shipping_fee ?? 0);
                $discount = (int) ($order->discount_amount ?? 0);
                $final = $subtotal + $shipping - $discount;

                $payment = 'COD';
                $paymentClass = 'bg-green-100 text-green-800';
                if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
                    $payment = 'Chuyển khoản';
                    $paymentClass = 'bg-blue-100 text-blue-800';
                } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
                    $payment = 'Ví điện tử';
                    $paymentClass = 'bg-purple-100 text-purple-800';
                }

                // Sử dụng mã hiển thị mới
                $displayCode = $this->generateOrderDisplayCode($order);

                return [
                    'id'              => $order->id,
                    'code'            => $displayCode, // Thay đổi từ #ORD-xxx thành mã mới
                    'display_code'    => $displayCode,
                    'customer'        => $order->customer_name ?? $order->receiver_name,
                    'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
                    'receiver'        => $order->receiver_name,
                    'receiver_phone'  => $order->receiver_phone,
                    'date'            => $order->created_at->format('d/m/Y'),
                    'subtotal'        => $subtotal,
                    'shipping_fee'    => $shipping,
                    'discount_amount' => $discount,
                    'final_amount'    => $final,
                    'amount'          => $final,
                    'payment'         => $payment,
                    'paymentClass'    => $paymentClass,
                    'status'          => $this->getStatusText($order),
                    'statusLabel'     => $this->getStatusLabel($order),
                    'type'            => $order->order_code ?? 'retail',
                    'address'         => $order->shipping_address,
                    'note'            => $order->note,
                    'products'        => $products,
                ];
            });

        return Inertia::render('Admin/Orders', [
            'type'          => $type,
            'initialOrders' => $orders->all(),
        ]);
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with(['details.productVariant.product', 'payment'])->findOrFail($id);

        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price'    => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'image'    => $product ? ($product->image_url[0] ?? '/images/default-product.jpg') : '/images/default-product.jpg',
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
            $payment = 'Ví điện tử';
        }

        // Sử dụng mã hiển thị mới
        $displayCode = $this->generateOrderDisplayCode($order);

        $orderData = [
            'id'              => $order->id,
            'code'            => $displayCode, // Thay đổi từ #ORD-xxx thành mã mới
            'display_code'    => $displayCode,
            'customer'        => $order->customer_name ?? $order->receiver_name,
            'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
            'receiver'        => $order->receiver_name,
            'receiver_phone'  => $order->receiver_phone,
            'date'            => $order->created_at->format('d/m/Y'),
            'subtotal'        => $subtotal,
            'shipping_fee'    => $shipping,
            'discount_amount' => $discount,
            'final_amount'    => $final,
            'amount'          => $final,
            'payment'         => $payment,
            'status'          => $this->getStatusText($order),
            'statusLabel'     => $this->getStatusLabel($order),
            'type'            => $order->order_code ?? 'retail',
            'address'         => $order->shipping_address,
            'note'            => $order->note,
            'products'        => $products,
        ];

        return Inertia::render('Admin/Orders/Show', ['order' => $orderData]);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $order = Order::findOrFail($id);
            $newStatus = $request->status;
            $statusMap = $this->getStatusMapForOrder($order);
            
            if (!isset($statusMap[$newStatus])) {
                return back()->with('error', 'Trạng thái không hợp lệ');
            }
            
            $statusInt = $statusMap[$newStatus];
            $order->order_status = $statusInt;
            $order->save();

            Log::info("Order #{$order->id} status updated to: {$newStatus}");

            return back()->with('success', 'Cập nhật trạng thái thành công');
        } catch (\Exception $e) {
            Log::error('Update order status error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    }

    /**
     * Tạo đơn hàng mới (từ PaymentController gọi)
     */
    public function store(Request $request)
    {
        Log::info('Admin\OrderController@store called', $request->all());
        
        try {
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
            $userId = Auth::id();
            $totalAmount = (int) $validated['total_amount'];
            $shippingFee = 0;
            $discountAmount = 0;

            Log::info('Creating order with type: ' . $orderType . ' for user: ' . $userId);

            // Bắt đầu transaction
            DB::beginTransaction();

            // Tạo đơn hàng với order_code đúng loại
            $order = Order::create([
                'user_id' => $userId,
                'order_code' => $orderType, // 'retail' hoặc 'preorder'
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'],
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $totalAmount + $shippingFee - $discountAmount,
                'order_status' => 0, // Pending
            ]);

            Log::info('Order created:', ['order_id' => $order->id, 'type' => $orderType]);

            // Tạo chi tiết đơn hàng và cập nhật stock
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (int) $item['price'];
                $subtotal = $price * $quantity;

                // Tạo order detail
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Cập nhật stock: CHỈ GIẢM STOCK CHO RETAIL
                // Pre-order KHÔNG giảm stock vì chưa có hàng
                if ($orderType === 'retail') {
                    if ($variant->stock < $quantity) {
                        throw new \Exception("Sản phẩm không đủ hàng. Còn {$variant->stock}, yêu cầu {$quantity}");
                    }
                    $variant->stock -= $quantity;
                    $variant->save();
                    Log::info("Stock updated for variant {$variant->id}: new stock {$variant->stock}");
                } else {
                    Log::info("Pre-order: Stock not reduced for variant {$variant->id}");
                }
            }

            // Tạo thanh toán
            Payment::create([
                'order_id' => $order->id,
                'transaction_code' => 'PAY-' . $order->id . '-' . time(),
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount + $shippingFee - $discountAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            DB::commit();

            // QUAN TRỌNG: Tạo mã đơn hàng hiển thị theo format mới
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('✅ Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order->load(['details', 'payment']),
                'order_display_code' => $displayCode, // QUAN TRỌNG: Trả về mã mới
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     * 
     * @param Order $order
     * @return string
     */
    public function generateOrderDisplayCode($order)
    {
        // Nếu truyền vào là ID
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        // Xác định prefix dựa trên loại đơn hàng
        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // Ví dụ: 13072026
        
        // Dùng ID của order làm sequence, format 5 số (VD: 00019)
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Xuất tất cả đơn hàng (không phân biệt loại)
     */
    public function export(Request $request)
    {
        try {
            $orders = Order::with(['details.productVariant.product', 'payment'])
                ->latest()
                ->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            $export = new OrdersExport('all', $formattedOrders);
            $date = now()->format('Ymd');
            $filename = "{$date}_tat_ca_don_hang.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Export all orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Xuất đơn hàng theo bộ lọc
     */
    public function exportWithFilters(Request $request)
    {
        try {
            $type = $request->input('type', 'retail');
            $status = $request->input('status', 'all');
            $search = $request->input('search', '');
            
            $query = Order::with(['details.productVariant.product', 'payment'])
                ->where('order_code', $type);
            
            if ($status !== 'all') {
                $statusMap = [
                    'pending' => 0, 'processing' => 1, 'shipping' => 2,
                    'completed' => 3, 'cancelled' => 4, 'approved' => 1,
                    'production' => 2, 'confirmed' => 1, 'waiting' => 2,
                ];
                if (isset($statusMap[$status])) {
                    $query->where('order_status', $statusMap[$status]);
                }
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('customer_name', 'LIKE', "%{$search}%")
                      ->orWhere('receiver_name', 'LIKE', "%{$search}%")
                      ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                      ->orWhere('receiver_phone', 'LIKE', "%{$search}%");
                });
            }
            
            $orders = $query->latest()->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            $export = new OrdersExport($type, $formattedOrders);
            
            $typeLabels = ['retail' => 'ban_le', 'wholesale' => 'ban_si', 'preorder' => 'preorder'];
            $typeLabel = $typeLabels[$type] ?? 'don_hang';
            $statusLabel = $status !== 'all' ? "_" . $status : "";
            $date = now()->format('Ymd');
            $filename = "{$date}_don_hang_{$typeLabel}{$statusLabel}.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Export filtered orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Format order data for export
     */
    protected function formatOrderForExport($order)
    {
        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
            $payment = 'Ví điện tử';
        }

        $productList = $products->map(function ($item) {
            return $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ';
        })->implode('; ');

        // Sử dụng mã hiển thị mới
        $displayCode = $this->generateOrderDisplayCode($order);

        return (object) [
            'id' => $order->id,
            'code' => $displayCode, // Thay đổi từ #ORD-xxx thành mã mới
            'display_code' => $displayCode,
            'type' => $order->order_code ?? 'retail',
            'customer_name' => $order->customer_name ?? $order->receiver_name,
            'customer_phone' => $order->customer_phone ?? $order->receiver_phone,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'created_date' => $order->created_at->format('d/m/Y H:i'),
            'products' => $productList,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'payment_method' => $payment,
            'status' => $order->getStatusLabel(),
            'note' => $order->note ?? '',
        ];
    }

    protected function getStatusText($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        $maps = [
            'retail' => [0 => 'pending', 1 => 'processing', 2 => 'shipping', 3 => 'completed', 4 => 'cancelled'],
            'wholesale' => [0 => 'pending', 1 => 'approved', 2 => 'production', 3 => 'shipping', 4 => 'completed', 5 => 'cancelled'],
            'preorder' => [0 => 'pending', 1 => 'confirmed', 2 => 'waiting', 3 => 'shipping', 4 => 'completed', 5 => 'cancelled'],
        ];
        return $maps[$orderCode][$status] ?? 'pending';
    }

    protected function getStatusLabel($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        $maps = [
            'retail' => [0 => 'Chờ xử lý', 1 => 'Đang xử lý', 2 => 'Đang giao', 3 => 'Hoàn thành', 4 => 'Đã hủy'],
            'wholesale' => [0 => 'Chờ xác nhận', 1 => 'Đã duyệt', 2 => 'Đang sản xuất', 3 => 'Đang giao', 4 => 'Hoàn thành', 5 => 'Đã hủy'],
            'preorder' => [0 => 'Chờ xác nhận', 1 => 'Đã xác nhận', 2 => 'Chờ hàng', 3 => 'Đang giao', 4 => 'Hoàn thành', 5 => 'Đã hủy'],
        ];
        return $maps[$orderCode][$status] ?? 'Chờ xử lý';
    }

    protected function getStatusMapForOrder($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $maps = [
            'retail' => ['pending' => 0, 'processing' => 1, 'shipping' => 2, 'completed' => 3, 'cancelled' => 4],
            'wholesale' => ['pending' => 0, 'approved' => 1, 'production' => 2, 'shipping' => 3, 'completed' => 4, 'cancelled' => 5],
            'preorder' => ['pending' => 0, 'confirmed' => 1, 'waiting' => 2, 'shipping' => 3, 'completed' => 4, 'cancelled' => 5],
        ];
        return $maps[$orderCode] ?? [];
    }
}
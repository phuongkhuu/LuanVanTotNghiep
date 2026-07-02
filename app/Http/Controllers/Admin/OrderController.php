<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index($type = 'retail')
    {
        $validTypes = ['retail', 'wholesale', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'retail';

        $orders = Order::with(['details.productVariant.product', 'payment'])
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
                        'image'    => $product ? $product->image : null,
                    ];
                });

                $subtotal = $products->sum('subtotal');
                $shipping = (int) ($order->shipping_fee ?? 0);
                $discount = (int) ($order->discount_amount ?? 0);
                $final = $subtotal + $shipping - $discount;

                $payment = 'COD';
                $paymentClass = 'bg-green-100 text-green-800';
                if ($order->payment && $order->payment->method === 'bank_transfer') {
                    $payment = 'Chuyển khoản';
                    $paymentClass = 'bg-blue-100 text-blue-800';
                }

                return [
                    'id'              => $order->id,
                    'code'            => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
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
                'image'    => $product ? $product->image : null,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        }

        $orderData = [
            'id'              => $order->id,
            'code'            => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
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

    public function updateStatus($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->status;
        $statusMap = $this->getStatusMapForOrder($order);
        $statusInt = $statusMap[$newStatus] ?? 0;

        $order->order_status = $statusInt;
        $order->save();

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    /**
     * Xuất tất cả đơn hàng (không phân biệt loại)
     */
    public function export(Request $request)
    {
        try {
            // Lấy TẤT CẢ đơn hàng, không phân biệt loại
            $orders = Order::with(['details.productVariant.product', 'payment'])
                ->latest()
                ->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            // Format orders
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            // Tạo export với type là 'all'
            $export = new OrdersExport('all', $formattedOrders);
            
            // Tạo filename
            $date = now()->format('Ymd_His');
            $filename = "tat_ca_don_hang_{$date}.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            \Log::error('Export all orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Xuất đơn hàng theo bộ lọc (loại đơn, trạng thái, tìm kiếm)
     */
    public function exportWithFilters(Request $request)
    {
        try {
            $type = $request->input('type', 'retail');
            $status = $request->input('status', 'all');
            $search = $request->input('search', '');
            
            // Lấy đơn hàng theo loại
            $query = Order::with(['details.productVariant.product', 'payment'])
                ->where('order_code', $type);
            
            // Lọc theo trạng thái nếu có
            if ($status !== 'all') {
                $statusMap = [
                    'pending' => 0,
                    'processing' => 1,
                    'shipping' => 2,
                    'completed' => 3,
                    'cancelled' => 4,
                    'approved' => 1,
                    'production' => 2,
                    'confirmed' => 1,
                    'waiting' => 2,
                ];
                
                if (isset($statusMap[$status])) {
                    $query->where('order_status', $statusMap[$status]);
                }
            }
            
            // Lọc theo tìm kiếm
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
            
            // Format orders
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            // Tạo export
            $export = new OrdersExport($type, $formattedOrders);
            
            // Tạo filename
            $typeLabels = [
                'retail' => 'ban_le',
                'wholesale' => 'ban_si',
                'preorder' => 'preorder'
            ];
            $typeLabel = $typeLabels[$type] ?? 'don_hang';
            $statusLabel = $status !== 'all' ? "_" . $status : "";
            $date = now()->format('Ymd_His');
            $filename = "don_hang_{$typeLabel}{$statusLabel}_{$date}.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            \Log::error('Export filtered orders error: ' . $e->getMessage());
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
        if ($order->payment && $order->payment->method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        }

        $productList = $products->map(function ($item) {
            return $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ';
        })->implode('; ');

        return (object) [
            'id' => $order->id,
            'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
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
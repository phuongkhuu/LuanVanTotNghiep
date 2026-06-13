<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index($type = 'retail')
    {
        $validTypes = ['retail', 'wholesale', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'retail';

        // Lấy tất cả đơn hàng
        $allOrders = Order::latest()->get()->map(function ($order) {
            // Map status dựa trên order_code
            $statusText = $this->getStatusText($order);
            $statusLabel = $this->getStatusLabel($order);
            
            // Xác định phương thức thanh toán
            $payment = 'COD';
            $paymentClass = 'bg-green-100 text-green-800';
            if ($order->payment && $order->payment->method === 'bank_transfer') {
                $payment = 'Chuyển khoản';
                $paymentClass = 'bg-blue-100 text-blue-800';
            }

            return [
                'id' => $order->id,
                'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'customer' => $order->receiver_name,
                'phone' => $order->receiver_phone,
                'date' => $order->created_at->format('d/m/Y'),
                'amount' => (int) $order->final_amount,
                'payment' => $payment,
                'paymentClass' => $paymentClass,
                'status' => $statusText,
                'statusLabel' => $statusLabel,
                'type' => $order->order_code ?? 'retail',
                'address' => $order->shipping_address,
                'note' => $order->note,
                'products' => $order->details->map(function ($detail) {
                    return [
                        'name' => $detail->product_name ?? 'Sản phẩm',
                        'quantity' => $detail->quantity,
                        'price' => (int) $detail->price,
                        'image' => $detail->product_image ?? null,
                    ];
                }),
            ];
        });

        return Inertia::render('Admin/Orders', [
            'type' => $type,
            'initialOrders' => $allOrders,
        ]);
    }

    protected function getStatusText($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        
        if ($orderCode === 'retail') {
            $map = [
                0 => 'pending',
                1 => 'processing',
                2 => 'shipping',
                3 => 'completed',
                4 => 'cancelled',
            ];
            return $map[$status] ?? 'pending';
        } 
        
        if ($orderCode === 'wholesale') {
            $map = [
                0 => 'pending',
                1 => 'approved',
                2 => 'production',
                3 => 'shipping',
                4 => 'completed',
                5 => 'cancelled',
            ];
            return $map[$status] ?? 'pending';
        }
        
        if ($orderCode === 'preorder') {
            $map = [
                0 => 'pending',
                1 => 'confirmed',
                2 => 'waiting',
                3 => 'shipping',
                4 => 'completed',
                5 => 'cancelled',
            ];
            return $map[$status] ?? 'pending';
        }
        
        return 'pending';
    }
    
    protected function getStatusLabel($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        
        if ($orderCode === 'retail') {
            $map = [
                0 => 'Chờ xử lý',
                1 => 'Đang xử lý',
                2 => 'Đang giao',
                3 => 'Hoàn thành',
                4 => 'Đã hủy',
            ];
            return $map[$status] ?? 'Chờ xử lý';
        }
        
        if ($orderCode === 'wholesale') {
            $map = [
                0 => 'Chờ xác nhận',
                1 => 'Đã duyệt',
                2 => 'Đang sản xuất',
                3 => 'Đang giao',
                4 => 'Hoàn thành',
                5 => 'Đã hủy',
            ];
            return $map[$status] ?? 'Chờ xác nhận';
        }
        
        if ($orderCode === 'preorder') {
            $map = [
                0 => 'Chờ xác nhận',
                1 => 'Đã xác nhận',
                2 => 'Chờ hàng',
                3 => 'Đang giao',
                4 => 'Hoàn thành',
                5 => 'Đã hủy',
            ];
            return $map[$status] ?? 'Chờ xác nhận';
        }
        
        return 'Chờ xử lý';
    }

    public function show($id)
    {
        $order = Order::with(['user', 'discount', 'campaign', 'details', 'payment'])->findOrFail($id);
        
        $orderData = [
            'id' => $order->id,
            'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'customer' => $order->receiver_name,
            'phone' => $order->receiver_phone,
            'date' => $order->created_at->format('d/m/Y'),
            'amount' => (int) $order->final_amount,
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) $order->discount_amount,
            'payment' => $order->payment ? ($order->payment->method === 'bank_transfer' ? 'Chuyển khoản' : 'COD') : 'COD',
            'status' => $this->getStatusText($order),
            'statusLabel' => $this->getStatusLabel($order),
            'type' => $order->order_code ?? 'retail',
            'address' => $order->shipping_address,
            'note' => $order->note,
            'products' => $order->details->map(function ($detail) {
                return [
                    'name' => $detail->product_name ?? 'Sản phẩm',
                    'quantity' => $detail->quantity,
                    'price' => (int) $detail->price,
                    'image' => $detail->product_image ?? null,
                ];
            }),
        ];
        
        return Inertia::render('Admin/Orders/Show', ['order' => $orderData]);
    }

    public function updateStatus($id)
    {
        $order = Order::findOrFail($id);
        $newStatus = request()->status;
        
        // Map status text back to integer based on order type
        $statusMap = $this->getStatusMapForOrder($order);
        $statusInt = $statusMap[$newStatus] ?? 0;
        
        $order->order_status = $statusInt;
        $order->save();

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }
    
    protected function getStatusMapForOrder($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        
        if ($orderCode === 'retail') {
            return [
                'pending' => 0,
                'processing' => 1,
                'shipping' => 2,
                'completed' => 3,
                'cancelled' => 4,
            ];
        }
        
        if ($orderCode === 'wholesale') {
            return [
                'pending' => 0,
                'approved' => 1,
                'production' => 2,
                'shipping' => 3,
                'completed' => 4,
                'cancelled' => 5,
            ];
        }
        
        if ($orderCode === 'preorder') {
            return [
                'pending' => 0,
                'confirmed' => 1,
                'waiting' => 2,
                'shipping' => 3,
                'completed' => 4,
                'cancelled' => 5,
            ];
        }
        
        return [];
    }

    public function export()
    {
        // Logic export Excel
        return back()->with('success', 'Xuất file thành công');
    }
}